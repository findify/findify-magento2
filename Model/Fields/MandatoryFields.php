<?php

namespace Findify\Findify\Model\Fields;

use Findify\Findify\Api\FieldsInterface;
use Findify\Findify\Helper\Product as ProductHelper;
use Findify\Findify\Model\Product\Stock;
use Findify\Findify\Model\Product\Image;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product;
use Psr\Log\LoggerInterface;

class MandatoryFields implements FieldsInterface
{
    const USE_PARENT_IMAGE_CONFIG_PATH = 'findify_configuration/general/parent_image';
    const USE_PARENT_URL_CONFIG_PATH = 'findify_configuration/general/parent_url';
    const USE_CACHE_IMAGE_CONFIG_PATH = 'findify_configuration/general/cache_image';
    /**#@+
     * Mandatory Fields
     */
    const ID = 'id';
    const TITLE = 'title';
    const VARIANT_TITLE = 'variant_title';
    const DESCRIPTION = 'description';
    const PRICE = 'price';
    const IMAGE_URL = 'image_url';
    const PRODUCT_URL = 'product_url';
    const CATEGORY = 'category';
    const THUMBNAIL_URL = 'thumbnail_url';
    const AVAILABILITY = 'availability';
    const CREATED_AT = 'created_at';
    const ITEM_GROUP_ID = 'item_group_id';
    /**#@-*/

    const TAGS = 'tags';
    const TAGS_REMOVE = 'findify-remove';
    const PRODUCT_IMAGE_PATH = 'catalog/product';
    /**
     * @var Stock
     */
    private $productStock;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var ImageHelper
     */
    private $imageHelper;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Image
     */
    private $productImage;

    /**
     * MandatoryFields constructor.
     *
     * @param Stock                       $productStock
     * @param ImageHelper                 $imageHelper
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ScopeConfigInterface        $scopeConfig
     * @param ProductHelper               $productHelper
     * @param LoggerInterface             $logger
     * @param Image                       $productImage
     */
    public function __construct(
        Stock $productStock,
        ImageHelper $imageHelper,
        CategoryRepositoryInterface $categoryRepository,
        ScopeConfigInterface $scopeConfig,
        ProductHelper $productHelper,
        LoggerInterface $logger,
        Image $productImage
    ) {
        $this->productStock = $productStock;
        $this->imageHelper = $imageHelper;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->productHelper = $productHelper;
        $this->logger = $logger;
        $this->productImage = $productImage;
    }

    /**
     * Returns key => value list of mandatory Fields
     *
     * @inheritDoc
     * @see https://developers.findify.io/docs/feed-generation-manual-csv
     */
    public function getFields($product, $parent, $store)
    {
        $productData[self::ID] = $product->getId();
        $productData[self::TITLE] = $product->getName();
        $productData[self::DESCRIPTION] = $product->getDescription();
        $productData[self::PRICE] = $this->productHelper->getFormattedPrice($product->getPrice());
        $productData[self::IMAGE_URL] = $this->getImageUrl($product, $store, 'image');
        $productData[self::PRODUCT_URL] = $product->getProductUrl();
        $productData[self::CATEGORY] = $this->buildCategoryPath($product, $store);
        $productData[self::THUMBNAIL_URL] = $this->getImageUrl($product, $store, 'thumbnail');
        $productData[self::AVAILABILITY] = $this->productStock->getAvailability($product, $store);
        $productData[self::CREATED_AT] = $product->getCreatedAt();
        $productData[self::ITEM_GROUP_ID] = $product->getId();

        if ($parent !== null) {
            $productData[self::ITEM_GROUP_ID] = $parent->getId();
            
            $productData[self::VARIANT_TITLE] = $productData[self::TITLE];
            $productData[self::TITLE] = $parent->getName();

            if ($this->scopeConfig->getValue(self::USE_PARENT_IMAGE_CONFIG_PATH, 'store', $store)) {
                $productData[self::IMAGE_URL] = $this->getImageUrl($parent, $store, 'image');
                $productData[self::THUMBNAIL_URL] = $this->getImageUrl($parent, $store, 'thumbnail');
            }
            if ($this->scopeConfig->getValue(self::USE_PARENT_URL_CONFIG_PATH, 'store', $store)) {
                $productData[self::PRODUCT_URL] = $parent->getProductUrl();
            }
        }

        return $productData;
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface   $store
     * @param string           $imageType
     * @return string
     */
    private function getImageUrl($product, $store, string $imageType = 'image')
    {
        if ($this->scopeConfig->getValue(self::USE_CACHE_IMAGE_CONFIG_PATH, 'store', $store)) {
            $productImage = $this->productImage->getProductImageAsset($product, $store);

            return $productImage->getUrl();
        }
        if (!$product->getImage() || Product::NOT_SELECTED_IMAGE === $product->getImage()) {
            $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl($imageType);
        } else {
            $imageUrl =
                $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::PRODUCT_IMAGE_PATH . $product->getImage();
        }

        return $imageUrl;
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface   $store
     * @return array
     */
    private function buildCategoryPath(ProductInterface $product, StoreInterface $store)
    {
        $categories = [];
        $categoryIds = $product->getCategoryIds();
        if (empty($categoryIds)) {
            return [];
        }
        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId, $store->getId());
                if (!$category->getIsActive()) {
                    continue;
                }
                $idPath = $category->getPath();
                $pathIds = explode('/', $idPath);

                // remove first two elements in path, which are always:
                // Root Catalog > Default Category (or other root category)
                array_splice($pathIds, 0, 2);

                $pathArray = [];
                foreach ($pathIds as $pathId) {
                    $category = $this->categoryRepository->get($pathId, $store->getId());
                    if ($category->getIsActive()) {
                        $categoryName = trim($category->getName());
                        if ($categoryName !== '') {
                            $pathArray[] = $categoryName;
                        } else {
                            // Don't allow weird categories that have
                            // empty names here and there in the structure
                            $pathArray = [];
                            break;
                        }
                    }
                }
                if (!empty($pathArray)) {
                    $categories[] = join(' > ', $pathArray);
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->log(
                    "ERROR",
                    $e->getMessage() . " Product's category cannot be found.",
                    [
                        'productId' => $product->getId(),
                        'categoryId' => $categoryId,
                    ]
                );
            }
        }

        // Sorted names are easier to debug
        asort($categories);

        return array_values($categories);
    }
}
