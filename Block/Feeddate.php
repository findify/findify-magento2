<?php

namespace Findify\Findify\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Feeddate extends Field
{
    protected $_template = 'Findify_Findify::feeddate.phtml';
        
    public function __construct(
        Context $context,
        array $data = []
    ) {
                
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getFeedFileDate()
    {
        $request = $this->_request;
        $storeId = (int) $request->getParam('store', 0);

        $configfilename = $this->_scopeConfig->getValue('attributes/feedinfo/feedfilename', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        $filename = str_replace("/", "", $configfilename);
        if(empty($filename)){
            $filename = 'jsonl_feed-'.$storeId;
        }
        $file = 'pub/media/findify/'.$filename.'.gz';

        if (file_exists($file)) {
            $timezone = $this->_scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            date_default_timezone_set($timezone);
            return date("F d Y H:i:s", filemtime($file));
        } else {
            return "$file does not exist yet";
        }

    }
}
