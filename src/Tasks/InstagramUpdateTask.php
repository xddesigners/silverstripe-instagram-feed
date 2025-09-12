<?php

namespace XD\InstagramFeed\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Input\InputInterface;
use XD\InstagramFeed\Clients\InstagramClient;

class InstagramUpdateTask extends BuildTask
{
    protected string $title = 'Instagram Update Task';

    protected static string  $description = 'Update Instagram feed data';

    protected $enabled = true;

    // implement execute method
    public function execute(InputInterface $input, PolyOutput $output): int
    {
        return $this->run($input, $output);
    }

    public function run(InputInterface $input, PolyOutput $output): int
    {
        $client = new InstagramClient();
        $client->updateCachedUserMedia();
        echo "Instagram feed updated successfully.";
    }

}
