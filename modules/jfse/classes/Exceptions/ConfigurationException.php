<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class ConfigurationException
 *
 * @package Ox\Mediboard\Jfse\Exceptions
 */
final class ConfigurationException extends JfseException
{
    /**
     * @return ConfigurationException
     */
    public static function sourceHttpNotFound(): self
    {
        return new static('SourceHttpNotFound', 'JfseConfigurationException-error-source_http_not_found');
    }

    /**
     * @param CMediusers $user
     *
     * @return ConfigurationException
     */
    public static function userNotConfigured(CMediusers $user): self
    {
        return new static(
            'UserNotConfigured',
            'JfseConfigurationException-error-user_not_configured',
            ["{$user->_user_last_name} {$user->_user_first_name}"]
        );
    }
}
