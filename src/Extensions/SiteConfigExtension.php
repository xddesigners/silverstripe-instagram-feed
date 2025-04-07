<?php

namespace XD\InstagramFeed\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\SiteConfig\SiteConfig;
use XD\InstagramFeed\Clients\InstagramClient;
use XD\InstagramFeed\Models\InstagramAuthObject;

/**
 * Class SiteConfigExtension
 * @package XD\InstagramFeed\Extensions
 * @property SiteConfig|SiteConfigExtension $owner
 */
class SiteConfigExtension extends Extension
{

    private static $instagram_tab = 'Root.Instagram';

    private static $db = [
        'InstagramAppID' => 'Varchar(255)',
        'InstagramAppSecret' => 'Varchar(255)',
        'InstagramVerificationToken' => 'Varchar(255)',
    ];

    private static $has_many = [
        'InstagramAuthObjects' => InstagramAuthObject::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $tab = Config::inst()->get(__CLASS__, 'instagram_tab');
        $fields->addFieldsToTab($tab, [
            TextField::create('InstagramAppID', _t(__CLASS__ . '.InstagramAppID', 'Instagram App ID')),
            TextField::create('InstagramAppSecret', _t(__CLASS__ . '.InstagramAppSecret', 'Instagram App Secret')),
            TextField::create('InstagramVerificationToken', _t(__CLASS__ . '.InstagramVerificationToken', 'Instagram Verification Token'))->setDisabled(true),
        ]);

        // add button to generate verification token
        $client = new InstagramClient();
        $tokenUrl = $client->getLoginUrl();

        $buttonField = LiteralField::create('InstagramAuthButton',
            '<a href="' . $tokenUrl . '" class="btn btn-primary font-icon-external-link mb-4" target="_blank">' . _t(__CLASS__ . '.InstagramAuthButton', 'Authenticate with Instagram') . '</a>');
        $fields->addFieldToTab($tab, $buttonField);


        $refreshUrl = '/__instaauth/refresh';

        $refreshButton = LiteralField::create('LoadInstagramItemsButton',
            '<a href="' . $refreshUrl . '" class="btn btn-secondary font-icon-sync"  style="margin-bottom: 20px;" target="_blank">' . _t(__CLASS__ . '.RefreshInstagramFeed', 'Refresh Instagram Feed') . '</a>'
        );
        $fields->addFieldToTab($tab, $refreshButton);


        $config = GridFieldConfig_RecordEditor::create();
        $gridField = GridField::create('InstagramAuthObjects',
            _t(self::class . '.InstagramAuthObjects', 'InstagramAuthObjects'),
            InstagramAuthObject::get(), $config);
        $fields->addFieldToTab($tab, $gridField);
    }

    public function generateVerificationToken()
    {
        return bin2hex(random_bytes(16));
    }

    public function onBeforeWrite()
    {
        if ($this->owner->isChanged('InstagramAppID') || $this->owner->isChanged('InstagramAppSecret')) {
            $this->owner->InstagramVerificationToken = $this->generateVerificationToken();
        }
    }

}