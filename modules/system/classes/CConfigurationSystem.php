<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\Cron\CCronJobIndexHandler;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSystem extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     * Todo: Move handlers locales to according modules
     */
    public function register()
    {
        $configs = [
            'General' => [
                "system_date" => "date",
            ],
            'network' => [
                'ip_address_range_whitelist' => 'text',
            ],
            'object_handlers' => [],
            'index_handlers' => [],
            'eai_handlers' => [],
        ];

        $configurations = CClassMap::getInstance()->getClassChildren(IConfigurationRegister::class, true, true);

        $object_handler_parameter_bag = new HandlerParameterBag();
        $index_handler_parameter_bag  = new HandlerParameterBag();
        $eai_handler_parameter_bag    = new HandlerParameterBag();

        /** @var IConfigurationRegister $_config */
        foreach ($configurations as $_config) {
            $_config->getObjectHandlers($object_handler_parameter_bag);
            $_config->getIndexHandlers($index_handler_parameter_bag);
            $_config->getEAIHandlers($eai_handler_parameter_bag);
        }

        $this->formatHandlersSpecs($object_handler_parameter_bag, $configs['object_handlers']);
        $this->formatHandlersSpecs($index_handler_parameter_bag, $configs['index_handlers']);
        $this->formatHandlersSpecs($eai_handler_parameter_bag, $configs['eai_handlers']);

        $configurations = [
            'CGroups' => [
                'system' => $configs,
            ],
        ];

        CConfiguration::register($configurations);
    }

    /**
     * @param HandlerParameterBag $parameter_bag
     * @param array               $container
     *
     * @throws Exception
     */
    private function formatHandlersSpecs(HandlerParameterBag $parameter_bag, array &$container): void
    {
        foreach ($parameter_bag as $_handler => $_values) {
            $_handler_spec = 'bool default|' . (($_values['default']) ? '1' : '0');

            if ($_values['extra']) {
                $_handler_spec .= " {$_values['extra']}";
            }

            $container[CClassMap::getSN($_handler)] = $_handler_spec;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectHandlers(HandlerParameterBag $parameter_bag): void
    {
        $parameter_bag
            ->register(CUserLogHandler::class, false);
    }

    /**
     * @inheritDoc
     */
    public function getIndexHandlers(HandlerParameterBag $parameter_bag): void
    {
        $parameter_bag
            ->register(CCronJobIndexHandler::class, false);
    }

    public function registerStatic(ConfigurationManager $manager): void
    {
        $manager->registerStatic(
            [
                'KeyChain' => [
                    'directory_path' => 'str onlyAdmin',
                ],
            ]
        );
    }
}
