<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFileIntegrity;

CCanDo::checkAdmin();

$limit          = CView::post("limit", "num");
$last_file_date = CView::post("last_file_date", "dateTime");
$reset          = CView::post("reset", "bool default|0");

CView::checkin();

$do = new CDoObjectAddEdit("CCronJob");
$do->doBind();

$integrity = new CFileIntegrity();

$params = $integrity->getParams();

if ($limit || $last_file_date) {
  $params['limit'] = ($limit) ?: $params['limit'];
  $params['last_file_date'] = ($last_file_date) ?: $params['last_file_date'];

  $integrity->setCache($params);
}

if ($reset) {
  $integrity->resetCheck();
}

$do->doStore();

$do->doRedirect();
