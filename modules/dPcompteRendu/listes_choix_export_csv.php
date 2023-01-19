<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CListeChoix;

/**
 * Export CSV des listes de choix
 */
CCanDo::checkRead();

$list       = CValue::get('ids', array());
$owner_guid = CValue::get('owner_guid');

if (!is_array($list)) {
  $list = explode("-", $list);
}

$owner_view = "";

if ($owner_guid) {
  $owner = CMbObject::loadFromGuid($owner_guid);
  $owner_view = " - $owner->_view";
}

$out = fopen('php://output', 'w');
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=\"Listes de choix$owner_view.csv\"");

$liste_choix = new CListeChoix();
fputcsv($out, array_keys($liste_choix->getCSVFields()));

foreach ($list as $id) {
  if (!$liste_choix->load($id)) {
    continue;
  }
  
  fputcsv($out, $liste_choix->getCSVFields());
}