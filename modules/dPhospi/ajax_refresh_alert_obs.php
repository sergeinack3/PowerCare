<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CObservationMedicale;

CCanDo::checkRead();

$obs_id = CView::get("obs_id", "ref class|CObservationMedicale");

CView::checkin();

$obs = new CObservationMedicale();
$obs->load($obs_id);

$obs->loadRefAlerte();
$obs->_ref_alerte->loadRefHandledUser();

$smarty = new CSmartyDP();

$smarty->assign("obs", $obs);

$smarty->display("inc_vw_alerte_obs.tpl");