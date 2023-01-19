<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Soins\CSejourTask;

CCanDo::checkAdmin();
$type = CValue::get("type", "check");

$where            = [];
$where["realise"] = " = '1'";
$where[]          = "date_realise IS NULL";
$where[]          = "author_realise_id IS NULL";
$where[]          = "author_id IS NOT NULL";

$task = new CSejourTask();
if ($type == "repair") {
    $tasks = $task->loadList($where, null, 100);
} else {
    $tasks = $task->loadIds($where);
}

CAppUI::stepAjax("Taches à corriger: " . count($tasks), UI_MSG_OK);

if ($type == "repair") {
    $correction = 0;
    foreach ($tasks as $_task) {
        /* @var CSejourTask $_task */
        $_task->date_realise      = $_task->date;
        $_task->author_realise_id = $_task->author_id;
        if ($msg = $_task->store()) {
            CApp::log($msg);
        } else {
            $correction++;
        }
    }
    CAppUI::stepAjax("Taches corrigés: $correction", UI_MSG_OK);
}
