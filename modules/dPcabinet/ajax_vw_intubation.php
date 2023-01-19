<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;

CCanDo::checkRead();

$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth");

CView::checkin();

$dossier_anesth = new CConsultAnesth();
$dossier_anesth->load($dossier_anesth_id);

$consult = $dossier_anesth->loadRefConsultation();
$consult->loadRefPatient();
$consult->loadRefPlageConsult();
$consult->loadListEtatsDents();

$smarty = new CSmartyDP();

$smarty->assign("consult_anesth", $dossier_anesth);
$smarty->assign("consult"       , $consult);
$smarty->assign("_is_dentiste"  , $consult->_is_dentiste);

$smarty->display("inc_consult_anesth/intubation.tpl");