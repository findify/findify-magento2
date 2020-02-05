<?php

namespace Findify\Findify\Api;

use Magento\Store\Api\Data\StoreInterface;

interface CompressedFileInterface
{
    /**
     * @param array|string   $productData
     * @param StoreInterface $store
     *
     * @return int|bool
     */
    public function save($productData, $store);
}
