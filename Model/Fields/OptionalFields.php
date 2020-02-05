<?php

namespace Findify\Findify\Model\Fields;

use Findify\Findify\Api\FieldsInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Findify\Findify\Block\System\Configuration\AttributeSource;
use Findify\Findify\Helper\Product as ProductHelper;

class OptionalFields implements FieldsInterface
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * OptionalFields constructor.
     *
     * @param ScopeConfigInterface       $scopeConfig
     * @param ProductHelper              $productHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductHelper $productHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productHelper = $productHelper;
    }

    /**
     * Returns key => value list of Optional Fields
     * @inheritDoc
     * @see https://developers.findify.io/docs/feed-generation-manual-csv
     */
    public function getFields($product, $parent, $store)
    {
        $fields = [];
        $selectedAttributes = $this->scopeConfig
            ->getValue(
                'findify_configuration/general/attributes',
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
        if ($selectedAttributes) {
            $selectedAttributes = json_decode($selectedAttributes, true);
        }

        if (is_array($selectedAttributes)) {
            foreach ($selectedAttributes as $selectedAttributesRow) {
                if (!isset($selectedAttributesRow['active_attribute'])) {
                    continue;
                }

                $attributeCode = $selectedAttributesRow['active_attribute'];
                $magentoAttributeCode = $selectedAttributesRow['magento_attribute'];
                switch ($selectedAttributesRow['attribute_source']) {
                    case AttributeSource::ORIGINAL_SOURCE:
                        $magentoAttributeValue =
                            $this->productHelper->getAttributeValue($product, $magentoAttributeCode);
                        break;
                    case AttributeSource::PARENT_SOURCE:
                        if ($parent !== null) {
                            $magentoAttributeValue =
                                $this->productHelper->getAttributeValue($parent, $magentoAttributeCode);
                        } else { // means current product is the Parent, so we are pulling value from it
                            $magentoAttributeValue =
                                $this->productHelper->getAttributeValue($product, $magentoAttributeCode);
                        }
                        break;
                }
                $fields[$attributeCode] = $magentoAttributeValue ?? '';
            }
        }

        return $fields;
    }
}
