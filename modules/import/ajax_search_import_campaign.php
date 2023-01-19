<?php
/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\Framework\Entity\CImportCampaign;

CCanDo::checkRead();

$name              = CView::get('name', 'str');
$min_creation_date = CView::get('_creation_date_min', 'dateTime');
$max_creation_date = CView::get('_creation_date_max', 'dateTime');
$min_closing_date  = CView::get('_closing_date_min', 'dateTime');
$max_closing_date  = CView::get('_closing_date_max', 'dateTime');

CView::checkin();

$campaign = new CImportCampaign();
$ds       = $campaign->getDS();

$where = [];

if ($name) {
  $where['name'] = $ds->prepareLike("%{$name}%");
}

if ($min_creation_date) {
  $where[] = $ds->prepare('`creation_date` >= ?', $min_creation_date);
}

if ($max_creation_date) {
  $where[] = $ds->prepare('`creation_date` <= ?', $max_creation_date);
}

if ($min_closing_date) {
  $where[] = $ds->prepare('`closing_date` >= ?', $min_closing_date);
}

if ($max_closing_date) {
  $where[] = $ds->prepare('`closing_date` <= ?', $max_closing_date);
}

$campaigns = $campaign->loadList($where, 'creation_date ASC');

$smarty = new CSmartyDP();
$smarty->assign('campaigns', $campaigns);
$smarty->display('inc_list_import_campaigns');