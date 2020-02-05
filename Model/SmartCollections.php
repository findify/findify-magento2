<?php

namespace Findify\Findify\Model;

use Findify\Findify\Api\SmartCollectionsInterface;
use Findify\Findify\Helper\SmartCollections as SmartCollectionsHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SmartCollections implements SmartCollectionsInterface
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
        SmartCollectionsHelper $smartCollectionsHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->smartCollectionsHelper = $smartCollectionsHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Return an array of all the smart collections for a specified store
     *
     * @param string $storeCode
     * @return array|null
     */
    public function fetchSmartCollections($storeCode)
    {
        $output = null;

        if ($this->smartCollectionsHelper->isFindifyEnabled()) {
            $stores = $this->storeManager->getStores();

            foreach ($stores as $store) {
                if ($store->getCode() == $storeCode) {
                    $output = $this->smartCollectionsHelper->fetchSmartCollections($store->getId());
                    break;
                }
            }
        }

        if (!$output) {
            $this->logger->error('Findify Smart Collection Error: Store code is not valid');
            throw new \InvalidArgumentException(
                __('Store code is not valid'),
                400
            );
        }

        return $output;
    }
}
