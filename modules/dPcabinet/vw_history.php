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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkRead();

// Définition des variables
$consultation_id = CView::getRefCheckRead("consultation_id", "ref class|CConsultation");

CView::checkin();

$consult = new CConsultation();
$consult->load($consultation_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefConsultAnesth();

$consult->loadLogs();

foreach ($consult->_refs_dossiers_anesth as $_dossier_anesth) {
  $_dossier_anesth->loadLogs();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);

$smarty->display("vw_history.tpl");
