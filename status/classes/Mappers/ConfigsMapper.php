<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Mappers;

class ConfigsMapper
{
    public $root_dir;
    public $base_url;
    public $instance_role;
    public $bdd;
    public $memory;
    public $session;
    public $mutex;
    public $is_maintenance;
    public $is_maintenance_allow_admin;
    public $servers_ip;

    /**
     * ConfigsMapper constructor.
     *
     * @param object $config
     */
    public function __construct($config)
    {
        $this->root_dir      = $config->get('root_dir');
        $this->base_url      = $config->get('base_url');
        $this->instance_role = $config->get('instance_role');
        $this->bdd           = [
            'bdd_type' => $config->get('db std dbtype'),
            'bdd_host' => $config->get('db std dbhost'),
            'bdd_name' => $config->get('db std dbname'),
            'bdd_user' => $config->get('db std dbuser'),
        ];
        $this->memory        = [
            'shared_memory'             => $config->get('shared_memory'),
            'shared_memory_distributed' => $config->get('shared_memory_distributed'),
            'shared_memory_params'      => $config->get('shared_memory_params'),
        ];

        $this->session = $config->get('session_handler');

        $this->mutex = [
            'session_mutex'      => $config->get('session_handler_mutex_type'),
            'mutex_redis'        => (bool)$config->get('mutex_drivers CMbRedisMutex'),
            'mutex_redis_params' => (array)$config->get('mutex_drivers_params CMbRedisMutex'),
            'mutex_apc'          => (bool)$config->get('mutex_drivers CMbAPCMutex'),
            'mutex_files'        => (bool)$config->get('mutex_drivers CMbFileMutex'),
        ];

        $this->is_maintenance             = (bool)$config->get('offline');
        $this->is_maintenance_allow_admin = (bool)$config->get('offline_non_admin');
        $this->servers_ip                 = $config->get('servers_ip');
    }
}
