<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CFunctions;

class RelancePMSIService
{
    public function getRelanceFromUserOrFunction(string $type, int $id): array
    {
        $relances = [];
        if ($type == "user") {
            $relances = CRelancePMSI::loadRelances($id);
        } else {
            $function = CFunctions::findOrFail($id);
            $function->loadRefsUsers();
            foreach ($function->_ref_users as $_user) {
                $relances += CRelancePMSI::loadRelances($_user->_id);
            }
        }

        CStoredObject::massLoadFwdRef($relances, "patient_id");
        CStoredObject::massLoadFwdRef($relances, "sejour_id");

        foreach ($relances as $_relance) {
            $_relance->loadRefPatient();
            $_relance->loadRefSejour();
        }

        $order_nom = CMbArray::pluck($relances, "_ref_patient", "nom");
        $order_prenom = CMbArray::pluck($relances, "_ref_patient", "prenom");
        array_multisort(
            $order_nom,
            SORT_ASC,
            $order_prenom,
            SORT_ASC,
            $relances
        );

        return $relances;
    }
}
