<?php

namespace Findify\Findify\Block;

use Magento\Framework\View\Element\Template;

class Jstag extends Template
{    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
    
    public function getJsTag()
    {        
        return $this->_scopeConfig->getValue('attributes/analytics/jstag', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }

}
