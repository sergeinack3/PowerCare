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

$campaign_id = CView::get('campaign_id', 'ref class|CImportCampaign', true);
$show_errors = CView::get('show_errors', 'enum list|all|valid|error', true);

CView::checkin();

$show_errors = ($show_errors) ?: 'valid';

// Check read on campaign first
$campaign = CImportCampaign::findOrNew($campaign_id);
$campaign->needsRead();

$all_campaign = $campaign->loadList();

$smarty = new CSmartyDP();
$smarty->assign('show_errors', $show_errors);
$smarty->assign('campaign', $campaign);
$smarty->assign('all_campaign', $all_campaign);
$smarty->display('vw_campaign_objects');