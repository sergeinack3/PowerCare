<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure\DocumentEntry;

use Ox\Core\CMbArray;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\Xds\Structure\CXDSCoded;

class CXDSConfidentiality extends CXDSCoded
{

    /**
     * Retourne un type confidentialité pour le masque passé en paramètre
     *
     * @param String $code Code
     *
     * @return self
     * @throws
     */
    public static function getMasquage(string $code): self
    {
        $values = CANSValueSet::loadEntries("confidentialityCode", $code);

        $confidentiality               = new self();
        $confidentiality->code         = CMbArray::get($values, "code");
        $confidentiality->code_system  = CMbArray::get($values, "codeSystem");
        $confidentiality->display_name = CMbArray::get($values, "displayName");

        return $confidentiality;
    }

}
