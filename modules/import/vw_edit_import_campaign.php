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

CCanDo::checkEdit();

$campaign_id = CView::get('campaign_id', 'ref class|CImportCampaign');

CView::checkin();

$campaign = CImportCampaign::findOrNew($campaign_id);

$smarty = new CSmartyDP();
$smarty->assign('campaign', $campaign);
$smarty->display('vw_edit_import_campaign');