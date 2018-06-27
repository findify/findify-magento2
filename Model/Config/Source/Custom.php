<?php

namespace Findify\Findify\Model\Config\Source;

class Custom implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {

        return [
            ['value' => 0, 'label' => __('Zero')],
            ['value' => 1, 'label' => __('One')],
            ['value' => 2, 'label' => __('Two')],
        ];
    }
}
