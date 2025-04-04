<?php

namespace XD\InstagramFeed\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;
use XD\InstagramFeed\Clients\InstagramClient;

class InstagramAuthController extends Controller
{
    public function index(HTTPRequest $request)
    {
        // create new Instagram instance
        $instagram = new InstagramClient();

        $siteConfig = SiteConfig::current_site_config();
        $verificationToken = $siteConfig->InstagramVerificationToken;

        // handle Facebook webhook verification
        if ($request->getVar('hub_mode') === 'subscribe') {
            if ($request->getVar('hub_verify_token') === $verificationToken) {
                return $request->getVar('hub_challenge');
            } else {
                return $this->httpError(403);
            }
        }

        // use oauth short lived token from code
        $token = $instagram->getOAuthToken($request->getVar('code'));

        // retrieve long lived token and store it
        $authObj = $instagram->storeInstagramAuthObject($token);
        if ($authObj) {
            // @todo add template for this screen, open in new window and close with button
            return [
                'Title'=> _t(__CLASS__ . '.InstagramConnected', 'Instagram connected success'),
                'Content' => DBHTMLText::create()->setValue(_t(self::class . '.TokenCreated', '<p>Received and store long lived token!</p><p><a href="/admin/settings/#Root_Instagram"></a></p>'))
            ];
        }

        return $this->httpError(403, 'No token received');

    }

}
