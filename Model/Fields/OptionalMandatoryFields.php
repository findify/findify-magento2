<?php

namespace Findify\Findify\Model\Fields;

use Findify\Findify\Api\FieldsInterface;
use Findify\Findify\Helper\Product as ProductHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Api\Data\StoreInterface;

class OptionalMandatoryFields implements FieldsInterface
{
    const ENABLE_OPTIONAL_CONFIG_PATH = 'findify_configuration/general/enable_optional';
    const OPTIONAL_MANDATORY_FIELDS_CONFIG_PATH = 'findify_configuration/general/optional_mandatory_fields';
    const SKU = 'sku';
    const BRAND = 'brand';
    const SELLER = 'seller';
    const SALE_PRICE = 'sale_price';
    const MATERIAL = 'material';
    const COLOR = 'color';
    const COST = 'cost';
    const SIZE = 'size';
    const QUANTITY = 'quantity';
    const RATING_SCORE = 'rating_score';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var ReviewFactory
     */
    private $reviewFactory;
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductHelper $productHelper,
        StockRegistryInterface $stockRegistry,
        ReviewFactory $reviewFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productHelper = $productHelper;
        $this->stockRegistry = $stockRegistry;
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * Returns key => value list of mandatory Fields
     *
     * @param ProductInterface      $product
     * @param ProductInterface|null $parent
     * @param StoreInterface        $store
     *
     * @return array
     *
     * @see https://developers.findify.io/docs/feed-generation-manual-csv
     */
    public function getFields($product, $parent, $store)
    {
        $data = [];
        $isEnabled = $this->scopeConfig->getValue(self::ENABLE_OPTIONAL_CONFIG_PATH, 'store', $store);
        if (!$isEnabled) {
            return [];
        }
        $fields = $this->scopeConfig->getValue(self::OPTIONAL_MANDATORY_FIELDS_CONFIG_PATH, 'store', $store);
        $fields = explode(',', $fields);
        foreach ($fields as $field) {
            $methodName = $this->constructMethodName($field);
            if (method_exists($this, $methodName)) {
                $data[$field] = $this->$methodName($product, $store);
            }
        }

        return $data;
    }

    /**
     * Take a field name like "rating_score" and translate it into a method name, like "getRatingScore()"
     *
     * @param $field
     *
     * @return string
     */
    private function constructMethodName($field)
    {
        $words = explode('_', $field);

        $methodName = 'get';
        foreach ($words as $word) {
            $methodName .= ucfirst(trim($word));
        }

        return $methodName;
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function getSku($product, $store)
    {
        return $product->getSku();
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getBrand($product, $store)
    {
        $value = $this->productHelper->getAttributeValue($product, self::BRAND);

        return $value ?: '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getSeller($product, $store)
    {
        $value = $this->productHelper->getAttributeValue($product, self::SELLER);

        return $value ?: '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return float
     */
    private function getSalePrice($product, $store)
    {
        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();

        return (float)sprintf('%0.2f', $finalPrice);
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function getMaterial($product, $store)
    {
        $value = $this->productHelper->getAttributeValue($product, self::MATERIAL);

        return $value ?: '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function getColor($product, $store)
    {
        $value = $this->productHelper->getAttributeValue($product, self::COLOR);

        return $value ?: '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getCost($product, $store)
    {
        return $product->getCost() ? sprintf('%0.2f', $product->getCost()) : '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function getSize($product, $store)
    {
        $value = $this->productHelper->getAttributeValue($product, self::SIZE);

        return $value ?: '';
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return float
     */
    private function getQuantity($product, $store)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        return $stockItem->getQty();
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function getRatingScore($product, $store)
    {
        /** @var  \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create();
        $review->getEntitySummary($product, $store->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();

        return $ratingSummary;
    }
}
