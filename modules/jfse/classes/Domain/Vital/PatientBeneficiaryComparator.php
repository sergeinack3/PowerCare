<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Exception;
use Ox\Core\IComparator;
use Ox\Mediboard\Patients\CPatient;

class PatientBeneficiaryComparator implements IComparator
{
    /**
     * @param Beneficiary $a
     * @param CPatient    $b
     *
     * @throws Exception
     */
    public function equals($a, $b): bool
    {
        $beneficiary    = &$a;
        $patient = &$b;

        if (!($beneficiary instanceof Beneficiary && $patient instanceof CPatient)) {
            throw new Exception(
                'Wrong parameters. Expected VitalCard as first parameter and CPatient as ' .
                'second parameter'
            );
        }

        $vc_patient = $beneficiary->getPatient();

        /* In the ApCV, the patient may have no last name, or no usual name */
        if (!$this->compareStr($vc_patient->getFirstName(), $patient->prenom)) {
            return false;
        } elseif ($vc_patient->getLastName() && !$this->compareStr($vc_patient->getLastName(), $patient->nom)) {
            return false;
        } elseif ($vc_patient->getBirthDate() !== $patient->naissance) {
            return false;
        } elseif (
            $patient->matricule && $beneficiary->getFullCertifiedNir()
            && $patient->matricule !== $beneficiary->getFullCertifiedNir()
        ) {
            return false;
        }

        return true;
    }

    private function compareStr(string $str1, string $str2): bool
    {
        return (strtolower($str1) === strtolower($str2));
    }
}
