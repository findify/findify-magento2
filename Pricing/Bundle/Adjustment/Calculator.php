<?php

namespace Findify\Findify\Pricing\Bundle\Adjustment;

use Magento\Framework\Pricing\Adjustment\Calculator as CalculatorBase;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Bundle price calculator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Calculator extends \Magento\Bundle\Pricing\Adjustment\Calculator
{

    protected $selectionListIds;

    public function __construct(
    CalculatorBase $calculator,
        AmountFactory $amountFactory,
        \Findify\Findify\Pricing\Bundle\Price\BundleSelectionFactory $bundleSelectionFactory,
        TaxHelper $taxHelper,
        PriceCurrencyInterface $priceCurrency 
        
    ) {

        $this->calculator = $calculator;
        $this->amountFactory = $amountFactory;
        $this->selectionFactory = $bundleSelectionFactory;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
    }

    protected function getSelectionAmounts(\Magento\Catalog\Model\Product $bundleProduct,
        $searchMin,
        $useRegularPrice = false) {
        // Flag shows - is it necessary to find minimal option amount in case if all options are not required
        $shouldFindMinOption = false;
        if ($searchMin && $bundleProduct->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC && !$this->hasRequiredOption($bundleProduct)
        ) {
            $shouldFindMinOption = true;
        }

        $canSkipRequiredOptions = $searchMin && !$shouldFindMinOption;

        $currentPrice = false;
        $priceList = [];
        foreach ($this->getBundleOptions($bundleProduct) as $option) {
            if ($this->canSkipOption($option, $canSkipRequiredOptions)) {
                continue;
            }
            $selectionPriceList = $this->createSelectionPriceList($option, $bundleProduct, $useRegularPrice);
            $selectionPriceList = $this->processOptions($option, $selectionPriceList, $searchMin);

            $lastSelectionPrice = end($selectionPriceList);
            $lastValue = $lastSelectionPrice->getAmount()->getValue() * $lastSelectionPrice->getQuantity();
            if ($shouldFindMinOption && (!$currentPrice ||
                $lastValue < ($currentPrice->getAmount()->getValue() * $currentPrice->getQuantity()))
            ) {
                $currentPrice = end($selectionPriceList);
            } elseif (!$shouldFindMinOption) {
                $priceList = array_merge($priceList, $selectionPriceList);
            }
        }

        $this->selectionListIds = [];
        foreach ($priceList as $selectionPrice) {
            $this->selectionListIds[] = $selectionPrice->getSelection()->getId();
        }

        return $shouldFindMinOption ? [$currentPrice] : $priceList;
    }

    public function getSelectionIds() {
        return $this->selectionListIds;
    }


}
