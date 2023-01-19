<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Facturation\CFactureEtablissement;

CCanDo::checkEdit();
$facture_guid = CValue::get("object_guid");

/* @var CFactureEtablissement $facture*/
$facture = CMbObject::loadFromGuid($facture_guid);

$facture->loadRefsObjects();
$facture->updateMontants();
$facture->loadRefsReglements();

// Ajout de reglements
$use_mode_default = CAppUI::gconf("dPfacturation CReglement use_mode_default");
$facture->_new_reglement_patient["montant"] = $facture->_du_restant;
$facture->_new_reglement_patient["mode"] = $use_mode_default != "none"  ? $use_mode_default : "autre";

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("facture", $facture);
$smarty->assign("reload" , 1);

$smarty->display("inc_vw_reglements_etab");