<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CConsommationMateriel;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\COperation;

class ProtocolesOpsLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function viewMaterielOperation(): void
    {
        $this->checkPermEdit();

        $operation_id = CView::get("operation_id", "ref class|COperation");
        $mode         = CView::get("mode", "str");

        CView::checkin();

        $operation = COperation::findOrFail($operation_id);

        CAccessMedicalData::logAccess($operation);

        $operation->loadRefsProtocolesOperatoires();
        $operation->loadRefsConsultAnesth();
        $operation->loadRefChir();
        $operation->loadRefsCommande();
        $operation->canDo();

        $sejour = $operation->loadRefSejour();
        $sejour->loadRefPatient();
        $sejour->loadPatientBanner();
        $sejour->_ref_patient->loadRefsNotes();

        $materiel_op               = new CMaterielOperatoire();
        $materiel_op->operation_id = $operation->_id;

        $readonly = $this->getReadOnly($operation);


        $this->renderSmarty("vw_materiel_operation", [
            "operation"    => $operation,
            "materiel_op"  => $materiel_op,
            "mode"         => $mode,
            "consommation" => new CConsommationMateriel(),
            "readonly"     => $readonly,
        ]);
    }

    private function getReadOnly(COperation $operation): int
    {
        if (
            $operation->consommation_user_id ||
            (
                CAppUI::gconf("dPsalleOp COperation numero_panier_mandatory") && !$operation->numero_panier
            )
        ) {
            return 1;
        }

        return 0;
    }

    /**
     * @throws Exception
     */
    public function editMaterielOp(): void
    {
        $this->checkPermEdit();

        $materiel_operatoire_id  = CView::get("materiel_operatoire_id", "ref class|CMaterielOperatoire");
        $protocole_operatoire_id = CView::get("protocole_operatoire_id", "ref class|CProtocoleOperatoire");

        CView::checkin();

        $materiel_op = CMaterielOperatoire::findOrNew($materiel_operatoire_id);

        if ($materiel_op->_id) {
            $materiel_op->loadRelatedProduct();
        } else {
            $materiel_op->protocole_operatoire_id = $protocole_operatoire_id;
        }
        $this->renderSmarty(
            "inc_edit_materiel_op",
            [
                "materiel_op" => $materiel_op,
            ]
        );
    }
}
