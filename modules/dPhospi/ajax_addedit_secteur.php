<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CSecteur;

$secteur_id = CValue::getOrSession("secteur_id");
$group      = CGroups::loadCurrent();

$secteur           = new CSecteur;
$secteur->group_id = $group->_id;
$secteur->load($secteur_id);
$secteur->loadRefsNotes();
$secteur->loadRefsServices();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("secteur", $secteur);
$smarty->display("inc_vw_secteur.tpl");

