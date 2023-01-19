<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;

$date    = CValue::get("date");
$chir_id = CValue::get("chir_id");

if (!$chir_id) {
  echo "<div class='small-info'>".CAppUI::tr("CConsultation-no_chir_planning")."</div>";
  CApp::rip();
}

$params = array(
  "debut"     => $date,
  "chir_id"   => $chir_id,
  "print"     => 1,
  "show_free" => 1
);

echo CApp::fetch("dPcabinet", "ajax_vw_planning", $params);
