<?php

namespace XD\InstagramFeed\Tasks;

use SilverStripe\Dev\BuildTask;
use XD\InstagramFeed\Clients\InstagramClient;

class InstagramUpdateTask extends BuildTask
{
    protected $title = 'Instagram Update Task';

    protected $description = 'Update Instagram feed data';

    protected $enabled = true;

    public function run($request)
    {
        $client = new InstagramClient();
        $client->updateCachedUserMedia();
        echo "Instagram feed updated successfully.";
    }

}