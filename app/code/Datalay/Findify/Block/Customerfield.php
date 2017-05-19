<?php
namespace Datalay\Findify\Block;

class Customerfield extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

protected $_columns = [];
protected $_customerGroupRenderer;
protected $_addButtonLabel;
protected $_addAfter = true;

    /**
     * Initialise form fields
     *
     * @return void
     */
/*    protected function _construct()
    {
        parent::_construct();
    }*/

protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

protected function getCustomerGroupRenderer() {
        if (!$this->_customerGroupRenderer) {
            $this->_customerGroupRenderer = $this->getLayout()->createBlock(
                    '\Datalay\Findify\Block\CustomerGroup', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_customerGroupRenderer;
    }




/*    protected function _prepareToRender()
    {
/*        $this->addColumn('header', ['label' => __('Header')]);
        $this->_addAfter = true;
        $this->_addButtonLabel = __('Add Header');
           $this->addColumn('name', ['label' => __('Name')]);
           $this->addColumn('value', ['label' => __('Value')]);
           $this->_addAfter = false;
           $this->_addButtonLabel = __('Add');
    }
*/

/*

< protected function _prepareToRender() {
<         $this->addColumn( 'attributename', [
---
> 
>    protected function _prepareToRender() {
>         $this->addColumn( 'customer_group', [
67c68
<         $this->addColumn('attributejson', array(
---
>         $this->addColumn('active', array(

*/


protected function _prepareToRender() {
        $this->addColumn( 'customer_group', [
            'label' => __('Magento Attribute'),
            'renderer' => $this->getCustomerGroupRenderer(),
                ]
        );
        $this->addColumn('active', array(
            'label' => __('Name in the Feed')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
}

protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $customerGroup = $row->getCustomerGroup();
        $options = [];
        if ($customerGroup) {
            $options['option_' . $this->getCustomerGroupRenderer()->calcOptionHash($customerGroup)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        }

 public function renderCellTemplate($columnName)
    {
        if ($columnName == "active") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry';
            $this->_columns[$columnName]['style'] = 'width:200px';
        }
/*        if ($columnName == "attributename") {
            $this->_columns[$columnName]['style'] = 'width:200px';
        }*/
        return parent::renderCellTemplate($columnName);
    }


}
