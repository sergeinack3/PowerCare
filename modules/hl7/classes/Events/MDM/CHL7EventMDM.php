<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MDM;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventMDM
 * Medical Records/Information Management
 */
interface CHL7EventMDM
{
    /**
     * Construct
     *
     * @return CHL7EventMDM
     */
    function __construct();

    /**
     * Build ORU message
     *
     * @param CMbObject $object object
     *
     * @return mixed
     */
    function build($object);
}
