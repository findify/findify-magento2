<?php

namespace Datalay\Findify\Block;

use Magento\Framework\View\Element\Template;

class Jstag extends Template
{    
    //protected $scopeConfig;
    
    public function __construct(
        //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        //$this->scopeConfig = $scopeConfig;
        
        parent::__construct($context, $data);
    }
    
    public function getJsTag()
    {        
        return $this->_scopeConfig->getValue('attributes/analytics/jstag', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }
}
