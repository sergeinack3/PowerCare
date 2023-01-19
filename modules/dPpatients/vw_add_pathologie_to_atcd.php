<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPathologie;

$pathologie_id = CView::get("pathologie_id", "ref class|CPathologie");
CView::checkin();

$pathologie = new CPathologie();
$pathologie->load($pathologie_id);

$antecedent        = new CAntecedent();
$antecedent->date  = $pathologie->debut;
$antecedent->rques = $pathologie->pathologie;

$patient = $pathologie->loadRefDossierMedical()->loadRefObject();

$smarty = new CSmartyDP();
$smarty->assign("antecedent", $antecedent);
$smarty->assign("patient", $patient);
$smarty->display("vw_add_pathologie_to_atcd.tpl");
