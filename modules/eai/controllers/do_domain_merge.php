<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CMergeLog;

/**
 * Merge domains
 */
CCanDo::checkAdmin();

$d1_id          = CValue::post("domain_1_id");
$d2_id          = CValue::post("domain_2_id");
$incrementer_id = CValue::post("incrementer_id");
$actor_id       = CValue::post("actor_id");
$actor_class    = CValue::post("actor_class");
$tag            = CValue::post("tag");
$libelle        = CValue::post("libelle");

$d1 = new CDomain();
$d1->load($d1_id);
$d1->isMaster();

$d2 = new CDomain();
$d2->load($d2_id);
$d2->isMaster();

$and = null;
if ($d1->_is_master_ipp || $d2->_is_master_ipp) {
  $and .= "AND object_class = 'CPatient'";
}
if ($d1->_is_master_nda || $d2->_is_master_nda) {
  $and .= "AND object_class = 'CSejour'";
}

$ds = CSQLDataSource::get("std");

$tag_search =  ($tag == $d1->tag) ? $d2->tag : $d1->tag;

// 1. On change les tags de tous les objets liés à ce domaine
$query = "UPDATE `id_sante400` 
            SET `tag` = REPLACE(`tag`, '$tag_search', '$tag'),
                `last_update` = '". CMbDT::dateTime() . "'
            WHERE `tag` LIKE '%$tag_search%'
            $and;";
$ds->query($query);

// 2. On fusionne les domaines
$d1->bind($_POST);
$d1->_force_merge = true;

$merge_log = CMergeLog::logStart(CUser::get()->_id, $d1, [$d2], false);

try {
    $d1->merge(array($d2), false, $merge_log);
    $merge_log->logEnd();
} catch (Throwable $t) {
    $merge_log->logFromThrowable($t);
    CAppUI::stepAjax($t->getMessage(), UI_MSG_WARNING);
}

CAppUI::stepAjax("CDomain-merge");

CApp::rip();
