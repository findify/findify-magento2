<?php

namespace Findify\Findify\Api;

interface SmartCollectionsListInterface
{
    /**
     * Returns a list of smart collections for a predefined store
     *
     * @return array
     */
    public function fetchSmartCollections();
}
