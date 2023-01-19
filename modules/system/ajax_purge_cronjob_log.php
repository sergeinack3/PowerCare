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
use Ox\Core\CView;
use Ox\Mediboard\System\Cron\CCronJobLog;

CCanDo::checkAdmin();

$do_purge = CView::get("do_purge", 'str');
$date_max = Cview::get("_date_max", 'dateTime');
$months   = Cview::get("months", 'bool default|0');
$max      = Cview::get("max", 'num default|10');
$delete   = Cview::get("delete", 'bool default|0');

CView::checkin();

if ($months) {
  $date_max = CMbDT::date("- $months MONTHS");
}

if (!$date_max) {
  CAppUI::stepAjax("Merci d'indiquer une date fin de recherche.", UI_MSG_ERROR);
}

$cronjob_log = new CCronJobLog();
$ds          = $cronjob_log->_spec->ds;

// comptage des echanges à supprimer
$count_delete    = 0;
$date_max_delete = $delete ? CMbDT::date("-6 MONTHS", $date_max) : $date_max;

$where                   = array();
$where["start_datetime"] = "< '$date_max_delete'";
$count_to_delete         = $cronjob_log->countList($where);

CAppUI::stepAjax("{$cronjob_log->_class}-msg-delete_count", UI_MSG_OK, $count_to_delete);

if (!$do_purge) {
  return;
}

$query = "DELETE FROM `{$cronjob_log->_spec->table}`
  WHERE `start_datetime` < '$date_max_delete'
  LIMIT $max";

$ds->exec($query);
$count_delete = $ds->affectedRows();
CAppUI::stepAjax("{$cronjob_log->_class}-msg-deleted_count", UI_MSG_OK, $count_delete);

// on continue si on est en auto
if ($count_to_delete + $count_delete) {
  echo "<script type='text/javascript'>CronJob.purge();</script>";
}
