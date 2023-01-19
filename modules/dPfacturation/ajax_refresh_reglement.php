<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CReglement;

CCanDo::checkEdit();
$facture_id   = CValue::getOrSession("facture_id");
$object_class = CValue::getOrSession("object_class");

$facture = new $object_class;
$facture->load($facture_id);
$facture->loadRefsObjects();
$facture->loadRefsReglements();
$facture->loadRefsNotes();

$reglement = new CReglement();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("facture"       , $facture);
$smarty->assign("reglement"     , $reglement);
$smarty->assign("date"          , CMbDT::date());

$smarty->display("inc_vw_reglements");