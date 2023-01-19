<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;

/**
 * Class CHL7v2SegmentERR
 * ERR - Represents an HL7 ERR message segment (Error)
 */
class CHL7v2SegmentERR extends CHL7v2Segment
{
    /** @var string */
    public $name = "ERR";

    /** @var CHL7v2Acknowledgment */
    public $acknowledgment;

    /** @var CHL7v2Error */
    public $error;

    /**
     * Build ERR segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return null
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        $version = $event->message->version;
        $sender  = $event->_sender;

        $error = $this->error;
        $acknowledgment = $this->acknowledgment;

        $data = [];

        if ($error) {
            // ERR-1: Error Code and Location (ELD) (optional repeating)
            if ($version < "2.5") {
                $data[] = $error->getCodeLocation();

                return $this->fill($data);
            }
            $data[] = null;

            // ERR-2: Error Location (ERL) (optional repeating)
            $data[] = [
                $error->getLocation(),
            ];

            // ERR-3: HL7 Error Code (CWE)
            $data[] = [
                $error->getHL7Code(),
            ];

            if ($error->level == CHL7v2Error::E_ERROR) {
                // ERR-4: Severity (ID)
                // Table - 0516
                // W - Warning - Transaction successful, but there may issues
                // I - Information - Transaction was successful but includes information e.g., inform patient
                // E - Error - Transaction was unsuccessful
                $data[] = "E";
                // ERR-5: Application Error Code (CWE) (optional)
                $data[] = [
                    [
                        "E002",
                        CAppUI::tr("CHL7Event-E002"),
                    ],
                ];
            } else {
                $data[] = "W";
                $data[] = [
                    /*array (
                      "A002",
                      CAppUI::tr("CHL7Event-A002")
                    )*/
                ];
            }

            // ERR-6: Application Error Parameter (ST) (optional repeating)
            $data[] = null;

            // ERR-7: Diagnostic Information (TX) (optional)
            $data[] = null;

            // ERR-8: User Message (TX) (optional)
            $data[] = CAppUI::tr("CHL7v2Exception-$error->code") . ($error->data ? " ($error->data)" : "");

            // ERR-9: Inform Person Indicator (IS) (optional repeating)
            $data[] = null;

            // ERR-10: Override Type (CWE) (optional)
            $data[] = null;

            // ERR-11: Override Reason Code (CWE) (optional repeating)
            $data[] = null;

            // ERR-12: Help Desk Contact Point (XTN) (optional repeating)
            $data[] = null;
        } else {
            // ERR-1: Error Code and Location (ELD) (optional repeating)
            if ($version < "2.5") {
                $comments = $acknowledgment->comments ? " : $acknowledgment->comments" : "";
                $data     = [
                    [
                        null,
                        null,
                        null,
                        // Code Identifying Error
                        [
                            // Identifier
                            $acknowledgment->hl7_error_code,
                            // Text
                            CHL7v2TableEntry::getDescription("357", $acknowledgment->hl7_error_code),
                            // Name of Coding System
                            null,
                            // Alternate Components
                            $acknowledgment->_mb_error_code,
                            // Alternate Text
                            CAppUI::tr(
                                "CHL7Event-$acknowledgment->_mb_error_code"
                            ) . $comments,
                            // Name of Alternate Coding System
                            null,
                        ],
                    ],
                ];

                return $this->fill($data);
            }
            $data[] = null;

            // ERR-2
            $data[] = [
                [
                    0,
                    0,
                ],
            ];

            // ERR-3
            $data[] = [
                [
                    $acknowledgment->hl7_error_code,
                    CHL7v2TableEntry::getDescription("357", $acknowledgment->hl7_error_code),
                ],
            ];
            // ERR-4
            $data[] = $acknowledgment->severity;

            // ERR-5
            // AppFine
            if (CModule::getActive("appFine")) {
                $data[] = CAppFineServer::getErr5($acknowledgment);
            } elseif (CModule::getActive("appFineClient") && CMbArray::get(
                    $sender->_configs,
                    "handle_portail_patient"
                )) {
                $data[] = CAppFineClient::getErr5($acknowledgment);
            } else {
                $data[] = [
                    [
                        $acknowledgment->_mb_error_code,
                        CAppUI::tr("CHL7Event-$acknowledgment->_mb_error_code"),
                    ],
                ];
            }

            // ERR-6
            $data[] = null;
            // ERR-7
            $data[] = null;
            // ERR-8
            $data[] = $acknowledgment->comments;
            // ERR-9
            $data[] = null;
            // ERR-10
            $data[] = null;
            // ERR-11
            $data[] = null;
            // ERR-12
            $data[] = null;
        }

        $this->fill($data);
    }
}
