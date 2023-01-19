<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\CApp;
use Ox\Mediboard\Cabinet\CExamGir;

CCanDo::checkRead();

$sejour_id               = CView::get("sejour_id", "num");
$exam_gir_id             = CView::get("examgir_id", "num");
$digest                  = CView::get("digest", "bool");
$codage_coherence        = CView::get("codage_coherence", "str notNull default|A");
$codage_orientation      = CView::get("codage_orientation", "str notNull default|A");
$codage_toilette         = CView::get("codage_toilette", "str notNull default|A");
$codage_habillage        = CView::get("codage_habillage", "str notNull default|A");
$codage_alimentation     = CView::get("codage_alimentation", "str notNull default|A");
$codage_elimination      = CView::get("codage_elimination", "str notNull default|A");
$codage_transferts       = CView::get("codage_transferts", "str notNull default|A");
$codage_deplacements_int = CView::get("codage_deplacements_int", "str notNull default|A");
$codage_deplacements_ext = CView::get("codage_deplacements_ext", "str notNull default|A");
$codage_alerter          = CView::get("codage_alerter", "str notNull default|A");

CView::checkin();

$codages = [
  "coherence"        => $codage_coherence,
  "orientation"      => $codage_orientation,
  "toilette"         => $codage_toilette,
  "habillage"        => $codage_habillage,
  "alimentation"     => $codage_alimentation,
  "elimination"      => $codage_elimination,
  "transferts"       => $codage_transferts,
  "deplacements_int" => $codage_deplacements_int,
  "deplacements_ext" => $codage_deplacements_ext,
  "alerter"          => $codage_alerter,
];

$exam_gir = new CExamGir();
$exam_gir->findOrNew($exam_gir_id);
$exam_gir->computeScoreGir(0,$codages);
CApp::json($exam_gir->score_gir);
