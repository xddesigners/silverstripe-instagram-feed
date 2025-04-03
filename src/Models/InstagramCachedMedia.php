<?php

namespace XD\InstagramFeed\Models;

use SilverStripe\ORM\DataObject;

/**
 * Class InstagramCachedMedia
 * @package XD\InstagramFeed\Models
 *
 * @property string Media
 * @property string UserProfile
 */
class InstagramCachedMedia extends DataObject
{

    private static $table_name = 'InstagramMedia';

    private static $db = [
        'Media' => 'Text',
        'UserProfile' => 'Text',
    ];

}