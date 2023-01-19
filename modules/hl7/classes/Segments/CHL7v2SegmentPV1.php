<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\SIHCabinet\CSIHCabinet;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentPV1
 * PV1 - Represents an HL7 PV1 message segment (Patient Visit)
 */
class CHL7v2SegmentPV1 extends CHL7v2Segment
{

    /** @var string */
    public $name = "PV1";

    /** @var null */
    public $set_id;


    /** @var CSejour */
    public $sejour;

    /** @var CAffectation */
    public $curr_affectation;

    /** @var CMbObject */
    public $reference_object;

    /**
     * Build PV1 segement
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

        $receiver = $event->_receiver;
        $group    = $receiver->_ref_group;

        $sejour           = $this->sejour;
        $curr_affectation = $this->curr_affectation;

        $data = [];

        if (CModule::getActive("oxCabinetSIH") && $receiver->_configs["cabinet_sih_id"]) {
            return $this->fill(CCabinetSIH::generateSegmentPV1($sejour, $this, $event->_receiver));
        }

        // PV1-1: Set ID - PV1 (SI) (optional)
        $data[] = $this->set_id;

        // PV1-2: Patient Class (IS)
        // Table - 0004
        // E - Emergency - Passage aux Urgences - Arrivée aux urgences
        // I - Inpatient - Hospitalisation
        // N - Not Applicable - Non applicable -
        // O - Outpatient - Actes et consultation externe
        // R - Recurring patient - Séances
        // Cas de la transaction ITI-30 "Patient Identity Field"
        if (!$sejour || (!$sejour->_id && !$this->reference_object)) {
            $data[] = "N";
            $this->fill($data);

            return null;
        }

        $data[] = $sejour->_hdj_seance ? "R" : CHL7v2TableEntry::mapTo("4", $sejour->type);

        // PV1-3: Assigned Patient Location (PL) (optional)
        $data[] = $this->getPL($receiver, $sejour, $curr_affectation);

        // PV1-4: Admission Type (IS) (optional)
        $data[] = $this->getPV14($receiver, $sejour);

        // PV1-5: Preadmit Number (CX) (optional)
        if ($receiver->_configs["build_PV1_5"] == "none") {
            $data[] = null;
        } elseif ($receiver->_configs["build_PV1_5"] == "sejour_id") {
            if (CHL7v2Message::$build_mode == "simple") {
                $data[] = [
                    $sejour->_id,
                ];
            } else {
                $data[] = [
                    [
                        $sejour->_id,
                        null,
                        null,
                        // PID-3-4 Autorité d'affectation
                        $this->getAssigningAuthority("mediboard", null, null, null, $sejour->group_id),
                        "RI",
                    ],
                ];
            }
        } else {
            if (CHL7v2Message::$build_mode == "simple") {
                $data[] = [
                    $sejour->_id,
                ];
            } else {
                $sejour->loadNPA($group->_id);
                $data[] = $sejour->_NPA ? [
                    [
                        $sejour->_NPA,
                        null,
                        null,
                        // PID-3-4 Autorité d'affectation
                        $this->getAssigningAuthority("FINESS", $group->finess),
                        "RI",
                    ],
                ] : null;
            }
        }


        // PV1-6: Prior Patient Location (PL) (optional)
        $data[] = $this->getPreviousPL($receiver, $sejour);

        // PV1-7: Attending Doctor (XCN) (optional repeating)
        if ($curr_affectation && $curr_affectation->_id) {
            $praticien = $curr_affectation->loadRefPraticien();
        } else {
            $praticien = $sejour->loadRefPraticien();
        }
        $data[] = $this->getXCN($praticien, $receiver);

        // PV1-8: Referring Doctor (XCN) (optional repeating)
        $data[] = $sejour->adresse_par_prat_id ? $this->getXCN($sejour->loadRefAdresseParPraticien(), $receiver) : null;

        // PV1-9: Consulting Doctor (XCN) (optional repeating)
        $data[] = null;

        // PV1-10: Hospital Service (IS) (optional)
        $data[] = $this->getPV110($receiver, $sejour, $curr_affectation);

        // PV1-11: Temporary Location (PL) (optional)
        if ($receiver->_configs["build_PV1_11"] == "uf_medicale") {
            $affectation_id = isset($curr_affectation->_id) ? $curr_affectation->_id : null;

            $ufs         = $sejour->getUFs(null, $affectation_id);
            $uf_medicale = isset($ufs["medicale"]) ? $ufs["medicale"] : null;
            if (isset($uf_medicale->_id)) {
                $data[] = [
                    [
                        $uf_medicale->code,
                    ],
                ];
            } else {
                $data[] = null;
            }
        } else {
            $data[] = null;
        }

        // PV1-12: Preadmit Test Indicator (IS) (optional)
        $data[] = null;

        // PV1-13: Re-admission Indicator (IS) (optional)
        $data[] = null;

        // PV1-14: Admit Source (IS) (optional)
        $data[] = $this->getPV114($receiver, $sejour);

        // PV1-15: Ambulatory Status (IS) (optional repeating)
        $data[] = null;

        // PV1-16: VIP Indicator (IS) (optional)
        // Table - 0099
        // Y - Yes
        // N - No
        $data[] = CHL7v2TableEntry::mapTo("99", $sejour->loadRefPatient()->vip);

        // PV1-17: Admitting Doctor (XCN) (optional repeating)
        if ($receiver->_configs["build_PV1_17"] == "none") {
            $data[] = null;
        } else {
            $data[] = $this->getXCN($sejour->loadRefPraticien(), $receiver);
        }

        // PV1-18: Patient Type (IS) (optional)
        $data[] = null;

        // PV1-19: Visit Number (CX) (optional)
        $data[] = $this->getVisitNumber($sejour, $group, $receiver, $this->reference_object);

        // PV1-20: Financial Class (FC) (optional repeating)
        $data[] = $sejour->loadRefPrestation()->code;

        // PV1-21: Charge Price Indicator (IS) (optional)
        // Table - 0032
        $data[] = $sejour->loadRefChargePriceIndicator()->code;

        // PV1-22: Courtesy Code (IS) (optional)
        // Table - 0045
        // Y - Demande de chambre particulière
        // N - Pas de demande de chambre particulière
        $data[] = $sejour->chambre_seule ? "Y" : "N";

        // PV1-23: Credit Rating (IS) (optional)
        $data[] = null;

        // PV1-24: Contract Code (IS) (optional repeating)
        $data[] = null;

        // PV1-25: Contract Effective Date (DT) (optional repeating)
        $data[] = null;

        // PV1-26: Contract Amount (NM) (optional repeating)
        $data[] = $this->getPV126($receiver, $sejour);

        // PV1-27: Contract Period (NM) (optional repeating)
        $data[] = null;

        // PV1-28: Interest Code (IS) (optional)
        $data[] = null;

        // PV1-29: Transfer to Bad Debt Code (IS) (optional)
        $data[] = null;

        // PV1-30: Transfer to Bad Debt Date (DT) (optional)
        $data[] = null;

        // PV1-31: Bad Debt Agency Code (IS) (optional)
        $data[] = null;

        // PV1-32: Bad Debt Transfer Amount (NM) (optional)
        $data[] = null;

        // PV1-33: Bad Debt Recovery Amount (NM) (optional)
        $data[] = null;

        // PV1-34: Delete Account Indicator (IS) (optional)
        if ($sejour->annule &&
            ((CModule::getActive("appFineClient") && CAppFineClient::loadIdex(
                        $sejour
                    )->_id && $receiver->_configs["send_evenement_to_mbdmp"])
                || (CModule::getActive("oxSIHCabinet") && CSIHCabinet::loadIdex(
                        $sejour
                    ) && $receiver->_configs["sih_cabinet_id"]))
        ) {
            // Table - 0111 - Delete Account Code
            // D - Admit cancel
            $data[] = CHL7v2TableEntry::mapTo("111", "d");
        } else {
            $data[] = null;
        }

        // PV1-35: Delete Account Date (DT) (optional)
        $data[] = null;

        // PV1-36: Discharge Disposition (IS) (optional)
        $sejour->loadRefsAffectations();
        $data[] = $this->getPV136($receiver, $sejour);

        // PV1-37: Discharged to Location (DLD) (optional)
        $data[] = $this->getPV137($receiver, $sejour, $event);

        // PV1-38: Diet Type (CE) (optional)
        if ($sejour->mode_pec_id) {
            $data[] = [
                $sejour->loadRefModePeC()->code,
            ];
        } else {
            $data[] = null;
        }

        // PV1-39: Servicing Facility (IS) (optional)
        $data[] = null;

        // PV1-40: Bed Status (IS) (optional)
        // Interdit par IHE France
        $data[] = null;

        // PV1-41: Account Status (IS) (optional)
        // Utilisation que pour les événements A03 et Z99
        // Table - 0117
        // D - C'était la dernière venue pour ce dossier administratif
        // N - Ce n'était pas la dernière venue pour ce dossier administratif
        if ($sejour->_hdj_seance && ($event->code == "A03" || $event->code == "Z99")) {
            $data[] = $sejour->last_seance ? "D" : "N";
        } else {
            $data[] = "";
        }

        // PV1-42: Pending Location (PL) (optional)
        $data[] = null;

        // PV1-43: Prior Temporary Location (PL) (optional)
        $data[] = null;

        // PV1-44: Admit Date/Time (TS) (optional)
        $data[] = $sejour->entree_reelle;

        // PV1-45: Discharge Date/Time (TS) (optional repeating)
        $data[] = $sejour->sortie_reelle;

        // PV1-46: Current Patient Balance (NM) (optional)
        $data[] = null;

        // PV1-47: Total Charges (NM) (optional)
        $data[] = null;

        // PV1-48: Total Adjustments (NM) (optional)
        $data[] = null;

        // PV1-49: Total Payments (NM) (optional)
        $data[] = null;

        // PV1-50: Alternate Visit ID (CX) (optional)
        if (
            CModule::getActive('oxSIHCabinet') && CMbArray::get($receiver->_configs, 'sih_cabinet_id')
            && $this->reference_object && $this->reference_object instanceof COperation
        ) {
            // On met operation_id dans PV1.50 pour que TAMM le récupère
            $data[] = [$this->reference_object->_id];
        } elseif (CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
            // Cas de l'utilisation du rang

            $sejour->loadNRA($group->_id);
            if (CHL7v2Message::$build_mode == "simple") {
                $data[] = [
                    $sejour->_ref_NRA->id400,
                ];
            }
        } else {
            $data[] = null;
        }

        // PV1-51: Visit Indicator (IS) (optional)
        $data[] = null;

        // PV1-52: Other Healthcare Provider (XCN) (optional repeating)
        $data[] = null;

        $this->fill($data);
    }
}
