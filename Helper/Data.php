<?php

namespace Findify\Findify\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getTemplate()
    {
        if ($this->scopeConfig->isSetFlag('findify_configuration/analytics/enable')) {
            $template = 'Findify_Findify::search/form.mini.phtml';
        } else {
            $template = 'Magento_Search::form.mini.phtml';
        }

        return $template;
    }
}
