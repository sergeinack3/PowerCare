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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkAdmin();

// Vérification des droits sur les praticiens
$praticiens = CConsultation::loadPraticiens(PERM_EDIT);

// Filtre
$filter = new CPlageconsult();

$where = array();

if ($filter->chir_id =  CValue::getOrSession("chir_id")) {
  $where["chir_id"] = "= '$filter->chir_id'";
}

if ($filter->_date_min = CValue::getOrSession("_date_min")) {
  $where[] = "date >= '$filter->_date_min'";
}

if ($filter->_date_max = CValue::getOrSession("_date_max")) {
  $where[] = "date <= '$filter->_date_max'";
}

// Chargement des plages
$plages = array();
if ($filter->chir_id) {
  /** @var CPlageconsult[] $plages */
  $plages = $filter->loadList($where, "date");
  foreach ($plages as $_plage) {
    $_plage->loadFillRate();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens", $praticiens);
$smarty->assign("plages"    , $plages    );
$smarty->assign("filter"    , $filter    );

$smarty->display("transfert_plageconsult.tpl");
