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
namespace Experius\MultipleWebsiteStoreCodeUrl\Plugin\Store\Model;

use Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings;
use Magento\Framework\UrlInterface;

class Store
{

    /**
     * @var \Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings
     */
    private $settings;

    /**
     * PathInfoProcessor constructor.
     * @param \Experius\MultipleWebsiteStoreCodeUrl\Helper\Settings $settings
     */
    public function __construct(
        Settings $settings
    ) {
        $this->settings = $settings;
    }

    public function aroundGetBaseUrl(\Magento\Store\Model\Store $subject, callable $proceed, $type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        $url = $proceed($type, $secure);
        if (!$this->settings->shouldRemoveWebsiteCodeFromStoreUrl()) {
            return $url;
        }
        if ($type != UrlInterface::URL_TYPE_LINK) {
            return $url;
        }
        $storeCode = $subject->getCode();
        $website = $subject->getWebsite();
        if (!$website) {
            return $proceed($type, $secure);
        }
        $websiteCode = $website->getCode();
        $storeUrlCode = str_replace("{$websiteCode}_", "", $storeCode);
        $url = str_replace($storeCode, $storeUrlCode, $url);
        return $url;
    }

}
