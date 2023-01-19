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

$campaign_id = CView::get('campaign_id', 'ref class|CImportCampaign notNull');
$class_name  = CView::get('class_name', 'str notNull');
$show_errors = CView::get('show_errors', 'enum list|all|valid|error default|valid');
$start       = CView::get('start', 'num default|0');
$step        = CView::get('step', 'num default|20');

CView::checkin();

$campaign = CImportCampaign::findOrFail($campaign_id);
$campaign->needsRead();

$entities = $campaign->loadEntityByClass($class_name, $show_errors, $start, $step);
$total    = $campaign->countEntityByClass($class_name, $show_errors);

$smarty = new CSmartyDP();
$smarty->assign('total', $total);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('entities', $entities);
$smarty->assign('campaign', $campaign);
$smarty->assign('class_name', $class_name);
$smarty->display('inc_list_campaign_objects');
