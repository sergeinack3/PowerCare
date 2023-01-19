<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFileIntegrity;
use Ox\Mediboard\System\Cron\CCronJob;

$cron_job_id = CView::get("cron_job_id", "ref class|CCronJob");

CView::checkin();

// Statut
$cron = new CCronJob();
$cron->load($cron_job_id);

$integrity = new CFileIntegrity();
$params = $integrity->getParams();

$progression = 0;
$status = null;

if ($cron->_id && $params) {
  $total_entries_count = $params['file_entries_count'] + $params['db_entries_count'];

  if ($total_entries_count != 0) {
    $progression = round(($params['offset'] / $total_entries_count) * 100);
  }
  $status = $params['status'];
}

$smarty = new CSmartyDP();
$smarty->assign("progression", $progression);
$smarty->assign('status', $status);
$smarty->display("inc_status_integrity.tpl");
