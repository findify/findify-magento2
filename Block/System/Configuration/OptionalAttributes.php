<?php

namespace Findify\Findify\Block\System\Configuration;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class OptionalAttributes extends AbstractFieldArray
{

    protected $_columns = [];
    protected $magentoAttributesRenderer;
    protected $attributeSourceRenderer;
    protected $_addButtonLabel;
    protected $_addAfter = true;

    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    protected function getMagentoAttributesRenderer()
    {
        if (!$this->magentoAttributesRenderer) {
            $this->magentoAttributesRenderer = $this->getLayout()->createBlock(
                MagentoAttributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->magentoAttributesRenderer;
    }
    protected function getAttributeSourceRenderer()
    {
        if (!$this->attributeSourceRenderer) {
            $this->attributeSourceRenderer = $this->getLayout()->createBlock(
                AttributeSource::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->attributeSourceRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('magento_attribute', [
                'label' => __('Magento Attribute'),
                'renderer' => $this->getMagentoAttributesRenderer(),
            ]);
        $this->addColumn('active_attribute', [
            'label' => __('Name in the Feed'),
        ]);
        $this->addColumn('attribute_source', [
            'label' => __('Source of attribute'),
            'renderer' => $this->getAttributeSourceRenderer(),
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $magentoAttribute = $row->getMagentoAttribute();
        $options = [];
        if ($magentoAttribute) {
            $options['option_' . $this->getMagentoAttributesRenderer()->calcOptionHash($magentoAttribute)] =
                'selected="selected"';
        }
        $attributeSource = $row->getAttributeSource();
        if ($attributeSource) {
            $options['option_' . $this->getAttributeSourceRenderer()->calcOptionHash($attributeSource)] =
                'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'active_attribute') {
            $this->_columns[$columnName]['class'] = 'input-text required-entry';
            $this->_columns[$columnName]['style'] = 'width:150px';
        }
        if ($columnName == 'attribute_source') {
            $this->_columns[$columnName]['renderer']->setExtraParams('style="width:100px"');
        }

        return parent::renderCellTemplate($columnName);
    }
}
