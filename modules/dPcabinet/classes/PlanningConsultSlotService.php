<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbDT;

abstract class PlanningConsultSlotService
{
    /** @var bool */
    public $consult_cancelled = false;
    /** @var string */
    public $chrono = null;
    /** @var bool */
    public $facture = null;

    /**
     * Récupération des slots d'une plage de consultation
     *
     * @throws Exception
     */
    protected function slotContents(CPlageconsult $plage, string $date = null): void
    {
        if (!$plage->countBackRefs("slots")) {
            //"Création" des slots
            $heures_debut  = [];
            $consultations = $plage->loadRefsConsultations($this->consult_cancelled);
            foreach ($consultations as $_consultation) {
                $heure_debut = $_consultation->heure;
                $heure_fin   = CMbDT::addTime($_consultation->heure, $plage->freq);
                for ($i = 1; $i <= $_consultation->duree; $i++) {
                    $heures_debut[] = $heure_debut;
                    $heure_debut    = $heure_fin;
                    $heure_fin      = CMbDT::addTime($heure_debut, $plage->freq);
                }
                if ($date) {
                    $this->consultContentsByDate($date, $_consultation);
                } else {
                    $this->consultContents($_consultation, $plage->_id);
                }
            }
            $date       = CMbDT::date($plage->date);
            $time_start = CMbDT::time($plage->debut);
            $time_end   = CMbDT::time($plage->fin);
            while ($time_start < $time_end) {
                $time_end_slot         = CMbDT::addTime($plage->freq, $time_start);
                $datetime_start        = $date . " " . $time_start;
                $datetime_end          = $date . " " . $time_end_slot;
                $slot                  = new CSlot();
                $slot->plageconsult_id = $plage->_id;
                $slot->start           = $datetime_start;
                $slot->end             = $datetime_end;
                if (in_array($time_start, $heures_debut)) {
                    $slot->status = "busy";
                } else {
                    $slot->status = "free";
                }
                $plage->_ref_slots[] = $slot;

                $time_start = $time_end_slot;
            }
        } else {
            //Récupération des slots
            $consult_ids = [];
            $slots       = $plage->loadRefsSlots("start ASC");

            if ($this->consult_cancelled) {
                $where                  = ["plageconsult_id" => "= '$plage->_id'", "annule" => "= '1'"];
                $consultation_annulee   = new CConsultation();
                $consultations_annulees = $consultation_annulee->loadList($where);
                foreach ($consultations_annulees as $_consultation_annulee) {
                    $this->consultContentsByDate($date, $_consultation_annulee);
                }
            }

            foreach ($slots as $_slot) {
                if ($_slot->consultation_id && !in_array($_slot->consultation_id, $consult_ids)) {
                    $consult_ids[] = $_slot->consultation_id;
                    $_slot->loadRefConsultation();
                    if (
                        ($this->chrono === null || $_slot->_ref_consultation->chrono == $this->chrono)
                        && ($this->facture === null || $_slot->_ref_consultation->facture == $this->facture)
                    ) {
                        if ($_slot->_ref_consultation->type_consultation == 'consultation') {
                            if ($date) {
                                $this->consultContentsByDate($date, $_slot->_ref_consultation);
                            } else {
                                $this->consultContents($_slot->_ref_consultation, $plage->_id);
                            }
                        }
                    }
                }
            }
        }
    }

    abstract protected function consultContentsByDate(string $date, CConsultation $consult): void;

    abstract protected function consultContents(CConsultation $consult, int $plage_id): void;
}
