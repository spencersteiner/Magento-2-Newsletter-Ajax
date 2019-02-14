<?php
/**
 * Copyright © Spencer Steiner.
 * See LICENSE.txt for license details.
 */

namespace SS\NewsletterAjax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package SS\NewsletterAjax\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Newsletter Ajax config path
     */
    const XML_PATH_NEWSLETTER_AJAX = 'newsletterajax/';

    /**
     * Helper to get config value
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Helper to get general config value
     *
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_NEWSLETTER_AJAX . 'general/' . $code, $storeId);
    }
}
