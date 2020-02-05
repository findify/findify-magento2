<?php

namespace Findify\Findify\Model;

use Findify\Findify\Helper\Product as ProductHelper;
use Findify\Findify\Model\Fields\OptionalMandatoryFields;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Findify\Findify\Model\Feed\FileProcessor;
use Findify\Findify\Model\Product\Filter as ProductFilter;
use Findify\Findify\Model\Product\ProductRepository as FindifyProductRepository;
use Findify\Findify\Model\Fields\MandatoryFields;
use Findify\Findify\Model\Fields\OptionalFields;
use Findify\Findify\Model\Fields\CustomFields;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Psr\Log\LoggerInterface;

class ProductFeedGenerator
{
    const IS_CRON_ENABLED_CONFIG_PATH = 'findify_configuration/cron/enable';
    const BATCH_SIZE_CONFIG_PATH = 'findify_configuration/technical/batch_size';
    const REMOVE_PARENT_PRODUCT_CONFIG_PATH = 'findify_configuration/general/remove';
    const EXCLUDE_PRODUCT_VISIBILITY_CONFIG_PATH = 'findify_configuration/general/exclude_product_visibility';
    const DEFAULT_BATCH_SIZE = 2000;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var FileProcessor
     */
    private $fileProcessor;
    /**
     * @var ProductFilter
     */
    private $productFilter;
    /**
     * @var MandatoryFields
     */
    private $mandatoryFields;
    /**
     * @var OptionalFields
     */
    private $optionalFields;
    /**
     * @var CustomFields
     */
    private $customFields;
    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var int
     */
    private $batchSize;
    /**
     * @var OptionalMandatoryFields
     */
    private $optionalMandatoryFields;
    /**
     * @var FindifyProductRepository
     */
    private $findifyProductRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Emulation
     */
    private $emulator;

    public function __construct(
        StoreManagerInterface $storeManager,
        FindifyProductRepository $findifyProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductFilter $productFilter,
        ScopeConfigInterface $scopeConfig,
        FileProcessor $fileProcessor,
        MandatoryFields $mandatoryFields,
        OptionalMandatoryFields $optionalMandatoryFields,
        OptionalFields $optionalFields,
        CustomFields $customFields,
        ProductHelper $productHelper,
        LoggerInterface $logger,
        Emulation $emulator
    ) {
        $this->storeManager = $storeManager;
        $this->findifyProductRepository = $findifyProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productFilter = $productFilter;
        $this->scopeConfig = $scopeConfig;
        $this->fileProcessor = $fileProcessor;
        $this->mandatoryFields = $mandatoryFields;
        $this->optionalMandatoryFields = $optionalMandatoryFields;
        $this->optionalFields = $optionalFields;
        $this->customFields = $customFields;
        $this->productHelper = $productHelper;
        $this->logger = $logger;
        $this->emulator = $emulator;
    }

    /**
     * Creates products feed per each store during cron job.
     * Works only if Findify Cron General Configuration is enabled
     * in global settings.
     *
     * @param array $specifiedStores
     * @return ProductFeedGenerator
     */
    public function generateFeed($specifiedStores = [])
    {
        $stores = $this->getStoreList($specifiedStores);
        foreach ($stores as $store) {
            $this->emulator->startEnvironmentEmulation($store->getId(), 'adminhtml');
            $productData = [];
            $currentPage = 1;
            do {
                $products = $this->getProductByStore($store, $currentPage);
                if (!$products) {
                    return $this;
                }
                /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                foreach ($products->getItems() as $product) {
                    if (
                        $this->scopeConfig->getValue(self::REMOVE_PARENT_PRODUCT_CONFIG_PATH, 'store', $store)
                        && in_array($product->getTypeId(),
                            [BundleType::TYPE_CODE, Configurable::TYPE_CODE, Grouped::TYPE_CODE], true)
                    ) {
                        continue;
                    }
                    $parentId = $this->productHelper->getItemGroupId($product);
                    $parent = null;
                    // if child belongs to parent ($parentId exists),
                    if ($parentId) {
                        $parent = $products->getItems()[$parentId] ?? null;
                        // but parent is disabled (null === $parent)
                        if (null === $parent
                            && in_array($product->getVisibility(), $this->getSelectedVisibilityArray($store))
                        ) {
                            // skip product with certain Visibility
                            continue;
                        }
                        // if child is a single product ($parentId doesn't exist),
                    } elseif (in_array($product->getVisibility(), $this->getSelectedVisibilityArray($store))) {
                        // skip product with certain Visibility
                        continue;
                    }
                    $productData[$product->getId()] = $this->customFields->getFields($product, $parent, $store)
                        + $this->mandatoryFields->getFields($product, $parent, $store)
                        + $this->optionalMandatoryFields->getFields($product, $parent, $store)
                        + $this->optionalFields->getFields($product, $parent, $store);
                }
                $currentPage++;
            } while ($this->getBatchSize() * ($currentPage - 1) <= $products->getTotalCount());
            $this->fileProcessor->saveFeed($productData, $store);
            $this->emulator->stopEnvironmentEmulation();
        }

        return $this;
    }

    /**
     * Returns a list of Active Front Stores, excluding admin store.
     * Or returns a list of Specified Stores
     *
     * @param array $specifiedStores
     * @return StoreInterface[]
     */
    private function getStoreList($specifiedStores)
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $id => $store) {
            if (empty($specifiedStores)
                && $store->getIsActive()
                && $this->scopeConfig->getValue(self::IS_CRON_ENABLED_CONFIG_PATH, 'store', $id)
            ) {
                continue;
            }
            if (in_array($store->getCode(), $specifiedStores)) {
                continue;
            }
            unset($stores[$id]);
        }

        return $stores;
    }

    /**
     * @param StoreInterface $store
     * @param int            $currentPage
     * @return null|ProductSearchResultsInterface
     */
    private function getProductByStore($store, $currentPage)
    {
        try {
            /** @var array $filterGroups */
            $filterGroups = $this->productFilter->getFilterGroups($store);
            $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
            $this->searchCriteriaBuilder
                ->setPageSize($this->getBatchSize())
                ->setCurrentPage($currentPage);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            /** @var ProductSearchResultsInterface $result */
            $result = $this->findifyProductRepository->getList($searchCriteria);
        } catch (\Exception $exception) {
            $this->logger->log($exception->getMessage());
            $result = null;
        }

        return $result;
    }

    /**
     * @return int
     */
    private function getBatchSize()
    {
        if (!isset($this->batchSize)) {
            if ($this->scopeConfig->getValue(self::BATCH_SIZE_CONFIG_PATH)) {
                $this->batchSize = $this->scopeConfig->getValue(self::BATCH_SIZE_CONFIG_PATH);
            } else {
                $this->batchSize = self::DEFAULT_BATCH_SIZE;
            }
        }

        return $this->batchSize;
    }

    private function getSelectedVisibilityArray($store): array
    {
        return explode(',',
            $this->scopeConfig->getValue(self::EXCLUDE_PRODUCT_VISIBILITY_CONFIG_PATH, 'store', $store));
    }
}
