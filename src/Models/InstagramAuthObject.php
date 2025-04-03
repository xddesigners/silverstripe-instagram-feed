<?php

namespace XD\InstagramFeed\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class InstagramAuthObject
 * @package XD\InstagramFeed\Models
 *
 * @property string LongLivedToken
 * @property string user_id
 */
class InstagramAuthObject extends DataObject
{
    private static $db = [
        'LongLivedToken' => 'Text',
        'user_id' => 'Varchar(255)'
    ];

    private static $has_one = [
        'SiteConfig' => SiteConfig::class,
    ];

    private static $table_name = 'InstagramAuthObject';

    private static $default_sort = 'LastEdited DESC';

    private static $summary_fields = [
        'Created' => 'Created',
        'LastEdited' => 'Last Edited',
        'user_id' => 'User ID',
        'LongLivedToken.LimitCharacters' => 'Long Lived Token'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['SiteConfigID']);
        return $fields;
    }

}
