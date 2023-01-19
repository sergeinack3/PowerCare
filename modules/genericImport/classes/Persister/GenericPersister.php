<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Persister;

use Exception;
use Ox\Import\Framework\Exception\PersisterException;
use Ox\Import\Framework\Persister\DefaultPersister;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Persister for generic import
 */
class GenericPersister extends DefaultPersister
{
    /**
     * @param CPatient $patient
     *
     * @return CPatient
     * @throws PersisterException
     * @throws Exception
     */
    protected function persistPatient(CPatient $patient): CPatient
    {
        $patient->_generate_IPP   = !$patient->_IPP && $this->configuration['generate_ipp'];
        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_IMPORT;

        $patient = $this->persist($patient);

        if ($patient->_IPP) {
            $ipp = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP(), null, $patient->_id);
            if (!$ipp->_id) {
                $ipp->id400 = $patient->_IPP;

                if ($msg = $ipp->store()) {
                    throw new PersisterException($msg);
                }
            }
        }

        return $patient;
    }

    /**
     * @param CSejour $sejour
     *
     * @return CSejour
     * @throws PersisterException
     * @throws Exception
     */
    protected function persistSejour(CSejour $sejour): CSejour
    {
        $sejour->_generate_NDA   = !$sejour->_NDA && $this->configuration['generate_nda'];
        $sejour = $this->persist($sejour);

        if ($sejour->_NDA) {
            $nda = CIdSante400::getMatch($sejour->_class, CSejour::getTagNDA(), null, $sejour->_id);
            if (!$nda->_id) {
                $nda->id400 = $sejour->_NDA;

                if ($msg = $nda->store()) {
                    throw new PersisterException($msg);
                }
            }
        }

        return $sejour;
    }
}
