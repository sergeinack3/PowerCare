<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;

$guid        = CValue::get("source_guid");
$source_name = CValue::get("source_name");
$object_guid = CValue::get("object_guid");
$light       = CValue::get("light", false);

/** @var CExchangeSource $source */
$source = CMbObject::loadFromGuid($guid);
if (!$source->_id) {
    $source->name = $source_name;
}

if ($source instanceof CExchangeSourceAdvanced) {
    $source->loadRefLastStatistic();
}

if ($source instanceof CSourcePOP) {
    if (!$source->_id && $object_guid) {
        [$object_class, $object_id] = explode("-", $object_guid);

        /** @var CSourcePOP $source */
        $source->object_class = $object_class;
        $source->object_id    = $object_id;
        $source->role         = CAppUI::conf('instance_role');
    }

    $source->loadRefMetaObject();
} elseif ($source instanceof CSourceSMTP) {
    if (!$source->_id) {
        $source->role = CAppUI::conf('instance_role');
    }
}

$smarty = new CSmartyDP("modules/" . $source->_ref_module->mod_name);
$smarty->assign("source", $source);
$smarty->assign("light", $light);
$smarty->display($source->_class . "_inc_config.tpl");
