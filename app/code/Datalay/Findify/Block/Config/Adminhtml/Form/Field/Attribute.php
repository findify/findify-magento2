<?php
namespace Datalay\Findify\Block\Config\Adminhtml\Form\Field;

//class Attribute extends Mage_Core_Block_Html_Select
class Attribute implements \Magento\Framework\Option\ArrayInterface
{
    public function _toHtml()
    {
	$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
	    ->getItems();

	foreach ($attributes as $attribute){
	    $attributecode = $attribute->getAttributecode();
	    $attributelabel = $attribute->getFrontendLabel();
	    if ($attributelabel == ''){
	        continue;
            }
            $attributelabel = str_replace("'", '', $attributelabel); // if an attribute contains ' it will break the js template so we remove it
            $this->addOption($attributecode, $attributelabel);
        }
 
        return parent::_toHtml();
    }
 
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function toOptionArray()
    {
        return [
            ['value' => 'grid', 'label' => __('Grid Only')],
            ['value' => 'list', 'label' => __('List Only')],
            ['value' => 'grid-list', 'label' => __('Grid (default) / List')],
            ['value' => 'list-grid', 'label' => __('List (default) / Grid')]
        ];
    }
}
