<?php

namespace Datalay\Findify\Block;

use Magento\Framework\View\Element\Template;

class Order extends Template
{    
    protected $checkoutSession;    
    //protected $salesOrderFactory;
        
    public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        //\Magento\Sales\Model\Order $salesOrderFactory,
        array $data = []
    )
    {
	$this->checkoutSession = $checkoutSession;
	//$this->salesOrderFactory = $salesOrderFactory;
        parent::__construct($context, $data);
    }
    
    
/*    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
*/
    public function getOrder()
    {
	// $orderId = $this->checkoutSession->getLastOrderId();
	// $order = $this->_salesFactory->load($orderId);
	$order = $this->checkoutSession->getLastRealOrder();
	//return $order->getData();
	return $order;
    }
}
