<?php

namespace Findify\Findify\Pricing\Bundle\Price;

/**
 * Bundle option price
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class BundleSelectionPrice extends \Magento\Bundle\Pricing\Price\BundleSelectionPrice
{

    public function getSelection(){
        return $this->selection;
    }
    
}
