<?php

namespace Findify\Findify\Block;

use Magento\Framework\View\Element\Template;

class Order extends Template
{    
    protected $checkoutSession;    
    protected $catalogProductTypeConfigurable;
    protected $catalogProductTypeGrouped;
        
    public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $catalogProductTypeGrouped,
        array $data = []
    )
    {
	$this->checkoutSession = $checkoutSession;
	$this->catalogProductTypeGrouped = $catalogProductTypeGrouped;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;

        parent::__construct($context, $data);
    }
    
    public function getOrder()
    {
	$order = $this->checkoutSession->getLastRealOrder();
	return $order;
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
