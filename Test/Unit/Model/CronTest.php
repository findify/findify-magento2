<?php

namespace Datalay\Findify\Test\Unit\Model;

class CronTest extends \PHPUnit_Framework_TestCase
{
    protected $model;
    protected $categoryRepositoryMock;
    protected $categoryTreeMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->searchCriteriaBuilderMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaBuilder', [], [], '', false);
        $this->filterBuilderMock = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->csvMock = $this->getMock('\Magento\ImportExport\Model\Export\Adapter\Csv', [], [], '', false);

        $this->model = new \Datalay\Findify\Model\Cron(
            $this->productRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->csvMock
        );
    }

    public function testOne()
    {
        $this->assertEquals(1, 1);
    }
}
