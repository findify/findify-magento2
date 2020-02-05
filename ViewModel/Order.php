<?php

namespace Findify\Findify\ViewModel;

use Findify\Findify\Helper\Product as ProductHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\DataObjectFactory;

class Order implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;
    /**
     * @var ProductHelper
     */
    private $productHelper;

    public function __construct(
        DataObjectFactory $objectFactory,
        CheckoutSession $checkoutSession,
        ProductHelper $productHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->objectFactory = $objectFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * Returns array of object with set data for cart page
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProducts()
    {
        $products = [];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->checkoutSession->getLastRealOrder();
        $items = $order->getAllVisibleItems();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $product = $this->objectFactory->create();
            $product->setId($item->getProductId());
            $product->setQty($item->getQtyOrdered());
            $product->setPrice(
                $this->productHelper->getFormattedPrice($item->getRowTotalInclTax() - $item->getDiscountAmount())
            );
            $product->setVariantId($this->getVariantId($item));
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return int|string
     */
    private function getVariantId($item)
    {
        $childArray = $item->getChildrenItems();
        if (!empty($childArray)) {
            $child = array_shift($childArray);

            return $child->getProductId();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->checkoutSession->getLastRealOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();
    }

    /**
     * @return string
     */
    public function getRevenue()
    {
        return $this->productHelper->getFormattedPrice($this->checkoutSession->getLastRealOrder()->getGrandTotal());
    }

    /**
     * @return string
     */
    public function getTotalDiscount()
    {
        return $this->productHelper->getFormattedPrice($this->checkoutSession->getLastRealOrder()->getDiscountAmount());
    }

    /**
     * @return string
     */
    public function getTotalTax()
    {
        return $this->productHelper->getFormattedPrice($this->checkoutSession->getLastRealOrder()->getTaxAmount());
    }

    /**
     * @return string
     */
    public function getTotalShipping()
    {
        return $this->productHelper->getFormattedPrice($this->checkoutSession->getLastRealOrder()->getShippingAmount());
    }
}
