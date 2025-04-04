<?php

namespace XD\InstagramFeed\Clients;

use EspressoDev\Instagram\Instagram;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
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

        $redirectUri = Controller::join_links(Director::absoluteBaseURL(), '__instaauth');

        $config = [
            'appId' => $siteConfig->InstagramAppID,
            'appSecret' => $siteConfig->InstagramAppSecret,
            'redirectUri' => $redirectUri,
        ];

        // auto set access token if it exists
        $authObj = InstagramAuthObject::get()->sort('Created', 'DESC')->first();
        if ($authObj) {
            // check if token needs to be refreshed, refresh after 30 days with TimeDiffIn
            $days = $authObj->dbObject('LastEdited')->TimeDiffIn('days');
            if ($days > 30) {
                $longLivedToken = $this->refreshLongLivedToken();
                $authObj->LongLivedToken = $longLivedToken->access_token;
                $authObj->write();
            }
            $this->setAccessToken($authObj->LongLivedToken);
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