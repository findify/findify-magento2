<?php

namespace Datalay\Findify\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class RunCronCommand extends Command
{

    protected $cronTask;

    public function __construct(
        \Datalay\Findify\Model\Cron $cronTask,
        State $state
    ) {
        try {
            $state->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // empty
        }
        $this->cronTask = $cronTask;
        parent::__construct();
    }


 
    protected function configure()
    {
        $this->setName('findify:run_cron')->setDescription('Runs Findify Feed generation cron task');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronTask->export();
    }
 
}
