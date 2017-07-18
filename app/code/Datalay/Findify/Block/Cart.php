<?php

namespace Datalay\Findify\Block;

use Magento\Framework\View\Element\Template;

class Cart extends Template
{    
    protected $cart;    
    protected $checkoutSession;    
    protected $catalogProductTypeConfigurable;
    protected $catalogProductTypeGrouped;
                
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $catalogProductTypeGrouped,
        array $data = []
    )
    {
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
	$this->catalogProductTypeGrouped = $catalogProductTypeGrouped;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;

        parent::__construct($context, $data);
    }
    
    public function getCart()
    {        
        return $this->cart;
    }
    
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    public function getGroupedParentIds($productId)
    {
        $parentIds = $this->catalogProductTypeGrouped->getParentIdsByChild($productId);
        return $parentIds;
    }

    public function getConfigurableParentIds($productId)
    {
	$parentIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($productId);
        return $parentIds;
    }

}
