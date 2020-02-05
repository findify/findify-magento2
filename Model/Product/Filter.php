<?php

namespace Findify\Findify\Model\Product;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Filter
{

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;
    /**
     * @var Status
     */
    private $status;

    public function __construct(
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        Status $status
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->status = $status;
    }

    /**
     * @param $store
     *
     * @return array
     */
    public function getFilterGroups($store)
    {
        $filterGroups = [];

        $filterGroups[] = $this->addStoreFilter($store);
        $filterGroups[] = $this->addEnableFilter();

        return $filterGroups;
    }

    /**
     * @param $store
     *
     * @return FilterGroup
     */
    public function addStoreFilter($store)
    {
        return $this->filterGroupBuilder
            ->addFilter(
                $this->filterBuilder
                    ->setField('store_id')
                    ->setValue($store->getId())
                    ->setConditionType('eq')
                    ->create()
            )->create();
    }

    /**
     * @return FilterGroup
     */
    public function addEnableFilter()
    {
        return $this->filterGroupBuilder
            ->addFilter(
                $this->filterBuilder
                    ->setField('status')
                    ->setValue($this->status->getSaleableStatusIds())
                    ->setConditionType('in')
                    ->create()
            )->create();
    }

}
