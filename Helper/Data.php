<?php
/**
 * A Magento 2 module named Experius MultipleWebsiteStoreCodeUrl
 * Copyright (C) 2017 Experius
 *
 * This file is part of Experius MultipleWebsiteStoreCodeUrl.
 *
 * Experius MultipleWebsiteStoreCodeUrl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Experius\MultipleWebsiteStoreCodeUrl\Helper;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Experius\MultipleWebsiteStoreCodeUrl\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resource;

    protected $currentWebsite = null;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resource;
    }

    /**
     * @param string $websiteCode
     * @param string $path
     * @param bool $isUrl
     * @return string
     */
    public function setCorrectWebsiteCodeUrl($websiteCode, $path, $isUrl = false)
    {
        $element = ($isUrl) ? 3 : 0;
        $pathParts = explode('/', ltrim($path, '/'), 5);
        $storeCode = "{$websiteCode}_{$pathParts[$element]}";

        if ($this->validateStore($storeCode)) {
            $pathParts[$element] = $storeCode;
            $path = implode('/', $pathParts);
            return $path;
        }
        //returns the original path if request havenot a valid store
        return $path;
    }

    /**
     * @param string $storeCode
     * @return bool
     */
    public function validateStore($storeCode)
    {
        try {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $this->storeManager->getStore($storeCode);
            return true;
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return int|null
     */
    public function getRequestToWebsiteId($request)
    {
        $baseUrl = str_replace('www.', '%', $request->getDistroBaseUrl());
        // Strip schemas
        $baseUrl = str_replace(['https://', 'http://'], '', $baseUrl);

        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('core_config_data');
        $websiteFilter = $connection->select()->from($table, ['scope_id'])
            ->where('scope = ?', 'websites')
            ->where('path in (?)', [Custom::XML_PATH_SECURE_BASE_URL, Custom::XML_PATH_UNSECURE_BASE_URL])
            ->where('value like ?', "%$baseUrl");
        $match = $connection->fetchCol($websiteFilter);

        return count($match) > 0 ? (int)$match[0] : null;
    }

    /**
     * Warning: Caches result
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Magento\Store\Api\Data\WebsiteInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequestToWebsite($request)
    {
        if (!$this->currentWebsite) {
            $websiteId = $this->getRequestToWebsiteId($request);
            $this->currentWebsite = $websiteId ? $this->storeManager->getWebsite($websiteId) : null;
        }
        return $this->currentWebsite;
    }
}
