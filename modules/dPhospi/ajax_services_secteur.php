<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CSecteur;

$secteur_id = CValue::get("secteur_id");

$secteur = new CSecteur;
$secteur->load($secteur_id);
$secteur->loadRefsServices();

$smarty = new CSmartyDP;

$smarty->assign("secteur", $secteur);

$smarty->display("inc_services_secteur.tpl");
