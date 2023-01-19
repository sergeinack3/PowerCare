<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$lit_id         = CView::get("lit_id", "ref class|CLit");

CView::checkin();

$affectation = new CAffectation();
$affectation->load($affectation_id);

if (!$affectation->_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $affectation->sejour_id = $sejour_id;
  $affectation->entree    = $sejour->entree;
  $affectation->sortie    = $sejour->sortie;
}

echo json_encode(CAffectation::alertePlacement($lit_id, $affectation));