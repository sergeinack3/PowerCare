<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Import\Exceptions;

use Ox\Import\Framework\Exception\PersisterException;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Patients\CPatient;

/**
 * Persister exception for Cabinet
 */
class CabinetPersisterException extends PersisterException
{
    public static function alreadyVaccinated(CInjection $injection): self
    {
        $patient = CPatient::find($injection->patient_id);

        return new self(
            'CabinetPersisterException-This injection was previously given for the %s vaccine type at %s months of age',
            $injection->_type_vaccin,
            $injection->recall_age,
            $patient->_view
        );
    }
}
