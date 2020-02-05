<?php

namespace Findify\Findify\Block\System\Configuration\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OptionalMandatoryFields implements OptionSourceInterface
{
    /**
     * Returns a list of optional fields
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Sku'), 'value' => 'sku'],
            ['label' => __('Brand'), 'value' => 'brand'],
            ['label' => __('Seller'), 'value' => 'seller'],
            ['label' => __('Sale price'), 'value' => 'sale_price'],
            ['label' => __('Material'), 'value' => 'material'],
            ['label' => __('Color'), 'value' => 'color'],
            ['label' => __('Cost'), 'value' => 'cost'],
            ['label' => __('Size'), 'value' => 'size'],
            ['label' => __('Quantity'), 'value' => 'quantity'],
            ['label' => __('Rating score'), 'value' => 'rating_score'],
        ];
    }
}
