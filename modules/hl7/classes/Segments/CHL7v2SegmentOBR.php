<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mpm\CPrisePosologie;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Class CHL7v2SegmentOBR
 * OBR - Represents an HL7 OBR message segment (Observation Request)
 */
class CHL7v2SegmentOBR extends CHL7v2Segment
{
    public $set_id;
    /** @var string */
    public $name = "OBR";

    /** @var CConsultation|CAppFineClientOrderItem|CEvenementMedical|CSejour|CPrescriptionLineElement $object */
    public $object;

    /** @var CPrisePosologie */
    public $prise_poso;

    /** @var CDocumentItem */
    public $docItem;

    /**
     * BuildOBR segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);
        $object  = $this->object;
        $docItem = $this->docItem;

        $prise_poso = $this->prise_poso;

        if (CModule::getActive("appFineClient") && $object instanceof CAppFineClientOrderItem) {
            return $this->fill(CAppFineClient::generateSegmentOBR($object));
        }

        // OBR-1: Set ID - Observation Request (SI) (optional)
        $data[] = $this->set_id;

        // OBR-2: Placer Order Number (EI)
        $data[] = $object->_id;

        // OBR-3: Filler Order Number (EI) (optional)
        $data[] = null;

        // OBR-4: Universal Service ID (CE)
        if ($docItem && $docItem->_id) {
            $obr4 = array(
                array(
                    '',
                    $docItem instanceof CFile ? $docItem->file_name : $docItem->nom,
                )
            );
        } else {
            $obr4 = array(
                array(
                    $object->_id,
                    $object->_class,
                )
            );
        }
        if ($object instanceof CPrescriptionLineElement) {
            $element = $object->loadRefElement();
            $obr4    = array(
                array(
                    $element->libelle_court,
                    $element->libelle,
                )
            );
        } elseif (isset($object->_ref_element_prescription)) {
            $element = $object->_ref_element_prescription;
            $obr4    = array(
                array(
                    $element->_id,
                    $element->libelle,
                )
            );
        }
        $data[] = $obr4;

        // OBR-5: Priority (ID) (optional)
        $data[] = null;

        // OBR-6: Request Date/Time (TS) (optional)
        $data[] = null;

        // OBR-7: Observation Date/Time (TS) (optional)
        if ($docItem && $docItem->_id && $docItem instanceof CCompteRendu) {
            $data[] = $docItem->validation_date;
        } else {
            $data[] = null;
        }

        // OBR-8: Observation End Date/Time (TS) (optional)
        $data[] = null;

        // OBR-9: Collection Volume (CQ) (optional)
        $data[] = null;

        // OBR-10: Collection Identifier (XCN) (optional repeating)
        $data[] = null;

        // OBR-11: Specimen Action Code (ID) (optional table 0065)
        $data[] = null;

        // OBR-12: Danger Code (CE) (optional)
        $data[] = null;

        // OBR-13: Relevant Clinical Info (ST) (optional)
        $data[] = null;

        // OBR-14: Specimen Rcv'd Date/Time (TS) (c)
        $data[] = null;

        // OBR-15: Specimen Source (CM) (optional)
        $data[] = null;

        // OBR-16: Ordering Provider (XCN)
        $data[] = null;

        // OBR-17: Order Callback Phone Number (XTN) (optional repeating)
        $data[] = null;

        // OBR-18: Placer Field 1 (ST) (optional)
        $data[] = $docItem && $docItem->_id ? $docItem->_guid : null;

        // OBR-19: Placer Field 2 (ST) (optional)
        $data[] = null;

        // OBR-20: Filler Field 1 (ST) (optional)
        $data[] = null;

        // OBR-21: Filler Field 2 (ST) (optional)
        $data[] = null;

        // OBR-22: Result Rpt./Status Change (TS) (c)
        $data[] = null;

        // OBR-23: Charge to Pratice (CM) (optional)
        $data[] = null;

        // OBR-24: Diagnostic Service Sect ID (ID) (optional table 0074)
        $data[] = null;

        // OBR-25: Result Status (ID) (c table 0123)
        $data[] = null;

        // OBR-26: Parent Result (CM) (optional)
        $data[] = null;

        // OBR-27: Quantity/Timing (TQ)
        $datetime = null;
        $quantity = "1";
        if ($object instanceof CSejour) {
            $datetime = $object->entree;
        }
        if ($object instanceof CConsultation) {
            $datetime = $object->_datetime;
        }
        if ($object instanceof CPrescriptionLineElement) {
            $datetime = $object->_debut_reel;

            if ($prise_poso) {
                $quantity = $prise_poso->quantite;
                $datetime = $object->debut . " " . $prise_poso->loadRefMoment()->heure;
            }
        }

        $data[] = array(
            array(
                $quantity,
                null,
                null,
                $datetime
            )
        );

        // OBR-28: Result Copies to (CN) (optional)
        $data[] = null;

        // OBR-29: Parent Number (CM) (optional)
        $data[] = null;

        // OBR-30: Transportation Mode (ID) (optional table 0124)
        $data[] = null;

        // OBR-31: reason for Study (CE) (optional)
        $data[] = null;

        // OBR-32: Principal Result Interpreter (CM) (optional)
        $data[] = null;

        // OBR-33: Assitant Result Interpreter (CM) (optional)
        $data[] = null;

        // OBR-34: Technician (CM) (optional)
        $data[] = null;

        // OBR-35: Transcriptionist (CM) (optional)
        $data[] = null;

        // OBR-36: Scheduled Date/time (TS) (optional)
        $data[] = null;

        // OBR-37: Number of Sample Containers (NM) (optional)
        $data[] = null;

        // OBR-38: Transport Logistics of Collected Sample (CE) (optional)
        $data[] = null;

        // OBR-39: Collector's Comment (CE) (optional)
        $data[] = null;

        // OBR-40: Transport Arrangement responsibility (CE) (optional)
        $data[] = null;

        // OBR-41: Transport Arranged (ID) (optional table 0224)
        $data[] = null;

        // OBR-42: Escort Required (ID) (optional table 0225)
        $data[] = null;

        // OBR-43: Planned Patient Transport Comment (CE) (optional)
        $data[] = null;

        $this->fill($data);
    }
}
