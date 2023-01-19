<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Matcher;

use Ox\Import\Framework\Matcher\DefaultMatcher;
use Ox\Import\GenericImport\Exception\GenericMatcherException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Matcher for generic import
 */
class GenericMatcher extends DefaultMatcher
{
    /**
     * @param CPatient $patient
     *
     * @return CPatient
     * @throws GenericMatcherException
     */
    public function matchPatient(CPatient $patient): CPatient
    {
        $num_ipp = $patient->_IPP;
        if ($num_ipp) {
            $ipp = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP(), $num_ipp);
            if ($ipp->_id && $ipp->object_id) {
                return $ipp->loadFwdRef("object_id");
            }
        }

        $patient = parent::matchPatient($patient);

        if ($patient->_id && $num_ipp) {
            $patient->_IPP = null;
            $patient->loadIPP();
            if ($patient->_IPP && $patient->_IPP !== $num_ipp) {
                throw GenericMatcherException::patientHasAlreadyAnIpp($num_ipp, $patient->_IPP);
            }
            $patient->_IPP = $num_ipp;
        }

        return $patient;
    }

    /**
     * @param CSejour $sejour
     *
     * @return CSejour
     * @throws GenericMatcherException
     */
    public function matchSejour(CSejour $sejour): CSejour
    {
        $num_nda = $sejour->_NDA;
        if ($num_nda) {
            $nda = CIdSante400::getMatch($sejour->_class, CSejour::getTagNDA(), $num_nda);
            if ($nda->_id && $nda->object_id) {
                return $nda->loadFwdRef("object_id");
            }
        }

        $sejour = parent::matchSejour($sejour);

        if ($sejour->_id && $num_nda) {
            $sejour->_NDA = null;
            $sejour->loadNDA();
            if ($sejour->_NDA && $sejour->_NDA !== $num_nda) {
                throw GenericMatcherException::sejourHasAlreadyAnNda($num_nda, $sejour->_NDA);
            }
            $sejour->_NDA = $num_nda;
        }

        return $sejour;
    }
}
