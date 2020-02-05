<?php

namespace Findify\Findify\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\StoreInterface;

interface FieldsInterface
{

    /**
     * @param ProductInterface $product
     * @param ProductInterface|null $parent
     * @param StoreInterface   $store
     *
     * @return array
     */
    public function getFields($product, $parent, $store);
}
