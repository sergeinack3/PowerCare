<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;

CCanDo::checkEdit();

$modele_etiquette_id = CView::get("modele_etiquette_id", "ref class|CModeleEtiquette");

CView::checkin();

$modele_etiquette = new CModeleEtiquette();
$group_id         = CGroups::loadCurrent()->_id;

if ($modele_etiquette_id) {
  $modele_etiquette->load($modele_etiquette_id);
  $modele_etiquette->loadRefsNotes();
}

// Nouveau modèle d'étiquette dans le cas d'un changement d'établissement
if (!$modele_etiquette_id || $modele_etiquette->group_id != $group_id) {
  // Chargement des valeurs par défaut si pas de modele_etiquette_id
  $modele_etiquette = new CModeleEtiquette();
  $modele_etiquette->valueDefaults();
  $modele_etiquette->group_id = $group_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("modele_etiquette", $modele_etiquette);
$smarty->assign("classes", CModeleEtiquette::getContextClasses());
$smarty->assign("fields", CModeleEtiquette::getFields());
$smarty->assign("listfonts", CModeleEtiquette::$listfonts);

$smarty->display("inc_edit_modele_etiquette.tpl");
