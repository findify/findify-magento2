<?php

namespace Findify\Findify\Pricing\Bundle\Price;

use Magento\Catalog\Model\Product;

/**
 * Bundle selection price factory
 * @api
 * @since 100.0.2
 */
class BundleSelectionFactory extends \Magento\Bundle\Pricing\Price\BundleSelectionFactory
{
    /**
     * Default selection class
     */
    const SELECTION_CLASS_DEFAULT = \Findify\Findify\Pricing\Bundle\Price\BundleSelectionPrice::class;

    /**
     * Create Price object for particular product
     *
     * @param Product $bundleProduct
     * @param Product $selection
     * @param float $quantity
     * @param array $arguments
     * @return BundleSelectionPrice
     */
    public function create(
        Product $bundleProduct,
        Product $selection,
        $quantity,
        array $arguments = []
    ) {
        $arguments['bundleProduct'] = $bundleProduct;
        $arguments['saleableItem'] = $selection;
        $arguments['quantity'] = $quantity ? floatval($quantity) : 1.;

        return $this->objectManager->create(self::SELECTION_CLASS_DEFAULT, $arguments);
    }
}
