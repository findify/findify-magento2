<?php

namespace Findify\Findify\Model\Feed;

use Findify\Findify\Api\CompressedFileInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Psr\Log\LoggerInterface;

class CompressedFile implements CompressedFileInterface
{
    const DEFAULT_PATH = '/findify/feeds/';
    const SPECIFIED_FOLDER_CONFIG_PATH = 'findify_configuration/cron/folder';
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var IoFile
     */
    private $ioFile;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DirectoryList $directoryList,
        IoFile $ioFile,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save($productData, $store)
    {
        $zipFolder = $this->getFindifyFeedFolder($store->getId());
        if (!$zipFolder) {
            return null;
        }
        // Write product feed array to gzipped file
        $fileName = 'findify-' . $store->getCode() . '.gz';
        $zipFile = $zipFolder . $fileName;
        $result = file_put_contents("compress.zlib://$zipFile", $productData);
        if (false !== $result) {
            $this->logger->critical(__("Findify data feed has been generated"));
        }

        return $result;
    }

    /**
     * @param $storeId
     * @return string|null
     * @throws FileSystemException
     */
    private function getFindifyFeedFolder($storeId)
    {
        try {
            $mediaPath = $this->directoryList->getPath('media');
            $zipPath = $this->scopeConfig->getValue(self::SPECIFIED_FOLDER_CONFIG_PATH, 'store', $storeId) ??
                self::DEFAULT_PATH;
            $zipPath = $this->wrapPath($zipPath);
            if (!is_dir($mediaPath . $zipPath)) {
                $ioAdapter = $this->ioFile;
                $ioAdapter->mkdir($mediaPath . $zipPath, 0775);
            }

            return $mediaPath . $zipPath;
        } catch (FileSystemException $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return null;
    }

    /**
     * @param string $zipPath
     * @return string
     */
    private function wrapPath(string $zipPath): string
    {
        if ($zipPath[0] !== "/") {
            $zipPath = '/' . $zipPath;
        }
        if (substr($zipPath, -1) !== "/") {
            $zipPath .= '/';
        }

        return $zipPath;
    }
}
