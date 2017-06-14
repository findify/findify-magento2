<?php
namespace Datalay\Findify\Block;
use Magento\Framework\View\Element\Template;

class Product extends Template
{    
    protected $_registry;

    public function __construct(
        // \Magento\Framework\View\Element\Template\Context $context,   ???                
        \Magento\Backend\Block\Template\Context $context,       
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {       
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentCategory()
    {       
        return $this->registry->registry('current_category');
    }

    public function getCurrentProduct()
    {       
        return $this->registry->registry('current_product');
    }   

}

?>
