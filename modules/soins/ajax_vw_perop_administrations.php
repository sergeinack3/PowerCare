<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Mpm\CPrescriptionLineMixItem;

CCanDo::checkRead();
$prescription_id      = CView::get("prescription_id", "ref class|CPrescription");
$sejour_id            = CView::get("sejour_id", "ref class|CSejour");
$show_administrations = CView::get("show_administrations", "bool default|0");
CView::checkin();

$sejour         = CSejour::find($sejour_id);
$last_operation = $sejour->loadRefLastOperation(true);
$operations     = $sejour->loadRefsOperations(["annulee" => "= '0'"]);

CAccessMedicalData::logAccess($sejour);

// Tri des gestes et administrations perop par ordre chronologique
$perops = [];

// Chargement des administrations
$administrations              = CAdministration::getPerop($prescription_id, true, $last_operation->_id);
$count_administrations_gestes = 0;

foreach ($administrations as $_adm) {
    $_adm->loadTargetObject();
    $_adm->loadRefsFwd();
    $object = $_adm->_ref_object;

    if ($object instanceof CPrescriptionLineMedicament || $object instanceof CPrescriptionLineMixItem) {
        $_produit = $object->_ref_produit;
        $_produit->loadRapportUnitePriseByCIS($object);
        $_produit->updateRatioMassique();

        if ($_produit->_ratio_mg) {
            $_adm->_quantite_mg = $_adm->quantite / $_produit->_ratio_mg;
        }

        [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
            $object,
            $_adm->quantite
        );

        $_adm->_ref_object->_unite_livret = $unite_lt;
        $_adm->_ref_object->_qte_livret   = $qte_lt;
    }

    $section              = CAdministration::getSectionPerop($last_operation->_id, $_adm->dateTime);
    $_adm->_perop_section = $section;

    $perops[$_adm->dateTime][$_adm->_guid] = $_adm;
    $count_administrations_gestes++;
}

if ($sejour->_ref_prescription_sejour && $sejour->_ref_prescription_sejour->_id) {
    // Chargements des perfusions pour afficher les poses et les retraits
    $prescription_line_mix                  = new CPrescriptionLineMix();
    $prescription_line_mix->prescription_id = $prescription_id;
    $prescription_line_mix->perop           = 1;
    /** @var CPrescriptionLineMix[] $mixes */
    $mixes = $prescription_line_mix->loadMatchingList();

    CStoredObject::massLoadFwdRef($mixes, "praticien_id");

    foreach ($mixes as $_mix) {
        $_mix->loadRefPraticien();
        $_mix->loadRefsLines();
        if ($_mix->date_pose && $_mix->time_pose) {
            $section                                      = CAdministration::getSectionPerop(
                $last_operation->_id,
                $_mix->_pose
            );
            $_mix->_perop_section                         = $section;
            $perops[$section][$_mix->_pose][$_mix->_guid] = $_mix;
        }
        if ($_mix->date_retrait && $_mix->time_retrait) {
            $section                                         = CAdministration::getSectionPerop(
                $last_operation->_id,
                $_mix->_retrait
            );
            $_mix->_perop_section                            = $section;
            $perops[$section][$_mix->_retrait][$_mix->_guid] = $_mix;
        }
        $count_administrations_gestes++;
    }
}

// Load the Perop gestures
$anesths_perop = $last_operation->loadRefsAnesthPerops();

if (!$show_administrations) {
    foreach ($anesths_perop as $_anesth_perop) {
        $_anesth_perop->updateFormFields();
        $_anesth_perop->loadRefUser();

        $section                       = CAdministration::getSectionPerop(
            $last_operation->_id,
            $_anesth_perop->datetime
        );
        $_anesth_perop->_perop_section = $section;

        $perops[$_anesth_perop->datetime][$_anesth_perop->_guid] = $_anesth_perop;
        $count_administrations_gestes++;
    }
}

ksort($perops);

$smarty = new CSmartyDP();
$smarty->assign("perops", $perops);
$smarty->assign("last_operation", $last_operation);
$smarty->assign("operations", $operations);
$smarty->assign("count_administrations_gestes", $count_administrations_gestes);
$smarty->assign("show_administrations", $show_administrations);
$smarty->assign("prescription_id", $prescription_id);
$smarty->display("inc_vw_perop_administrations");
