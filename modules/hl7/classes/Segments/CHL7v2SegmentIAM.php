<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2SegmentIAM
 * IMA - Represents an HL7 IAM message segment (Patient Adverse Reaction Information)
 */
class CHL7v2SegmentIAM extends CHL7v2Segment
{
    /** @var string */
    public $name = "IAM";

    /** @var null */
    public $set_id;

    /** @var CPatient */
    public $patient;

    /** @var CAntecedent */
    public $antecedent;

    /** @var CAntecedent */
    public $antecedent_handled;

    /**
     * Build IAM segment
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        /** @var CPatient $patient */
        $patient = $this->patient;

        /** @var CAntecedent $antecedent */
        $antecedent = $this->antecedent;

        /** @var CAntecedent $antecedent_handled */
        $antecedent_handled = $this->antecedent_handled;

        $data = [];

        // IAM-1: Set ID
        $data[] = $this->set_id;

        // IAM-2: Allergen Type Code (CE) optional
        $data[] = null;

        // IAM-3: Allergen Code/Mnemonic/Description (CE)
        $data[] = [
            [
                $antecedent->_id,
                $antecedent->_view,
            ],
        ];

        // IAM-4: Allergy Severity Code (CE) optional
        // Table 0128
        $data[] = null;

        // IAM-5: Allergy Reaction Code (ST) optional repeating
        $data[] = null;

        // IAM-6: Allergy Action Code (CNE)
        // Table 0323
        // A - Add/Insert
        // D - Delete
        // U - Update
        // X - No change
        if ($antecedent_handled && ($antecedent_handled->_id == $antecedent->_id)) {
            $current_log = $antecedent_handled->loadLastLog();
            $antecedent->annule || isset($antecedent_handled->_delete) ? $current_log->type = "delete" : null;
            $data[] = CHL7v2TableEntry::mapTo("323", $current_log->type);
        } else {
            $data[] = 'X';
        }

        // IAM-7: Allergy Unique Identifier (EI) optional
        $data[] = $antecedent->_id;

        // IAM-8: Action Reason (ST) optional
        $data[] = null;

        // IAM-9: Sensitivity to Causative Agent Code (CE) optional
        $data[] = null;

        // IAM-10: Allergen Group Code/Mnemonic/Description (CE) optional
        $data[] = null;

        // IAM-11: Onset Date (DT) optional
        $data[] = null;

        // IAM-12: Onset Date Text (ST) optional
        $data[] = null;

        // IAM-13: Reported Date/Time (TS) optional
        $data[] = null;

        // IAM-14: Reported By (XPN) optional
        $data[] = null;

        // IAM-15: Relationship to Patient Code (CE) optional
        $data[] = null;

        // IAM-16: Alert Device Code (CE) optional
        $data[] = null;

        // IAM-17: Allergy Clinical Status Code (CE) optional
        $data[] = null;

        // IAM-18: Statused by Person (XCN) optional
        $data[] = null;

        // IAM-19: Statused by Organization (XON) optional
        $data[] = null;

        // IAM-20: Statused at Date/Time (TS) optional
        $data[] = null;

        // IAM-21: Inactivated by Person (XCN) optional
        $data[] = null;

        // IAM-22: Inactivated Date/Time (DTM) optional
        $data[] = null;

        // IAM-23: Initially Recorded by Person (XCN) optional
        $data[] = null;

        // IAM-24: Initially Recorded Date/Time (DTM) optional
        $data[] = null;

        // IAM-25: Modified by Person (XCN) optional
        $data[] = null;

        // IAM-26: Modified Date/Time (DTM) optional
        $data[] = null;

        // IAM-27: Clinician Identified Code (CWE) optional
        $data[] = null;

        // IAM-28: Initially Recorded by Organization (XON) optional
        $data[] = null;

        // IAM-29: Modified by Organization (XON) optional
        $data[] = null;

        // IAM-30: Inactivated by Organization (XON) optional
        $data[] = null;

        $this->fill($data);
    }
}
