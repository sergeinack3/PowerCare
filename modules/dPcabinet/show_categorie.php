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

$consult_id = CView::getRefCheckRead("consult_id", "ref class|CConsultation");

CView::checkin();

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$categorie = $consult->loadRefCategorie();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("categorie", $categorie);
$smarty->display("inc_icone_categorie_consult.tpl");
