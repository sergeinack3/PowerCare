<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Handlers\HandlerParameterBag;

/**
 * IConfiguration
 */
interface IConfigurationRegister
{
    /**
     * @return void
     */
    public function register();

    /**
     * Register static configurations
     *
     * @param ConfigurationManager $manager
     *
     * @return void
     */
    public function registerStatic(ConfigurationManager $manager): void;

    /**
     * @param HandlerParameterBag $parameter_bag
     */
    public function getObjectHandlers(HandlerParameterBag $parameter_bag): void;

    /**
     * @param HandlerParameterBag $parameter_bag
     */
    public function getIndexHandlers(HandlerParameterBag $parameter_bag): void;

    /**
     * @param HandlerParameterBag $parameter_bag
     */
    public function getEAIHandlers(HandlerParameterBag $parameter_bag): void;
}
