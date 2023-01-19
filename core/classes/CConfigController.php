<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Core\Redis\CRedisClient;

/**
 * CConfigController class
 *
 * Generic controller to manage Mediboard configurations
 */
class CConfigController
{
    public $configs;
    public $config_db;
    public $ajax;
    public $module;

    /**
     * Constructor
     *
     * @param string $module Module name
     */
    function __construct($module)
    {
        global $mbpath;
        $mbpath = "";

        CMbArray::extract($_POST, "m");
        CMbArray::extract($_POST, "dosql");
        CMbArray::extract($_POST, "suppressHeaders");
        CMbArray::extract($_POST, "@config");

        $this->ajax      = CMbArray::extract($_POST, "ajax");
        $this->configs   = $_POST;
        $this->config_db = CAppUI::conf("config_db");
        $this->module    = $module;
    }

    /**
     * Update configurations from post request
     *
     * @return void
     */
    function updateConfigs()
    {
        $module = CModule::getInstalled($this->module);
        if (!$module || !$module->canAdmin()) {
            CAppUI::redirect();
        }

        $this->updateDbConfigs();

        global $dPconfig;
        $mbConfig = new CMbConfig();

        try {
            if ($mbConfig->update($_POST)) {
                CAppUI::setMsg("Configure-success-modify");
            }
        } catch (Exception $e) {
            CAppUI::setMsg("Configure-failed-modify", UI_MSG_ERROR, $e->getMessage());
        }

        $mbConfig->load();
        $dPconfig = $mbConfig->values;

        if ($this->config_db) {
            CMbConfig::loadValuesFromDB();
        }

        // Cas Ajax
        if ($this->ajax) {
            CSQLDataSource::$log = false;
            CRedisClient::$log   = false;

            echo CAppUI::getMsg();
            CApp::rip();
        }
    }

    /**
     * Handle database configurations
     *
     * @return void
     */
    private function updateDbConfigs()
    {
        if ($this->config_db) {
            // Ne pas inclure de config relatives aux bases de données
            foreach ($_POST as $key => $_config) {
                if (in_array($key, CMbConfig::$forbidden_values) || $key == "db") {
                    unset($this->configs[$key]);
                } else {
                    unset($_POST[$key]);
                }
            }

            $this->configs = array_map_recursive('stripslashes', $this->configs);

            $list = [];
            CMbConfig::buildConf($list, $this->configs, null);

            $ds = CSQLDataSource::get("std");
            foreach ($list as $key => $value) {
                $query = "INSERT INTO `config_db`
                  VALUES (%1, %2)
                  ON DUPLICATE KEY UPDATE value = %2";
                $query = $ds->prepare($query, $key, $value);

                if ($ds->exec($query) === false) {
                    CAppUI::setMsg("Configure-failed-modify", UI_MSG_ERROR);
                } else {
                    CAppUI::setMsg("Configure-success-modify");
                }
            }
        }
    }
}
