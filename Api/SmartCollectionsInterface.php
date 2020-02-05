<?php

namespace Findify\Findify\Api;

interface SmartCollectionsInterface
{
    /**
     * Returns a list of smart collections for a specified store
     *
     * @param $storeCode
     * @return array
     */
    public function fetchSmartCollections($storeCode);
}
