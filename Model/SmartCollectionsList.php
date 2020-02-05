<?php

namespace Findify\Findify\Model;

use Findify\Findify\Api\SmartCollectionsListInterface;
use Findify\Findify\Helper\SmartCollections as SmartCollectionsHelper;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SmartCollectionsList implements SmartCollectionsListInterface
{
    /**
     * @var SmartCollectionsHelper
     */
    private $smartCollectionsHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SmartCollectionsHelper $smartCollectionHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->smartCollectionsHelper = $smartCollectionHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Return an array of all the smart collections for a default store
     *
     * @return array|null
     * @throws ApiCallException
     */
    public function fetchSmartCollections()
    {
        $output = null;

        if ($this->smartCollectionsHelper->isFindifyEnabled()) {
            $storeId = $this->smartCollectionsHelper->getDefaultStoreForSmartCollections();

            $output = $this->smartCollectionsHelper->fetchSmartCollections($storeId);
        }

        if (!$output) {
            $this->logger->error('Findify Smart Collection Error: There was an error with API call');
            throw new ApiCallException(
                __('There was an error with API call'),
                400
            );
        }

        return $output;
    }
}
