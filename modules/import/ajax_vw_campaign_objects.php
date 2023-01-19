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
$show_errors = CView::get('show_errors', 'enum list|all|valid|error default|valid');

CView::setSession('campaign_id', $campaign_id);
CView::setSession('show_errors', $show_errors);

CView::checkin();

$campaign = CImportCampaign::findOrFail($campaign_id);

$classes = $campaign->getImportedEntities($show_errors, true);

$smarty = new CSmartyDP();
$smarty->assign('campaign', $campaign);
$smarty->assign('classes', $classes);
$smarty->assign('show_errors', $show_errors);
$smarty->display('inc_vw_campaign_objects');
