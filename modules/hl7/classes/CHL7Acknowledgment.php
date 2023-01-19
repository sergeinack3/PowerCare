<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;


use DOMDocument;
use Ox\Interop\Hl7\Events\CHL7Event;

/**
 * Interface CHL7v2Acknowledgment
 * Acknowledgment HL7
 */
interface CHL7Acknowledgment
{
    /**
     * Construct
     *
     * @param CHL7Event $event Event HL7
     *
     * @return CHL7Acknowledgment
     */
    function __construct(CHL7Event $event = null);

    /**
     * Handle acknowledgment
     *
     * @param string $ack_hl7 HL7 acknowledgment
     *
     * @return DOMDocument
     */
    function handle($ack_hl7);

    /**
     * Get acknowledgment status
     *
     * @return string
     */
    function getStatutAcknowledgment();

    /**
     * Generate acknowledgment
     *
     * @param string       $ack_code       Acknowledgment code
     * @param string|array $mb_error_codes Mediboard error code
     * @param null         $hl7_error_code HL7 error code
     * @param string       $severity       Severity
     * @param null         $comments       Comments
     * @param null         $object         Object
     *
     * @return null|string
     */
    function generateAcknowledgment(
        $ack_code,
        $mb_error_codes,
        $hl7_error_code = null,
        $severity = null,
        $comments = null,
        $object = null
    );
}
