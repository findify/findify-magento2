<?php

namespace Findify\Findify\Helper;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\Currency;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Product extends AbstractHelper
{
    /**
     * @var Configurable
     */
    private $configurableType;
    /**
     * @var BundleType
     */
    private $bundleType;
    /**
     * @var Grouped
     */
    private $groupedType;
    /**
     * @var Currency
     */
    private $currency;

    public function __construct(
        Context $context,
        BundleType $bundleType,
        Configurable $configurableType,
        Grouped $groupedType,
        Currency $currency
    ) {
        $this->bundleType = $bundleType;
        $this->configurableType = $configurableType;
        $this->groupedType = $groupedType;
        $this->currency = $currency;

        parent::__construct($context);
    }

    /**
     * Looking for parent Id. Since it's unknown to what product child belongs to
     * we are looking through all types and get the first parent Id
     *
     * @param ProductInterface $product
     *
     * @return string|null
     */
    public function getItemGroupId(ProductInterface $product)
    {
        $parentIdsByChild = $this->configurableType->getParentIdsByChild($product->getId());
        if (!empty($parentIdsByChild)) {
            return $parentIdsByChild[0];
        }
        $parentIdsByChild = $this->bundleType->getParentIdsByChild($product->getId());
        if (!empty($parentIdsByChild)) {
            return $parentIdsByChild[0];
        }
        $parentIdsByChild = $this->groupedType->getParentIdsByChild($product->getId());
        if (!empty($parentIdsByChild)) {
            return $parentIdsByChild[0];
        }

        // This is weird, but if there are no parent there is no group
        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $code
     *
     * @return mixed
     */
    public function getAttributeValue($product, $code)
    {
        $attributeValue = $product->getAttributeText($code);
        if (!$attributeValue) {
            $attributeValue = $product->getData($code);
        }

        return $attributeValue;
    }

    /**
     * @param string $price
     *
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->currency->format($price, ['symbol' => ''], false, false);
    }
}
