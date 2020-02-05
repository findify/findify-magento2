<?php

namespace Findify\Findify\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\StoreInterface;

interface ProductStockInterface
{
    /**
     * @param ProductInterface $product
     * @param StoreInterface   $store
     *
     * @return string
     */
    public function getAvailability($product, $store): string;
}
