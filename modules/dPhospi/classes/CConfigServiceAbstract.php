<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;

class CConfigServiceAbstract extends CStoredObject
{
    public $service_id;
    public $group_id;

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props               = parent::getProps();
        $props["service_id"] = "ref class|CService";
        $props["group_id"]   = "ref class|CGroups";

        return $props;
    }

    static function setSHM($name, $config)
    {
        $cache = Cache::getCache(Cache::DISTR);

        $cache->set($name, $config);
    }

    static function getSHM($name)
    {
        $cache = Cache::getCache(Cache::DISTR);

        return $cache->get($name);
    }

    static function remSHM($name)
    {
        $cache = Cache::getCache(Cache::DISTR);

        return $cache->delete($name);
    }

    protected static function _getAllConfigs($class, $key, $value)
    {
        // Chargement des etablissements
        $group = new CGroups();

        /** @var CGroups[] $groups */
        $groups = $group->loadList();
        CStoredObject::massLoadBackRefs($groups, "services", "nom");

        // Chargement de toutes les configs
        /** @var self $config */
        $config = new $class();

        /** @var self[] $all_configs */
        $all_configs = $config->loadList();

        if ($all_configs == null) {
            return null;
        }

        $configs_default = [];

        /** @var self[] $configs_default */
        // Creation du tableau de valeur par defaut (quelque soit l'etablissement)
        foreach ($all_configs as $_config) {
            if (!$_config->service_id && !$_config->group_id) {
                $configs_default[$_config->$key] = $_config;
            } else {
                if ($_config->service_id) {
                    $configs_service[$_config->service_id][$_config->$key] = $_config->$value;
                } else {
                    $configs_group[$_config->group_id][$_config->$key] = $_config->$value;
                }
            }
        }

        $configs = [];

        // Parcours des etablissements
        foreach ($groups as $group_id => $group) {
            $group->loadRefsServices();
            // Parcours des services
            foreach ($group->_ref_services as $service_id => $_service) {
                foreach ($configs_default as $_config_default) {
                    $configs[$group_id][$service_id][$_config_default->$key] = $_config_default->$value;
                    if (isset($configs_group[$group_id][$_config_default->$key])) {
                        $configs[$group_id][$service_id][$_config_default->$key] = $configs_group[$group_id][$_config_default->$key];
                    }
                    if (isset($configs_service[$service_id][$_config_default->$key])) {
                        $configs[$group_id][$service_id][$_config_default->$key] = $configs_service[$service_id][$_config_default->$key];
                    }
                }
            }
            // Si aucun service
            foreach ($configs_default as $_config_default) {
                if (isset($configs_group[$group_id][$_config_default->$key])) {
                    $configs[$group_id]["none"][$_config_default->$key] = $configs_group[$group_id][$_config_default->$key];
                } else {
                    $configs[$group_id]["none"][$_config_default->$key] = $_config_default->$value;
                }
            }
        }

        return $configs;
    }

    static function emptySHM()
    {
    }

    static function isCacheUpToDate($name)
    {
        $cache = Cache::getCache(Cache::DISTR);

        return ($cache->get($name) !== null);
    }

    protected static function _emptySHM($class, $key)
    {
        $cache = Cache::getCache(Cache::DISTR);

        $cache->delete($key);
    }
}
