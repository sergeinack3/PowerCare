<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2SegmentNK1
 * NK1 - Represents an HL7 NK1 message segment (Next of Kin / Associated Parties)
 */
class CHL7v2SegmentNK1 extends CHL7v2Segment
{

    /** @var string */
    public $name = "NK1";

    /** @var null */
    public $set_id;


    /** @var CCorrespondantPatient */
    public $correspondant;

    /**
     * Build NK1 segement
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

        $correspondant = $this->correspondant;
        $message       = $event->message;
        $receiver      = $event->_receiver;

        // NK1-1: Set ID - NK1 (SI)
        $data[] = $this->set_id;

        // NK1-2: NK Name (XPN) (optional repeating)
        $data[] = $this->getXPN($correspondant, $event->_receiver);

        // NK1-3: Relationship (CE) (optional)
        // Table 0063 - Relationship
        $relationships = [
            CHL7v2TableEntry::mapTo("63", $correspondant->parente),
        ];

        if ($correspondant->parente == "autre") {
            $relationships[] = $correspondant->parente_autre;
        }

        $data[] = [
            $relationships,
        ];

        // NK1-4: Address (XAD) (optional repeating)
        $linesAdress = explode("\n", $correspondant->adresse, 2);
        $data[]      = [
            [
                CValue::read($linesAdress, 0),
                str_replace("\n", $message->componentSeparator, CValue::read($linesAdress, 1)),
                ($receiver->_configs["build_fields_format"] == "uppercase") ? CMbString::upper(
                    $correspondant->ville
                ) : $correspondant->ville,
                null,
                $correspondant->cp,
            ],
        ];

        // NK1-5: Phone Number (XTN) (optional repeating)

        // Table - 0201
        // ASN - Answering Service Number
        // BPN - Beeper Number
        // EMR - Emergency Number
        // NET - Network (email) Address
        // ORN - Other Residence Number
        // PRN - Primary Residence Number
        // VHN - Vacation Home Number
        // WPN - Work Number

        // Table - 0202
        // BP       - Beeper
        // CP       - Cellular Phone
        // FX       - Fax
        // Internet - Internet Address: Use Only If Telecommunication Use Code Is NET
        // MD       - Modem
        // PH       - Telephone
        // TDD      - Telecommunications Device for the Deaf
        // TTY      - Teletypewriter

        $phones = [];
        if ($correspondant->tel) {
            $phones[] = [
                null,
                // Table - 0201
                "PRN",
                // Table - 0202
                "PH",
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $correspondant->tel,
            ];
        }

        if ($correspondant->mob) {
            $phones[] = [
                null,
                // Table - 0201
                "PRN",
                // Table - 0202
                "CP",
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $correspondant->mob,
            ];
        }

        if ($correspondant->email) {
            $phones[] = [
                null,
                // Table - 0201
                "NET",
                // Table - 0202
                "Internet",
                $correspondant->email,
            ];
        }

        $data[] = $phones;

        // NK1-6: Business Phone Number (XTN) (optional repeating)
        $data[] = null;

        // NK1-7: Contact Role (CE) (optional)
        // Table - 0131
        $roles = [
            CHL7v2TableEntry::mapTo("131", $correspondant->relation),
        ];
        if ($correspondant->relation == "autre") {
            $roles = array_merge($roles, [$correspondant->relation_autre]);
        }
        $data[] = [
            $roles,
        ];

        // NK1-8: Start Date (DT) (optional)
        $data[] = $correspondant->date_debut;

        // NK1-9: End Date (DT) (optional)
        $data[] = $correspondant->date_fin;

        // NK1-10: Next of Kin / Associated Parties Job Title (ST) (optional)
        $data[] = null;

        // NK1-11: Next of Kin / Associated Parties Job Code/Class (JCC) (optional)
        $data[] = null;

        // NK1-12: Next of Kin / Associated Parties Employee Number (CX) (optional)
        $data[] = null;

        // NK1-13: Organization Name - NK1 (XON) (optional repeating)
        $data[] = null;

        // NK1-14: Marital Status (CE) (optional)
        $data[] = null;

        // NK1-15: Administrative Sex (IS) (optional)
        $data[] = null;

        // NK1-16: Date/Time of Birth (TS) (optional)
        $data[] = null;

        // NK1-17: Living Dependency (IS) (optional repeating)
        $data[] = null;

        // NK1-18: Ambulatory Status (IS) (optional repeating)
        $data[] = null;

        // NK1-19: Citizenship (CE) (optional repeating)
        $data[] = null;

        // NK1-20: Primary Language (CE) (optional)
        $data[] = null;

        // NK1-21: Living Arrangement (IS) (optional)
        $data[] = null;

        // NK1-22: Publicity Code (CE) (optional)
        $data[] = null;

        // NK1-23: Protection Indicator (ID) (optional)
        $data[] = null;

        // NK1-24: Student Indicator (IS) (optional)
        $data[] = null;

        // NK1-25: Religion (CE) (optional)
        // Interdit IHE France
        $data[] = null;

        // NK1-26: Mother's Maiden Name (XPN) (optional repeating)
        $data[] = null;

        // NK1-27: Nationality (CE) (optional)
        $data[] = null;

        // NK1-28: Ethnic Group (CE) (optional repeating)
        // Interdit IHE France
        $data[] = null;

        // NK1-29: Contact Reason (CE) (optional repeating)
        $data[] = null;

        // NK1-30: Contact Person's Name (XPN) (optional repeating)
        $data[] = null;

        // NK1-31: Contact Person's Telephone Number (XTN) (optional repeating)
        $data[] = null;

        // NK1-32: Contact Person's Address (XAD) (optional repeating)
        $data[] = null;

        // NK1-33: Next of Kin/Associated Party's Identifiers (CX) (optional repeating)
        $data[] = [
            [
                $correspondant->_id,
                null,
                null,
                // PID-3-4 Autorité d'affectation
                $this->getAssigningAuthority(),
                "PN",
            ],
        ];

        // NK1-34: Job Status (IS) (optional)
        $data[] = null;

        // NK1-35: Race (CE) (optional repeating)
        // Interdit IHE France
        $data[] = null;

        // NK1-36: Handicap (IS) (optional)
        $data[] = null;

        // NK1-37: Contact Person Social Security Number (ST) (optional)
        $data[] = null;

        // NK1-38: Next of Kin Birth Place (ST) (optional)
        $data[] = null;

        // NK1-39: VIP Indicator (IS) (optional)
        // On utilise le champ pour transmettre l'information de création dans AppFine
        if (CModule::getActive("appFineClient") && $receiver->_configs["send_evenement_to_mbdmp"]) {
            $idex = CIdSante400::getMatchFor($correspondant, CAppFineClient::getObjectTagResponsableAppFine());
            $data[] = $idex->_id ? "Y" : "N";
        } else {
            $data[] = "N";
        }

        $this->fill($data);
    }
}
