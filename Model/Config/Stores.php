<?php

namespace Findify\Findify\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stores implements ArrayInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $stores = $this->storeManager->getStores();

        $output = [];
        foreach ($stores as $store) {
            $output[] = [
                'value' => $store->getStoreId(),
                'label' => $store->getName()
            ];
        }

        return $output;
    }
}
