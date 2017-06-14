<?php

namespace Datalay\Findify\Block;

use Magento\Framework\View\Element\Template;

class Cart extends Template
{    
    protected $cart;    
    protected $checkoutSession;    
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        
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
}
