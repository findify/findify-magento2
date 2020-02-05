<?php

namespace Findify\Findify\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Findify\Findify\Helper\SmartCollections as SmartCollectionsHelper;

class CreateSmartCollections extends Command
{
    /**
     * @var SmartCollectionsHelper
     */
    protected $smartCollectionsHelper;

    public function __construct(
        SmartCollectionsHelper $smartCollectionsHelper
    ) {
        $this->smartCollectionsHelper = $smartCollectionsHelper;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('findify:datafeed:createSmartCollections');
        $this->setDescription('Create smart collections');

        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputMessage = $this->smartCollectionsHelper->createSmartCollections();

        // If there is an output message that means there was an error in the response
        if (!$outputMessage) {
            $output->writeln('Smart collections created successfully');
        } else {
            $output->writeln('Error: ' . $outputMessage);
        }
    }
}
