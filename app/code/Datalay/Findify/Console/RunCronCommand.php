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
        $this->cronTask = $cronTask;
        $this->state = $state;
        
        parent::__construct();
    }
 
    protected function configure()
    {
        $this->setName('findify:run_cron')->setDescription('Runs Findify Feed generation cron task');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->state->getAreaCode()) {
            $this->state->setAreaCode('adminhtml');
        }

        $this->cronTask->export();
    }
 
}
