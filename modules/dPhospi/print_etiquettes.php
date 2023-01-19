<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;

ignore_user_abort(true);

$printer_guid        = CView::get("printer_guid", "str");
$object_class        = CView::get("object_class", "str");
$object_id           = CView::get("object_id", "ref class|$object_class");
$modele_etiquette_id = CView::get("modele_etiquette_id", "ref class|CModeleEtiquette");

$spec_params = array(
  "str",
  "default" => array()
);
$params      = CView::get("params", $spec_params);

CView::checkin();

/** @var CMbObject $object */
$object = new $object_class;
$object->load($object_id);

$fields = array();

$object->completeLabelFields($fields, $params);

// Chargement des mod�les d'�tiquettes
$modele_etiquette = new CModeleEtiquette();
$modele_etiquette->load($modele_etiquette_id);

if ($modele_etiquette->_id) {
  $modele_etiquette->completeLabelFields($fields, $params);
  $modele_etiquette->replaceFields($fields);
  $modele_etiquette->printEtiquettes($printer_guid);
  CApp::rip();
}

$where = array();

$where['object_class'] = " = '$object_class'";
$where["group_id"]     = " = '" . CGroups::loadCurrent()->_id . "'";

if (count($modeles_etiquettes = $modele_etiquette->loadList($where))) {
  // TODO: faire une modale pour proposer les mod�les d'�tiquettes
  $first_modele = reset($modeles_etiquettes);
  $first_modele->completeLabelFields($fields, $params);
  $first_modele->replaceFields($fields);
  $first_modele->printEtiquettes($printer_guid);
}
else {
  CAppUI::stepAjax("Aucun mod�le d'�tiquette configur� pour l'objet " . CAppUI::tr($object_class));
}
