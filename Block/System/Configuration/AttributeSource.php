<?php

namespace Findify\Findify\Block\System\Configuration;

use Magento\Framework\View\Element\Html\Select;

class AttributeSource extends Select
{
    const ORIGINAL_SOURCE = 'original';
    const PARENT_SOURCE = 'parent';

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->_options = [
                [
                    'value' => self::ORIGINAL_SOURCE,
                    'label' => __('Original'),
                ],
                [
                    'value' => self::PARENT_SOURCE,
                    'label' => __('Parent'),
                ],
            ];
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
