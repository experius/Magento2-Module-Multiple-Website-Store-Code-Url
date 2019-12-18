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

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
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

}

