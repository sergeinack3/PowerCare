<?php

namespace Ox\Mediboard\Patients\Services;

use Ox\Core\CAppUI;
use Ox\Mediboard\Patients\CPatient;

class PatientMergeService
{
    /** @var array $patients */
    private $patients;

    public function __construct($patients)
    {
        $this->patients = $patients;
    }

    /**
     * Fait plusieurs vérifications et retourne un tableau contenant des messages warning
     *
     * @return array
     * @throws \Exception
     */
    public function getWarnings(): array
    {
        $warnings = [];

        //Verification sur le matricule INS
        $ins = [];
        /** @var CPatient $patient */
        foreach ($this->patients as $patient) {
            $ins[] = $patient->loadRefPatientINSNIR()->ins_nir;
        }

        if (count(array_unique($ins)) !== 1) {
            $warnings[] = CAppUI::tr('CPatient-merge-warning-INS-conflict', [implode(', ', $ins)]);
        }

        return $warnings;
    }
}
