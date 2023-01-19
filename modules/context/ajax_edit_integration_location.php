<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Context\CContextualIntegrationLocation;

CCanDo::checkRead();

$location_id    = CView::get("location_id", "ref class|CContextualIntegrationLocation");
$integration_id = CView::get("integration_id", "ref class|CContextualIntegration");

CView::checkin();

$location = new CContextualIntegrationLocation();
$location->load($location_id);

if (!$location->_id) {
  $location->integration_id = $integration_id;
}

$smarty = new CSmartyDP();
$smarty->assign("location", $location);
$smarty->display("inc_edit_integration_location.tpl");