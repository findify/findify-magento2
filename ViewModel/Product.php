<?php

namespace Findify\Findify\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Product implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Returns current Product ID
     *
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->registry->registry('current_product');

        return $product ? $product->getId() : null;
    }
}
