<?php

namespace Findify\Findify\Api;

use Magento\Store\Api\Data\StoreInterface;

interface FileProcessorInterface
{
    /**
     * @param array          $productData
     * @param StoreInterface $store
     *
     * @return mixed
     */
    public function saveFeed($productData, $store);
}
