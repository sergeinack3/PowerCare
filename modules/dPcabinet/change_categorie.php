<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;

CCanDo::checkRead();

$consult_id = CValue::get("consult_id");

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$prat = $consult->loadRefPlageConsult()->loadRefChir();

$categorie = new CConsultationCategorie();
$categories = $categorie->loadList(
  array(
    "`function_id` = '$prat->function_id' OR `praticien_id` = '$prat->_id'"
  ),
  "nom_categorie ASC"
);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("consult"   , $consult);
$smarty->assign("categories", $categories);
$smarty->display("change_categorie.tpl");
