<?php

namespace Findify\Findify\Block\System\Configuration;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

class GenerateButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Findify_Findify::system/config/generateButton.phtml';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface   $serializer
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for Generate DataFeed button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('findify/generate/index');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $name = $this->retrieveScopeName();
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'generate_feed_button',
                'label' => __('Generate DataFeed for ' . $name),
            ]
        );

        return $button->toHtml();
    }

    /**
     * Returns list store's codes.
     * If Scope is a Website, returns a list of all its stores.
     * If Scope is a Store, returns its code.
     * Returns empty array if Default scope
     *
     * @return string
     */
    public function getParams(): string
    {
        $params['stores'] = [];
        $scope = $this->getSelectedScope();
        if ($scope instanceof WebsiteInterface) {
            $params['stores'] = $scope->getStoreCodes();
        }
        if ($scope instanceof StoreInterface) {
            $params['stores'][] = $scope->getCode();
        }

        return $this->serializer->serialize($params);
    }

    /**
     * Retrieve Scope Name. Either website's or store's name.
     *
     * @return string
     */
    private function retrieveScopeName(): string
    {
        $scope = $this->getSelectedScope();
        $name = $scope ? $scope->getName() : __('All Stores');

        return $name;
    }

    /**
     * @return StoreInterface|WebsiteInterface|null
     */
    private function getSelectedScope()
    {
        $scope = null;
        $params = $this->getRequest()->getParams();
        if (isset($params['store'])) {
            $storeId = $this->getRequest()->getParam('store');
            $scope = $this->storeManager->getStore($storeId);
        }
        if (isset($params['website'])) {
            $websiteId = $this->getRequest()->getParam('website');
            $scope = $this->storeManager->getWebsite($websiteId);
        }

        return $scope;
    }
}
