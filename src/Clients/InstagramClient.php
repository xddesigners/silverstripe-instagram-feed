<?php

namespace XD\InstagramFeed\Clients;

use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use EspressoDev\Instagram\Instagram;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\TemplateGlobalProvider;
use XD\InstagramFeed\Models\InstagramAuthObject;
use XD\InstagramFeed\Models\InstagramCachedMedia;

class InstagramClient extends Instagram implements TemplateGlobalProvider
{

    private static $media_limit = 16;

    public function __construct()
    {
        $siteConfig = SiteConfig::current_site_config();

        if (!$siteConfig->InstagramAppID || !$siteConfig->InstagramAppSecret) {
            return false;
        }

        $redirectUri = Controller::join_links(Director::absoluteBaseURL(), '__instaauth');

        $config = [
            'appId' => $siteConfig->InstagramAppID,
            'appSecret' => $siteConfig->InstagramAppSecret,
            'redirectUri' => $redirectUri,
        ];

        $authObj = InstagramAuthObject::get()->sort('Created', 'DESC')->first();
        if (!$authObj) {
            return false;
        }
        $this->setAccessToken($authObj->LongLivedToken); // Required before refreshing

        if ($authObj) {
            // Refresh if older than 30 days
            $lastEdited = new \DateTime($authObj->LastEdited);
            $now = new \DateTime();
            $diffDays = $now->diff($lastEdited)->days;

            if ($diffDays > 30) {
                try {
                    $longLivedToken = $this->refreshLongLivedToken();
                } catch (\EspressoDev\Instagram\InstagramException $e) {
                    // log error
                    Injector::inst()->get(LoggerInterface::class)->error('Instagram API Error: ' . Director::absoluteBaseURL() . ' - ' . $e->getMessage());
                    return;
                }

                // store token
                $token = $longLivedToken->access_token;

                $authObj->LongLivedToken = $token;
                $authObj->write();
                $this->setAccessToken($token);
            }

        }

        parent::__construct($config);
    }

    public function storeInstagramAuthObject($token)
    {
        $longLivedToken = $this->getLongLivedToken();
        if ($longLivedToken) {
            $authObj = InstagramAuthObject::create();
            $authObj->LongLivedToken = $longLivedToken->access_token;
            $authObj->user_id = $token->user_id;
            $authObj->write();
            return $authObj;
        }
        return null;
    }

    public function updateCachedUserMedia($userId = "me", $limit = 16, array $pagination = [])
    {
        return $this->getCachedUserMedia($userId, $limit, $pagination, true);
    }

    /**
     * @param $userId
     * @param $limit
     * @param array $pagination
     * @param $forceUpdate
     * @return array
     * @throws \EspressoDev\Instagram\InstagramException
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function getCachedUserMedia($userId = "me", $limit = 16, array $pagination = [], $forceUpdate = false)
    {
        $cachedMedia = InstagramCachedMedia::get()->sort('Created', 'DESC')->first();
        if ($cachedMedia && !$forceUpdate) {
            return ['LastUpdated' => $cachedMedia->LastEdited, 'Media' => json_decode($cachedMedia->Media ?: ''), 'Profile' => json_decode($cachedMedia->Profile ?: '')];
        }

        if (!$cachedMedia) {
            $cachedMedia = InstagramCachedMedia::create();
        }

        $media = $this->getUserMedia($userId, $limit, $pagination) ?: [];

        if (isset($media->error) && isset($media->error->message)) {
            // if cli is running mail error to admin

            // get admin_email from config of email classs
            $to = Config::inst()->get(Email::class, 'admin_email');

            if (Director::is_cli()) {
                // send to client
                $mail = Email::create()
                    ->setSubject(_t(__CLASS__ . '.InstagramAPIError', 'Instagram API Error'))
                    ->setBody(
                        'Website Instagram API Error: ' . $media->error->message
                    )->setTo($to);
                $mail->send();

                // log error
                Injector::inst()->get(LoggerInterface::class)->error('Instagram API Error: ' . Director::absoluteBaseURL() . ' - ' . $media->error->message);

                return;
            }

            // do not update but return successfull cached media
            return ['Error' => $media->error->message, 'LastUpdated' => $cachedMedia->LastEdited, 'Media' => json_decode($cachedMedia->Media ?: ''), 'Profile' => json_decode($cachedMedia->Profile ?: '')];
        }


        $profile = $this->getUserProfile() ?: [];

        $cachedMedia->Media = json_encode($media, true);
        $cachedMedia->Profile = json_encode($profile, true);

        $cachedMedia->write();

        return ['LastUpdated' => $cachedMedia->LastEdited, 'Media' => json_decode($cachedMedia->Media ?: ''), 'Profile' => json_decode($cachedMedia->Profile ?: '')];
    }

    public function getMediaList($userId = "me", $limit = 16, array $pagination = [], $forceUpdate = false)
    {
        /**
         * @var array|object $media
         */
        $mediaArr = $this->getCachedUserMedia($userId, $limit, $pagination, $forceUpdate);
        $media = $mediaArr['Media'];

        $out = ArrayList::create();

        if (isset($media->data) && is_array($media->data)) {
            $count = 0;
            foreach ($media->data as $mediaItem) {
                if (!is_object($mediaItem)) {
                    continue;
                }

                $itemArray = [];
                foreach ($mediaItem as $key => $value) {
                    if (is_string($key) && is_string($value)) {
                        $itemArray[$key] = $value;
                        if ($key == 'caption') {
                            // get first line of text only
                            $itemArray['caption_short'] = preg_replace('/\s+/', ' ', $value);
                        }
                    }
                }

                if (!empty($mediaItem->children->data)) {
                    $itemArray['Children'] = ArrayList::create(json_decode(json_encode($mediaItem->children->data), true));
                }
                $out->push(ArrayData::create($itemArray));
                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
        }

        return $out;
    }

    public function getUserProfile()
    {
        return [];
        $data = $this->getCachedUserMedia();
        return ArrayData::create($data['Profile'] ?: []);
    }

    public static function InstagramMedia($limit)
    {
        $client = new InstagramClient();
        return $client->getMediaList('me', $limit);
    }

    public static function InstagramProfile()
    {
        $client = new InstagramClient();
        return $client->getUserMedia();
    }

    public static function get_template_global_variables()
    {
        return [
            'InstagramMedia' => 'InstagramMedia',
            'InstagramProfile' => 'InstagramProfile',
        ];
    }

}
