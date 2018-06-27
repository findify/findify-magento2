<?php

namespace Findify\Findify\Block;

class Generatenow extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('findify/system/config/button.phtml');
    }
 
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
 
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_findifyfeed/check');
    }
 
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'findifyfeed_button',
            'label'     => $this->helper('adminhtml')->__('Run Now'),
            'onclick'   => 'javascript:check(); return false;'
        ));
 
        return $button->toHtml();
    }
    
}
