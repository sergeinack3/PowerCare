<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

$idex_id = CValue::get("idex_id");

$idex = new CIdSante400;
$idex->load($idex_id);

$filter               = new CIdSante400;
$filter->object_class = $idex->object_class;
$filter->object_id    = $idex->object_id;

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);
$idexs       = $filter->loadMatchingList(CGroups::loadCurrent()->_id);

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_cancel");
$idexs       = array_merge($idexs, $filter->loadMatchingList());

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_trash");
$idexs       = array_merge($idexs, $filter->loadMatchingList());

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_pa");
$idexs       = array_merge($idexs, $filter->loadMatchingList());

$tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);

// Chargement de l'objet afin de récupérer l'id400 associé
$object = new $filter->object_class;
$object->load($filter->object_id);
$object->loadNDA(CGroups::loadCurrent()->_id);

foreach ($idexs as $_idex) {
  // L'identifiant 400 coché
  if ($_idex->_id == $idex_id) {
    $_idex->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);
    if ($msg = $_idex->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    continue;
  }
  // L'ancien est à mettre en trash
  if ($_idex->id400 == $object->_NDA) {
    $_idex->tag = CAppUI::conf("dPplanningOp CSejour tag_dossier_trash") . $tag;
    if ($msg = $_idex->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
  }
}