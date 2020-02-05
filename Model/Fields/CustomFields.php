<?php

namespace Findify\Findify\Model\Fields;

use Findify\Findify\Api\FieldsInterface;

class CustomFields implements FieldsInterface
{
    /**
     * This method can be used either for adding new custom fields
     * or for overwriting existing ones.
     *
     * @inheritDoc
     * @see https://developers.findify.io/docs/feed-generation-manual-csv
     */
    public function getFields($product, $parent, $store)
    {
        return [];
    }
}
