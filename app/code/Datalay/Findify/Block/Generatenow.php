<?php
namespace Datalay\Findify\Block;

//class Generatenow extends Mage_Adminhtml_Block_System_Config_Form_Field
class Generatenow extends \Magento\Config\Block\System\Config\Form\Field
{

/*    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        //$url = $this->getUrl('catalog/product'); //
	$url = $this->getUrl('findifyfeed/adminhtml_controller/action');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Run Now')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
    */
    
    protected function _construct()
    {
        parent::_construct();
        //Mage::log('Datalay_Findify_Block_Generatenow _construct()');
        $this->setTemplate('datalay/system/config/button.phtml');
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
