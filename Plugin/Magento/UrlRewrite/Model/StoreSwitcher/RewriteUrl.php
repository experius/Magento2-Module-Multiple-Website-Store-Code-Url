<?php

namespace Experius\MultipleWebsiteStoreCodeUrl\Plugin\Magento\UrlRewrite\Model\StoreSwitcher;

use Experius\MultipleWebsiteStoreCodeUrl\Helper\Data;
use Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RewriteUrl
 * @package Experius\MultipleWebsiteStoreCodeUrl\Plugin\Magento\UrlRewrite\Model\StoreSwitcher
 */
class RewriteUrl
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Data
     */
    private $data;

    /**
     * RewriteUrl constructor.
     * @param StoreManagerInterface $storeManager
     * @param Settings $settings
     * @param Data $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Settings $settings,
        Data $data
    ) {
        $this->storeManager = $storeManager;
        $this->settings = $settings;
        $this->data = $data;
    }

    /**
     * @param \Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl $subject
     * @param $fromStore
     * @param $targetStore
     * @param $redirectUrl
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSwitch(
        \Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl $subject,
        $fromStore,
        $targetStore,
        $redirectUrl
    ) {
        $return = [$fromStore,$targetStore,$redirectUrl];
        if (!$this->settings->shouldRemoveWebsiteCodeFromStoreUrl()) {
            return $return;
        }

        // to prevent a 404 when used on a home page
        $redirectUrlTemp = rtrim($redirectUrl,'/');
        $temp = explode('/',$redirectUrlTemp,5);
        if (rtrim($temp[3],'/') ==  rtrim(end($temp),'/'))
        {
            if (strpos(rtrim($redirectUrl, '/'), rtrim($targetStore->getBaseUrl(),'/')) === false) {
                $redirectUrl = $targetStore->getBaseUrl();
                return [$fromStore,$targetStore,$redirectUrl];
            }
            return $return;
        }
        unset($temp);
        unset($redirectUrlTemp);

        $website = $this->storeManager->getWebsite();
        if (!$website) {
            return $return;
        }
        $websiteCode = $website->getCode();
        $redirectUrl = $this->data->setCorrectWebsiteCodeUrl($websiteCode,$redirectUrl,true);

        return [$fromStore,$targetStore,$redirectUrl];
    }
}
