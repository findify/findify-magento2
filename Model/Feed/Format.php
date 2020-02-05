<?php

namespace Findify\Findify\Model\Feed;

use Findify\Findify\Api\FormatInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class Format implements FormatInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }
    /**
     * @inheritDoc
     */
    public function prepareDataForSave($productsData)
    {
        $result = [];
        try {
            foreach ($productsData as $productData) {
                $result[] = $this->serializer->serialize($productData) . "\n";
            }
        } catch (\InvalidArgumentException $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $result;
    }
}
