<?php
namespace Datalay\Findify\Block\Adminhtml;



class Findifymodel extends \Magento\Backend\Block\Widget\Grid\Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_findifymodel";
	$this->_blockGroup = "findifyfeed";
	$this->_headerText = __("Findifymodel Manager");
	$this->_addButtonLabel = __("Add New Item");
	parent::__construct();
	
	}

}