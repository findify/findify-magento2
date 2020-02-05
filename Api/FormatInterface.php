<?php

namespace Findify\Findify\Api;

interface FormatInterface
{
    /**
     * Prepares data in JSONL format: 1 product per line
     *
     * @param array $productData
     *
     * @return array
     */
    public function prepareDataForSave($productData);
}
