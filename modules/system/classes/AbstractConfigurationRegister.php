<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Handlers\HandlerParameterBag;

/**
 * IConfiguration
 */
abstract class AbstractConfigurationRegister implements IConfigurationRegister, IShortNameAutoloadable
{
    /**
     * AbstractConfigurationRegister constructor.
     */
    final public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        // Nothing
    }

    /**
     * @inheritDoc
     */
    public function registerStatic(ConfigurationManager $manager): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function getObjectHandlers(HandlerParameterBag $parameter_bag): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getIndexHandlers(HandlerParameterBag $parameter_bag): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getEAIHandlers(HandlerParameterBag $parameter_bag): void
    {
    }
}
