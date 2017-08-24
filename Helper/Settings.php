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

class Settings extends AbstractHelper
{

    const CONFIG_PATH_REMOVE_WEBSITE_CODE_FROM_STORE_URL = 'remove_website_code_from_store_url';

    public $configPathModule = 'web/url';

    public function shouldRemoveWebsiteCodeFromStoreUrl()
    {
        return $this->scopeConfig->isSetFlag(
            $this->configPathModule . "/" . self::CONFIG_PATH_REMOVE_WEBSITE_CODE_FROM_STORE_URL
        );
    }
}
