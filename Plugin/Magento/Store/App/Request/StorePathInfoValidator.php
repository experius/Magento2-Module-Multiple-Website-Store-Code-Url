<?php

namespace Experius\MultipleWebsiteStoreCodeUrl\Plugin\Magento\Store\App\Request;

use Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StorePathInfoValidator
 * @package Experius\MultipleWebsiteStoreCodeUrl\Plugin\Magento\Store\App\Request
 */
class StorePathInfoValidator
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings
     */
    private $settings;

    /**
     * @var \Experius\MultipleWebsiteStoreCodeUrl\Helper\Data
     */
    private $helper;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var \Magento\Framework\App\Request\PathInfo
     */
    private $pathInfo;

    /**
     * StorePathInfoValidator constructor.
     * @param StoreManagerInterface $storeManager
     * @param Settings $settings
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param \Magento\Framework\App\Request\PathInfo $pathInfo
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Settings $settings,
        \Experius\MultipleWebsiteStoreCodeUrl\Helper\Data $helper,
        StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\Request\PathInfo $pathInfo
    ) {
        $this->storeManager = $storeManager;
        $this->settings = $settings;
        $this->helper = $helper;
        $this->storeCookieManager = $storeCookieManager;
        $this->pathInfo = $pathInfo;
    }

    /**
     * @param \Magento\Store\App\Request\StorePathInfoValidator $subject
     * @param $result
     * @param $request
     * @param string $pathInfo
     * @return string
     */
    public function afterGetValidStoreCode(
        \Magento\Store\App\Request\StorePathInfoValidator $subject,
        $result,
        $request,
        $pathInfo = ''
    ) {
        if ($result != null || !$this->settings->shouldRemoveWebsiteCodeFromStoreUrl()) {
            return $result;
        }
        if (empty($pathInfo)) {
            $pathInfo = $this->pathInfo->getPathInfo(
                $request->getRequestUri(),
                $request->getBaseUrl()
            );
        }
        $websiteCode = $this->storeCookieManager->getStoreCodeFromCookie();

        if(!$websiteCode && $website = $this->helper->getRequestToWebsite($request)) {
            $websiteCode = $website->getCode();
        }
        if (!$websiteCode) {
            return $result;
        }
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = "{$websiteCode}_{$pathParts[0]}";
        try {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $this->storeManager->getStore($storeCode);
        } catch (\Exception $e) {
            return $result;
        }
        return $storeCode;
    }
}
