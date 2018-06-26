<?php

namespace Datalay\Findify\Model\Config\Backend;

class Frequency extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'attributes/schedule/cron_expr';
    const CRON_MODEL_PATH = 'attributes/schedule/run/model';
    protected $_configValueFactory;
    protected $_runModelPath = '';
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        $this->config = $config;
        
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $time = $this->config->getValue('attributes/schedule/time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $frequency = $this->config->getValue('attributes/schedule/frequency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $timearray = explode(',', $time);

        $cronExprArray = [
            isset($timearray[1])?intval($timearray[1]):'00', //Minute
            isset($timearray[0])?intval($timearray[0]):'00', //Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);
        $this->_logger->info('Frequency.php: $cronExprString is: '.$cronExprString);

        try {
            $this->_configValueFactory->create()->load(self::CRON_STRING_PATH,'path')->setValue($cronExprString)->setPath(self::CRON_STRING_PATH)->save();
            $this->_configValueFactory->create()->load(self::CRON_MODEL_PATH,'path')->setValue($this->_runModelPath)->setPath(self::CRON_MODEL_PATH)->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }

}
