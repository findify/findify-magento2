<?php

namespace Findify\Findify\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory as CurlFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection as ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;

class SmartCollections extends AbstractHelper
{
    const XML_PATH_FINDIFY_SMARTCOLLECTIONS_ENABLED = 'findify_configuration/smartcollections/enabled';
    const XML_PATH_FINDIFY_SMARTCOLLECTIONS_EMAIL = 'findify_configuration/smartcollections/email';
    const XML_PATH_FINDIFY_SMARTCOLLECTIONS_PASSWORD = 'findify_configuration/smartcollections/password';
    const XML_PATH_FINDIFY_SMARTCOLLECTIONS_DEFAULT_STORE = 'findify_configuration/smartcollections/default_store';
    const XML_PATH_FINDIFY_SMARTCOLLECTIONS_APIKEY = 'findify_configuration/smartcollections/api_key';
    const API_ACCOUNT_URL = 'https://admin.findify.io/v1/accounts/login';
    const ADMIN_FINDIFY_MERCHANT_URL = 'https://admin.findify.io/v1/merchants';

    const SMART_COLLECTION_PREFIX = '__';

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var StoreRepository
     */
    private $storeRepository;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var CurlFactory
     */
    private $curlFactory;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $apiData = [];
    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var EavConfig
     */
    protected $eavConfig;

    private $currentStoreId;

    private $nameAttributeId;

    private $categoryNames;

    protected $_connection;

    public function __construct(
        Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        EncryptorInterface $encryptor,
        StoreRepository $storeRepository,
        SerializerInterface $serializer,
        CurlFactory $curlFactory,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig
    ) {
        $this->categoryCollection = $categoryCollectionFactory;
        $this->encryptor = $encryptor;
        $this->storeRepository = $storeRepository;
        $this->serializer = $serializer;
        $this->curlFactory = $curlFactory;
        $this->logger = $logger;
        $this->_resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;

        parent::__construct($context);
    }

    /**
     * Check if Findify is enabled in the configuration
     *
     * @return mixed
     */
    public function isFindifyEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FINDIFY_SMARTCOLLECTIONS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email address for Findify
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_FINDIFY_SMARTCOLLECTIONS_EMAIL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get password for Findify
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FINDIFY_SMARTCOLLECTIONS_PASSWORD,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get an ID of a default store for fetching smart collections
     *
     * @return mixed
     */
    public function getDefaultStoreForSmartCollections()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_FINDIFY_SMARTCOLLECTIONS_DEFAULT_STORE);
    }

    /**
     * Get API key for Findify
     *
     * @param $storeId
     * @return mixed
     */
    public function getApiKey($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FINDIFY_SMARTCOLLECTIONS_APIKEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the Findify data that's going to be used to fetch/create/edit smart collections
     *
     * @param $storeId
     * @return array|null
     * @throws NoSuchEntityException
     * @throws AuthorizationException
     */
    public function getApiData($storeId)
    {
        $apiKey = $this->getApiKey($storeId);
        $apiData = null;

        if (empty($apiKey)) {
            throw new NoSuchEntityException(__('No API Key. Check API settings'));
        }

        if (isset($this->apiData[$storeId])) {
            return $this->apiData[$storeId];
        }
        $data = [
            'login' => $this->getEmail(),
            'password' => $this->encryptor->decrypt($this->getPassword()),
            'api_key' => $this->encryptor->decrypt($apiKey),
        ];

        $payload = $this->serializer->serialize($data);

        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::POST,
            self::API_ACCOUNT_URL,
            '1.1',
            ['Content-Type:application/json'],
            $payload
        );

        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        $result = $this->serializer->unserialize($body);

        $merchantId = null;
        $apiKeyId = null;
        if (!isset($result['token'])) {
            throw new AuthorizationException(__('No token Key. Please check the credentials'));
        }
        foreach ($result['user']['merchants'] as $merchant) {
            if (isset($merchant['apiKeys'][0]['apiKey']) && $merchant['apiKeys'][0]['apiKey'] == $data['api_key']) {
                $merchantId = $merchant['merchantID'];
                $apiKeyId = $merchant['apiKeys'][0]['apiKeyID'];
            }
        }

        $this->apiData[$storeId] = [
            'token' => $result['token'],
            'merchant_id' => $merchantId,
            'api_key_id' => $apiKeyId,
        ];

        return $this->apiData[$storeId];
    }

    /**
     * Output smart collections in a JSON format
     *
     * @param $storeId
     * @return array|null
     */
    public function fetchSmartCollections($storeId)
    {
        $formatUrl = self::ADMIN_FINDIFY_MERCHANT_URL . '/%s/smart-collections/%s';

        return $this->sendRequest(\Zend_Http_Client::GET, $storeId, $formatUrl);
    }

    /**
     * Edit smart collections if they already exist, otherwise create them in Findify
     */
    public function createSmartCollections()
    {
        $this->nameAttributeId = $this->getNameAttributeId();
        $this->categoryNames = $this->getCategoryNames();
        $stores = $this->storeRepository->getList();
        $output = null;

        foreach ($stores as $store) {
            $this->currentStoreId = $store->getStoreId();
            $smartCollections = $this->getAllSmartCollections($this->currentStoreId);

            $categories = $this->categoryCollection->create()
                ->addAttributeToSelect('url_path')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setStore($this->currentStoreId);

            foreach ($categories as $category) {
                if (in_array($category->getEntityId(), [1, 2]) || !$category->getName()) {
                    continue;
                }

                $categoryPath = self::SMART_COLLECTION_PREFIX . trim($category->getUrlPath());

                if (!in_array($categoryPath, $smartCollections)) {
                    $result = $this->createSmartCollection($category, $this->currentStoreId);
                } else {
                    // If a smart collection already exists edit it
                    $collectionId = array_search($categoryPath, $smartCollections);
                    $result = $this->updateSmartCollection($category, $this->currentStoreId, $collectionId);
                }
                if (isset($result['error'])) {
                    $output = $result['error']['message'];
                }
            }
        }

        return $output;
    }

    /**
     * Return an array of all smart collections
     *
     * @param int $storeId
     * @return array
     */
    public function getAllSmartCollections($storeId): array
    {
        $smartCollections = [];
        $result = $this->fetchSmartCollections($storeId);

        if ($result && !isset($result['error'])) {
            foreach ($result as $category) {
                $smartCollections[$category['id']] = $category['slot'];
            }
        }

        return $smartCollections;
    }

    /**
     * Creates and sends Smart collection Create request
     *
     * @param $category
     * @param $storeId
     * @return array|bool|float|int|string|null
     */
    private function createSmartCollection($category, $storeId)
    {
        $payload = $this->prepareSmartCollectionPayload($category);
        $formatUrl = self::ADMIN_FINDIFY_MERCHANT_URL . '/%s/smart-collections/%s';

        return $this->sendRequest(\Zend_Http_Client::POST, $storeId, $formatUrl, $payload);
    }

    /**
     * Creates and sends Smart collection Update request
     *
     * @param $category
     * @param $storeId
     * @param $collectionId
     * @return array|bool|float|int|string|null
     */
    private function updateSmartCollection($category, $storeId, $collectionId)
    {
        $payload = $this->prepareSmartCollectionPayload($category);
        $formatUrl = self::ADMIN_FINDIFY_MERCHANT_URL . '/%s/smart-collections/%s/' . $collectionId;

        return $this->sendRequest(\Zend_Http_Client::PUT, $storeId, $formatUrl, $payload);
    }

    /**
     * @param string $method
     * @param int    $storeId
     * @param string $formatUrl
     * @param string $body
     * @return array|bool|float|int|string|null
     */
    private function sendRequest($method, $storeId, $formatUrl, $body = '')
    {
        try {
            $apiData = $this->getApiData($storeId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->alert($exception->getMessage());

            return null;
        } catch (AuthorizationException $exception) {
            $this->logger->critical($exception->getMessage());

            return null;
        }
        try {
            $url = sprintf($formatUrl, $apiData['merchant_id'], $apiData['api_key_id']);

            /** @var Curl $httpAdapter */
            $httpAdapter = $this->curlFactory->create();
            $httpAdapter->write(
                $method,
                $url,
                '1.1',
                [
                    'Content-Type:application/json',
                    'x-token: ' . $apiData['token'],
                ],
                $body
            );

            $result = $httpAdapter->read();
            $body = \Zend_Http_Response::extractBody($result);
            $result = $this->serializer->unserialize($body);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $result;
    }

    /**
     * @param $category
     * @return string
     */
    private function getFullCategoryPath($category): string
    {
        $categoryPath = trim($category->getUrlPath());
        $explodeCategoryPath = explode('/', $categoryPath);
        $fullCategoryPath = $this->capitalisation($category->getName());

        if (count($explodeCategoryPath) > 1) {
            $parentCategoryName = $category->getParentCategory()->getName();

            if ($this->fetchParentName($category->getParentId(), $this->currentStoreId)) {
                $parentCategoryName = $this->fetchParentName($category->getParentId(), $this->currentStoreId);
            }

            $fullCategoryPath = $this->capitalisation($parentCategoryName) .
                '>' . $this->capitalisation($category->getName());
        }

        return $fullCategoryPath;
    }

    /**
     * @param $path
     * @return string
     */
    private function capitalisation($path)
    {
        return implode(' ', array_map(array($this, 'uppercaseEveryWord'), explode(' ', trim($path))));
    }

    /**
     *
     * @param $path
     * @return string
     */
    private function uppercaseEveryWord($path)
    {
        $path = mb_convert_case($path, MB_CASE_LOWER, 'UTF-8');
        $firstLetter = mb_substr($path, 0, 1, 'UTF-8');
        $firstLetter = mb_convert_case(mb_strtolower($firstLetter), MB_CASE_UPPER, 'UTF-8');

        return $firstLetter . mb_substr($path, 1, null, 'UTF-8');
    }

    /**
     * Fetching the ID of a default Magento attribute (category name)
     *
     * @return int|mixed|null
     * @throws LocalizedException
     */
    private function getNameAttributeId()
    {
        $attributeId = null;
        $attribute = $this->eavConfig->getAttribute('catalog_category', 'name');

        if ($attribute->getAttributeId()) {
            $attributeId = $attribute->getAttributeId();
        }

        return $attributeId;
    }

    /**
     * Get a list of category names
     *
     * @return array
     */
    private function getCategoryNames()
    {
        $categoryData = array();
        $this->_connection = $this->_resourceConnection->getConnection();
        $table = $this->_connection->getTableName('catalog_category_entity_varchar');
        $query = "
            SELECT *
            FROM `{$table}`
            WHERE attribute_id = '{$this->nameAttributeId}'
        ";

        $result = $this->_connection->fetchAll($query);

        if (count($result) > 0) {
            foreach ($result as $category) {
                $categoryData[$category['entity_id']][$category['store_id']] = $category['value'];
            }
        }

        return $categoryData;
    }

    /**
     * @param $parentId
     * @param $storeId
     * @return string|null
     */
    private function fetchParentName($parentId, $storeId)
    {
        $parentName = null;
        $categories = $this->categoryNames;

        if (isset($categories[$parentId], $categories[$parentId][$storeId])) {
            $parentName = $categories[$parentId][$storeId];
        }

        return $parentName;
    }

    /**
     * @param $categoryPath
     * @param $fullCategoryPath
     * @param $category
     * @return array
     */
    private function buildCategoryData($categoryPath, $fullCategoryPath, $category): array
    {
        $categoryData = [
            'slot' => self::SMART_COLLECTION_PREFIX . $categoryPath,
            'query' => [
                'filters' => [
                    [
                        'name' => 'category',
                        'type' => 'category',
                        'action' => 'include',
                        'values' => [
                            [
                                'value' => $fullCategoryPath,
                            ],
                        ],
                    ],
                ],
            ],
            'enabled' => $category->getIsActive() ? true : false,
            'showConfiguredFilters' => false,
        ];

        return $categoryData;
    }

    /**
     * @param $category
     * @return bool|string
     */
    private function prepareSmartCollectionPayload($category)
    {
        $categoryPath = trim($category->getUrlPath());
        $fullCategoryPath = $this->getFullCategoryPath($category);
        $categoryData = $this->buildCategoryData($categoryPath, $fullCategoryPath, $category);
        $payload = $this->serializer->serialize($categoryData);

        return $payload;
    }

    /**
     * Sends POST request to trigger Products synchronization
     *
     * @param int $storeId
     * @return bool|mixed|string
     */
    public function syncProducts($storeId)
    {
        $formatUrl = self::ADMIN_FINDIFY_MERCHANT_URL . '/%s/feeds/%s/pull';

        return $this->sendRequest(\Zend_Http_Client::POST, $storeId, $formatUrl);
    }
}
