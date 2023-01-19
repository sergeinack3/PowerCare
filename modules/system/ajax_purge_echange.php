<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;

$source_exchange = array("CSourceFTP"  => "CExchangeFTP",
                         "CSourceSOAP" => "CEchangeSOAP");

CCanDo::checkAdmin();

$do_purge     = CValue::get("do_purge");
$date_max     = CValue::get("date_max");
$months       = CValue::get("months");
$max          = CValue::get("max", 1000);
$delete       = CValue::get("delete");
$source_class = CValue::get("source_class");

if ($months) {
  $date_max = CMbDT::date("- $months MONTHS");
}

if (!$date_max) {
  CAppUI::stepAjax("Merci d'indiquer une date fin de recherche.", UI_MSG_ERROR);
}

$exchange_class = $source_exchange[$source_class];
$exchange = new $exchange_class;
$ds = $exchange->_spec->ds;

// comptage des echanges à supprimer
$count_delete = 0;

if (!$do_purge) {
  if ($delete) {
    $date_max_delete = CMbDT::date("-6 MONTHS", $date_max);
    $where = array();
    $where["send_datetime"] = "< '$date_max_delete'";
    $count_delete = $exchange->countList($where);

    CAppUI::stepAjax("$exchange_class-msg-delete_count", UI_MSG_OK, $count_delete);
  }

  // comptage des echanges à vider qui ne le sont pas deja
  $where = array();
  $where["send_datetime"] = "< '$date_max'";
  $where["purge"] = "= '0'";
  $count_purge = $exchange->countList($where);

  CAppUI::stepAjax("$exchange_class-msg-purge_count", UI_MSG_OK, max(0, $count_purge - $count_delete));

  return;
}

// suppression effective
if ($delete) {
  $query = "DELETE FROM `{$exchange->_spec->table}`
    WHERE `send_datetime` < '$date_max_delete'
    LIMIT $max";

  $ds->exec($query);
  $count_delete = $ds->affectedRows();
  CAppUI::stepAjax("$exchange_class-msg-deleted_count", UI_MSG_OK, $count_delete);
}

// vidage des champs effective
$query = "UPDATE `{$exchange->_spec->table}`
  SET
  `purge` = '1',
  `output` = '',
  `input` = ''
  WHERE `send_datetime` < '$date_max'
  AND `purge` = '0'
  LIMIT $max";

$ds->exec($query);
$count_purge = $ds->affectedRows();
CAppUI::stepAjax("$exchange_class-msg-purged_count", UI_MSG_OK, $count_purge);

// on continue si on est en auto
if ($count_purge + $count_delete) {
  CAppUI::callbackAjax("Echange.purge", false, $source_class);
}