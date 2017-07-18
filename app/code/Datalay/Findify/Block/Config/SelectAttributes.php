<?php

namespace Datalay\Findify\Block\Config;

class SelectAttributes extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_itemRenderer;
    
    public function _prepareToRender()
    {
        $this->addColumn('attributename', array(
            'label' => __('Magento Attribute'),
            'renderer' => $this->_getRenderer(),
        ));

        $this->addColumn('attributejson', array(
            'label' => __('Name in the Feed'),
            'style' => 'width:200px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    
    protected function  _getRenderer() 
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock('\Datalay\Findify\Block\Config\Adminhtml\Form\Field\Attribute', '', array('is_render_to_js_template'=>true));
        }
        return $this->_itemRenderer;
    }
 
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()
                ->calcOptionHash($row->getData('attributename')),
            'selected="selected"'
        );
    }
    
}
