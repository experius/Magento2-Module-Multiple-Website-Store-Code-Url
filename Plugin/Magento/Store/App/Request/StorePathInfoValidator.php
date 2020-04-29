<?php

namespace Experius\MultipleWebsiteStoreCodeUrl\Plugin\Magento\Store\App\Request;

use Experius\MultipleWebsiteStoreCodeUrl\Helper\Data;
use Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings;
use Magento\Framework\App\Request\PathInfo;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseFactory;

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
     * @var Data
     */
    private $helper;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var PathInfo
     */
    private $pathInfo;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * StorePathInfoValidator constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Settings $settings
     * @param Data $helper
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param PathInfo $pathInfo
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Settings $settings,
        Data $helper,
        StoreCookieManagerInterface $storeCookieManager,
        PathInfo $pathInfo,
        ResponseFactory $responseFactory
    ) {
        $this->storeManager = $storeManager;
        $this->settings = $settings;
        $this->helper = $helper;
        $this->storeCookieManager = $storeCookieManager;
        $this->pathInfo = $pathInfo;
        $this->responseFactory = $responseFactory;
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
        if (!$this->settings->shouldRemoveWebsiteCodeFromStoreUrl()) {
            return $result;
        }
        if (empty($pathInfo)) {
            $pathInfo = $this->pathInfo->getPathInfo(
                $request->getRequestUri(),
                $request->getBaseUrl()
            );
        }
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        if ($result) {
            if (strpos($pathParts[0], '_') === false) {
                return $result;
            }

        }
        $websiteCode = $this->storeCookieManager->getStoreCodeFromCookie();

        if(!$websiteCode && $website = $this->helper->getRequestToWebsite($request)) {
            $websiteCode = $website->getCode();
        }
        if (!$websiteCode) {
            return $result;
        }

        if ($result && strpos($request->getRequestUri(), "/{$websiteCode}_") === 0) {
            $requestUri = str_replace("/{$websiteCode}_", "", $request->getRequestUri());
            $response = $this->responseFactory->create();
            $response->setRedirect($request->getDistroBaseUrl() . $requestUri, 301);
            $response->sendResponse();
            exit;
        }

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
