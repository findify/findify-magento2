<?php

namespace Findify\Findify\Cron;

use Findify\Findify\Model\ProductFeedGenerator;

class GenerateProductFeed
{

    /**
     * @var ProductFeedGenerator
     */
    private $generator;

    public function __construct(
        ProductFeedGenerator $generator
    ) {
        $this->generator = $generator;
    }

    /**
     * Generates products feeds for all stores.
     *
     * @return void
     */
    public function execute()
    {
        $this->generator->generateFeed();
    }
}
