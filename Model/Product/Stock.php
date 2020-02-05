<?php

namespace Findify\Findify\Model\Product;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Findify\Findify\Api\ProductStockInterface;

class Stock implements ProductStockInterface
{
    const IN_STOCK = 'in stock';
    const OUT_OF_STOCK = 'out of stock';
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getAvailability($product, $store): string
    {
        $stockStatus = $this->stockRegistry->getProductStockStatus($product->getId(), $store->getId());

        return $stockStatus ? self::IN_STOCK : self::OUT_OF_STOCK;
    }
}
