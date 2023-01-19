<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();

$rpu_id      = CView::get("rpu_id", "ref class|CRPU");
$mode_sortie = CView::get('mode_sortie', 'enum list|normal|transfert|mutation|deces');

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);

$consult = $rpu->loadRefConsult();

if ($consult && $consult->_id) {
  $consult->loadRefsActes();
}

if ($rpu->mutation_sejour_id) {
  $rpu->loadRefSejourMutation()->loadRefsActes();
}

$group = CGroups::loadCurrent();

$cotation     = CAppUI::conf("dPurgences Display check_cotation", $group);
$gemsa        = CAppUI::conf("dPurgences Display check_gemsa", $group);
$ccmu         = CAppUI::conf("dPurgences Display check_ccmu", $group);
$dp           = CAppUI::conf("dPurgences Display check_dp", $group);
$display_sfmu = CAppUI::conf("dPurgences CRPU display_motif_sfmu", $group);
$sfmu         = CAppUI::conf("dPurgences CRPU gestion_motif_sfmu", $group);

$value = array();

if ($cotation > 1) {
  if ($rpu->_ref_consult && !$rpu->_ref_consult->_ref_actes
    && ($rpu->_mode_sortie != 'mutation' && ($mode_sortie && $mode_sortie != 'mutation'))
  ) {
    $value[] = "CRPU-msg-missing_cotation";
  }
}

if ($gemsa > 1) {
  if (!$rpu->gemsa) {
    $value[] = "CRPU-gemsa";
  }
}

if ($ccmu > 1) {
  if (!$rpu->ccmu) {
    $value[] = "CRPU-ccmu";
  }
}

if ($dp > 1 && $rpu->orientation !== "PSA") {
  if (!$rpu->_DP) {
    $value[] = "CRPU-_DP";
  }
}

if ($display_sfmu && $sfmu > 1) {
  if (!$rpu->motif_sfmu) {
    $value[] = "CRPU-motif_sfmu";
  }
}

CApp::json($value);