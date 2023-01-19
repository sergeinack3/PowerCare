<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CWhiteList;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$emails = CView::get("emails", "str");

CView::checkin();

if (!is_array($emails)) {
  $emails = array($emails);
}

$result = array();

$whitelist = new CWhiteList();

$where = array(
  "group_id" => "= '" . CGroups::get()->_id . "'"
);

$whitelists = $whitelist->loadColumn("email", $where);

foreach ($emails as $_email) {
  // Absence de paramétrage (toutes les adresses sont refusées)
  $is_white = false;
  if (!count($whitelists)) {
    $result[] = $_email;
    continue;
  }
  foreach ($whitelists as $_whitelist) {
    // On espace le point
    $_whitelist = preg_replace("/\./", "\\.", $_whitelist);
    // On prend en compte l'étoile pour la regexp
    $_whitelist = preg_replace("/\*/", ".*", $_whitelist);

    if (preg_match("/$_whitelist/", $_email)) {
      $is_white = true;
      break;
    }
  }
  if (!$is_white) {
    $result[] = $_email;
  }
}

echo CMbArray::toJSON($result);