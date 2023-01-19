<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\DEC;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\Events\CHL7v2Event;

/**
 * Classe CHL7v2EventORU
 * Transporter des structures spécifiques dans des messages HL7
 */
class CHL7v2EventORU extends CHL7v2Event implements CHL7EventORU
{
    /** @var string */
    public $event_type = "ORU";

    /**
     * Construct
     *
     * @param string $i18n i18n
     */
    function __construct($i18n = null)
    {
        $this->profil    = "ORU";
        $this->msg_codes = [
            [
                $this->event_type,
                $this->code,
                "{$this->event_type}_{$this->struct_code}",
            ],
        ];
    }

    /**
     * Build event
     *
     * @param CMbObject $object Object
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($object)
    {
        parent::build($object);
    }
}
