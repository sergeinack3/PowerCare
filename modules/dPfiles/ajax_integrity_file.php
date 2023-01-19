<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFileIntegrity;
use Ox\Mediboard\System\Cron\CCronJob;

CCanDo::checkAdmin();

$cron_job_id = CView::get("cron_job_id", "ref class|CCronJob");

CView::checkin();

$cron_job = new CCronJob();
$cron_job->load($cron_job_id);

$integrity = new CFileIntegrity();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("cron_job", $cron_job);
$smarty->assign("params", $integrity->getParams());
$smarty->display("inc_integrity_files.tpl");
