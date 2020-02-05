<?php

namespace Findify\Findify\Model\Feed;

use Findify\Findify\Api\FileProcessorInterface;
use Findify\Findify\Helper\SmartCollections;

class FileProcessor implements FileProcessorInterface
{
    /**
     * @var Format
     */
    private $feedFormat;
    /**
     * @var CompressedFile
     */
    private $compressedFile;
    /**
     * @var Data
     */
    private $apiHelper;

    public function __construct(Format $feedFormat, CompressedFile $compressedFile, SmartCollections $apiHelper)
    {
        $this->feedFormat = $feedFormat;
        $this->compressedFile = $compressedFile;
        $this->apiHelper = $apiHelper;
    }

    /**
     * @inheritDoc
     */
    public function saveFeed($productsData, $store)
    {
        $dataForSave = $this->feedFormat->prepareDataForSave($productsData);
        $result = $this->compressedFile->save($dataForSave, $store);
        if (false !== $result) {
            $this->apiHelper->syncProducts($store->getId());
        }
    }
}
