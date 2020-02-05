<?php

namespace Findify\Findify\Model\Indexer;

use Findify\Findify\Helper\SmartCollections as SmartCollectionsHelper;
use Magento\Framework\Indexer\ActionInterface;

class GenerateSmartCollections implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var SmartCollectionsHelper
     */
    private $smartCollectionsHelper;

    public function __construct(SmartCollectionsHelper $smartCollectionsHelper)
    {
        $this->smartCollectionsHelper = $smartCollectionsHelper;
    }

    /**
     * Used by mview, allows process indexer in the "Update on schedule" mode
     *
     * @param $ids
     */
    public function execute($ids)
    {
        $this->smartCollectionsHelper->createSmartCollections();
    }

    /**
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        $this->smartCollectionsHelper->createSmartCollections();
    }

    /**
     * Works with a set of entity changed (may be massaction)
     *
     * @param array $ids
     */
    public function executeList(array $ids)
    {
        $this->smartCollectionsHelper->createSmartCollections();
    }

    /**
     * Works in runtime for a single entity using plugins
     *
     * @param $id
     */
    public function executeRow($id)
    {
        $this->smartCollectionsHelper->createSmartCollections();
    }
}
