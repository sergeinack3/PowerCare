<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Exception;

use Ox\Import\Framework\Exception\ImportException;

/**
 * Exception for generic matcher
 */
class GenericMatcherException extends ImportException
{
    /**
     * @param string $import_ipp
     * @param string $exist_ipp
     *
     * @return static
     */
    public static function patientHasAlreadyAnIpp(string $import_ipp, string $exist_ipp): self
    {
        return new self(
            'GenericMatcherException-Error-Patient unabled to import ipp, patient has already an ipp',
            $import_ipp,
            $exist_ipp
        );
    }

    /**
     * @param string $import_nda
     * @param string $exist_nda
     *
     * @return static
     */
    public static function sejourHasAlreadyAnNda(string $import_nda, string $exist_nda): self
    {
        return new self(
            'GenericMatcherException-Error-Sejour unabled to import NDA, sejour has already an NDA',
            $import_nda,
            $exist_nda
        );
    }
}
