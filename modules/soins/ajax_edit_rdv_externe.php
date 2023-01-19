<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Soins\CRDVExterne;

CCanDo::checkEdit();

$rdv_externe_id = CView::get("rdv_externe_id", "ref class|CRDVExterne");
$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$rdv_externe = new CRDVExterne();
$rdv_externe->load($rdv_externe_id);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("rdv_externe", $rdv_externe);

$smarty->display("vw_edit_rdv_externe");
