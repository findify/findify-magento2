<?php

namespace Findify\Findify\Block;

use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerGroup extends \Magento\Framework\View\Element\Html\Select {
    protected $groupfactory;
    protected $searchCriteriaBuilder;
    protected $attributeRepository;
    
    public function __construct(
      \Magento\Framework\View\Element\Context $context,
      \Magento\Customer\Model\GroupFactory $groupfactory,
      \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
      SearchCriteriaBuilder $searchCriteriaBuilder,
      array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupfactory = $groupfactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    } 

    public function _toHtml() {
        if (!$this->getOptions()) {
	    $searchCriteria = $this->searchCriteriaBuilder->create();
	    $attributeRepository = $this->attributeRepository->getList(
	        'catalog_product',
        	$searchCriteria
	    );

	    foreach ($attributeRepository->getItems() as $items) {
                $attributecode = $items->getAttributecode();
                $attributelabel = $items->getFrontendLabel();
                if ($attributelabel == ''){
                    continue;
                }
                $attributelabel = str_replace("'", '', $attributelabel); // if an attribute contains ' it will break the js template so we remove it
                $this->addOption($attributecode, $attributelabel);
            }            
            
        }

        return parent::_toHtml();
    }

    public function setInputName($value) {
        return $this->setName($value);
    }

}
