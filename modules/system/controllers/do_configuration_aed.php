<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CacheManager;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationModelManager;

$configs        = CValue::post("c");
$object_guid    = CValue::post("object_guid");
$mode           = CValue::post("mode");
$static_configs = (bool)CValue::post("static_configs");

// Strip slashed because added in store
$configs = CMbArray::mapRecursive("stripslashes", $configs);

$strategy = CConfigurationModelManager::getStrategy($mode);

if ($object_guid != 'global') {
    $object_guids = explode('|', $object_guid);
    foreach ($object_guids as $_object_guid) {
        $object = CMbObject::loadFromGuid($_object_guid);
        $object->needsRead();

        $messages = CConfiguration::setConfigs($configs, $object, $strategy);
    }
} else {
    $messages = CConfiguration::setConfigs($configs, null, $strategy, $static_configs);
}

$clear_modules = [];
$paths         = array_keys($configs);

foreach ($paths as $_path) {
    $parts           = explode(' ', $_path, 2);
    $clear_modules[] = $parts[0];
}

$clear_modules = array_unique($clear_modules);

foreach ($messages as $msg) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
}

CAppUI::setMsg("CConfiguration-msg-modify");

echo CAppUI::getMsg();
CApp::rip();
