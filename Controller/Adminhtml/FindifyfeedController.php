<?php

namespace Findify\Findify\Controller\Adminhtml;

class Findifyfeed extends \Magento\Backend\App\Action
{

    public function __construct() {}
    
    public function checkAction()
    {
        $timecreated   = strftime("%Y-%m-%d %H:%M:%S",  mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        $timescheduled = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        $jobCode = 'findifyfeed_crongeneratefeed';

        try {
                $schedule = Mage::getModel('cron/schedule');
                $schedule->setJobCode($jobCode)
                        ->setCreatedAt($timecreated)
                        ->setScheduledAt($timescheduled)
                        ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                        ->save();
        } catch (Exception $e) {
                 throw new \Exception(Mage::helper('cron')->__('Unable to save Cron expression'));
        }

        $result = 1;
        Mage::app()->getResponse()->setBody($result);
    }

    public function execute() {}
    		
}
