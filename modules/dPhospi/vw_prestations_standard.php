<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Mediusers\CMediusers;

$group = CGroups::loadCurrent();

// Récupération des prestations
$presta           = new CPrestation;
$presta->group_id = $group->_id;
$prestations      = $presta->loadMatchingList("nom");
foreach ($prestations as $_prestation) {
  $_prestation->loadRefGroup();
  $_prestation->loadRefsNotes();
}

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("prestations", $prestations);
$smarty->assign("prestation", $presta);
$smarty->assign("etablissements", $etablissements);

$smarty->display("vw_prestation_standard.tpl");