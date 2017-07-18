<?php

namespace Datalay\Findify\Block\Adminhtml;

class Feedisrunning extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $findifyFeedHelper;

    public function __construct(
        \Datalay\Findify\Helper\Data $findifyFeedHelper
    ) {
        $this->findifyFeedHelper = $findifyFeedHelper;
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return (string) $this->findifyFeedHelper->getFeedIsRunning();
    }

}
