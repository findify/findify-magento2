<?php

namespace Findify\Findify\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Area;
use Magento\Store\Api\Data\StoreInterface;

class Image
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;
    /**
     * @var PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;
    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $presentationConfig,
        ParamsBuilder $imageParamsBuilder,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->presentationConfig = $presentationConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
    }

    /**
     * Returns Products image Assets for a product.
     *
     * @param ProductInterface $product
     * @param StoreInterface   $store
     * @return mixed
     */
    public function getProductImageAsset(ProductInterface $product, StoreInterface $store)
    {
        $themeId = $this->scopeConfig->getValue(DesignInterface::XML_PATH_THEME_ID, 'store', $store);
        $params = ['area' => Area::AREA_FRONTEND, 'themeId' => $themeId];
        $viewImageConfig = $this->presentationConfig->getViewConfig($params)->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            'category_page_list'
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);
        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type'],
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }

        return $imageAsset;
    }
}
