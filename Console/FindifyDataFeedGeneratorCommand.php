<?php

namespace Findify\Findify\Console;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Findify\Findify\Model\ProductFeedGenerator;

class FindifyDataFeedGeneratorCommand extends Command
{

    const INPUT_KEY_STORE = 'store';
    const SHORTCUT_KEY_STORE = 's';
    /**
     * @var ProductFeedGenerator
     */
    private $feedGenerator;
    /**
     * @var State
     */
    private $state;

    public function __construct(
        State $state,
        ProductFeedGenerator $feedGenerator
    ) {
        $this->state = $state;
        $this->feedGenerator = $feedGenerator;
        parent::__construct();
    }
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('findify:datafeed:generate');
        $this->setDescription('Generate Findify Data Feed');
        $this->setDefinition([
            new InputOption(
                self::INPUT_KEY_STORE,
                self::SHORTCUT_KEY_STORE,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Store view code(s).'
            ),
        ]);

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
        $stores = [];
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        if ($input->getOption(self::INPUT_KEY_STORE)) {
            $stores = $input->getOption(self::INPUT_KEY_STORE);
        }
        $this->feedGenerator->generateFeed($stores);
        $output->writeln("Feeds have been generated");
    }
}
