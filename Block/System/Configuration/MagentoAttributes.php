<?php

namespace Findify\Findify\Block\System\Configuration;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class MagentoAttributes extends Select
{
    protected $groupFactory;
    protected $searchCriteriaBuilder;
    protected $attributeRepository;

    /**
     * MagentoAttributes constructor.
     *
     * @param Context                      $context
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     * @param array                        $data
     */
    public function __construct(
        Context $context,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributeRepository = $this->attributeRepository->getList(
                'catalog_product',
                $searchCriteria
            );

            foreach ($attributeRepository->getItems() as $items) {
                $attributeCode = $items->getAttributecode();
                $attributeLabel = $items->getFrontendLabel();
                if ($attributeLabel == '') {
                    continue;
                }
                $attributeLabel = str_replace(
                    "'",
                    '',
                    $attributeLabel
                ); // if an attribute contains ' it will break the js template so we remove it
                $this->addOption($attributeCode, $attributeLabel);
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
