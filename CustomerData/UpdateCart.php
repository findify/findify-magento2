<?php

namespace Findify\Findify\CustomerData;

use Findify\Findify\Helper\Product as ProductHelper;
use Magento\Checkout\Model\Session;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\DataObject;

class UpdateCart extends DataObject implements SectionSourceInterface
{
    private $checkoutSession;
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * UpdateCart constructor.
     *
     * @param Session       $checkoutSession
     * @param ProductHelper $productHelper
     * @param array         $data
     */
    public function __construct(
        Session $checkoutSession,
        ProductHelper $productHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->checkoutSession = $checkoutSession;
        $this->productHelper = $productHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $products = [];
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $product['id'] = $item->getProductId();
            $product['qty'] = $item->getQty();
            $product['price'] = $this->productHelper->getFormattedPrice($item->getPrice());
            $product['variant_id'] = $this->getVariantId($item);
            $products[] = $product;
        }

        return [
            'products' => $products,
        ];
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return int|string|null
     */
    private function getVariantId($item)
    {
        return $item->getQtyOptions() ? key($item->getQtyOptions()) : '';
    }
}
