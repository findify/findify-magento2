<?php

namespace Findify\Findify\Block\Analytics;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class JsTag extends Template
{
    /**
     * Returns the Js snippet.
     *
     * @see https://dashboard.findify.io/settings/integration
     * @return string
     */
    public function getJsTag()
    {
        return $this->_scopeConfig->getValue('findify_configuration/analytics/js_tag_url', ScopeInterface::SCOPE_STORE);
    }
}
