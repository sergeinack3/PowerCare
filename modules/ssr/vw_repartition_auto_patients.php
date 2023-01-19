<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CPlateauTechnique;

global $m;
CCanDo::checkRead();
$date = CView::get("date", "date default|" . CMbDT::date("+1 day"));
CView::checkin();

// Plateaux disponibles
$where                = array();
$where[]              = "type = '$m' OR type IS NULL";
$where["repartition"] = " = '1'";
$plateau              = new CPlateauTechnique();
$plateaux             = $plateau->loadGroupList($where);

$techniciens = array("nb" => 0, "plateau" => 0);
/** @var CPlateauTechnique[] $plateaux */
foreach ($plateaux as $_plateau) {
  $_plateau->loadRefsTechniciens();
  $techniciens["nb"] += count($_plateau->_ref_techniciens);
  if (count($_plateau->_ref_techniciens) == 1) {
    $techniciens["plateau"] = $_plateau;
  }
}

//La répartition automatique peut se faire si un seul plateau technique est disponible avec une seul technicien
if ($techniciens["nb"] != 1) {
  return;
}

$plateau       = $techniciens["plateau"];
$technicien    = reset($plateau->_ref_techniciens);
$technicien_id = $technicien->_id;

//Récupération des séjours à répartir
$sejours = CBilanSSR::loadSejoursSSRfor(null, $date, false);
foreach ($sejours as $_sejour) {
  $bilan_ssr = $_sejour->loadRefBilanSSR();
  if (!$bilan_ssr->_id) {
    $bilan_ssr->sejour_id = $_sejour->_id;
  }
  $bilan_ssr->technicien_id = $technicien_id;
  if ($msg = $bilan_ssr->store()) {
    return $msg;
  }
}

return;
