<?php

namespace Findify\Findify\Controller\Adminhtml\Generate;

use Findify\Findify\Model\ProductFeedGenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Setup\Exception;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ProductFeedGenerator
     */
    private $feedGenerator;

    /**
     * @param Context              $context
     * @param JsonFactory          $resultJsonFactory
     * @param ProductFeedGenerator $feedGenerator
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductFeedGenerator $feedGenerator
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->feedGenerator = $feedGenerator;
        parent::__construct($context);
    }

    /**
     * Generates DataFeed
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $stores = $this->getRequest()->getParam('stores');
        try {
            $this->feedGenerator->generateFeed($stores);
            $response['success'] = true;
        } catch (\Exception $exception) {
            $response['success'] = false;
            $response['error'] = $exception->getMessage();
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData($response);
    }
}
