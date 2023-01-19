<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\handlers\CFilesObjectHandler;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationEai extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        CConfiguration::register(
            array_merge(
                $this->getAllConfigActors(),
                [
                    'CGroups' => [],
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getObjectHandlers(HandlerParameterBag $parameter_bag): void
    {
        $parameter_bag
            ->register(CEAIGroupsHandler::class, true)
            ->register(CInteropActorHandler::class, true)
            ->register(CFilesObjectHandler::class, false);
    }

    /**
     * Get all configurations availlable for all actors
     *
     * @return array
     */
    public function getAllConfigActors(): array
    {
        $class_map = CClassMap::getInstance();
        $classes = $class_map->getClassChildren(ConfigurationActorInterface::class, true, true);

        // keep only config where module is active (prevent objet not installed)
        foreach ($classes as $key => $class) {
            $module = $class_map->getClassMap($class)->module;
            if (!CModule::getActive($module)) {
                unset($classes[$key]);
            }
        }

        $configurations = [];
        /** @var ConfigurationActorInterface $class */
        foreach ($classes as $class) {
            $configs = $class->getConfigurationsActor();

            foreach ($configs as $key => $values) {
                if (!is_array($values) || empty($values)) {
                    continue;
                }

                // merge all configs for an actor
                $configurations[$key]['eai'] = array_merge_recursive(
                    CMbArray::getRecursive($configurations, "$key eai", []),
                    $values
                );
            }
        }

        return $configurations;
    }
}
