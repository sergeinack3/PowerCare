<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbDT;

/**
 * Gestion des slots
 */
class SlotService
{
    /**
     * V�rifie lors de la modification d'une plage de consultation si les slots doivent �tre modifi�s
     *
     * @return string|void|null
     * @throws Exception
     */
    public function verifySlot(CPlageconsult $plage_consult)
    {
        //Cr�ation de la plage de consult
        if (!count($plage_consult->loadRefsSlots("start ASC"))) {
            $this->createAllSlot($plage_consult);

            return;
        }

        $slots      = $plage_consult->loadRefsSlots();
        $first_slot = reset($slots);
        $frq_slot   = CMbDT::format(CMbDT::durationTime($first_slot->start, $first_slot->end), "%H:%M:00");
        $date       = CMbDT::format($first_slot->start, "%Y-%m-%d");
        foreach ($slots as $_slot) {
            if (!$_slot->consultation_id) {
                if ($msg = $_slot->delete()) {
                    return $msg;
                }
            } else {
                $_slot->overbooked = true;
                $_slot->store();
            }
        }
        $this->createAllSlot($plage_consult);
    }

    /**
     * Cr�e les slots de 0
     *
     * @return string|void|null
     * @throws Exception
     */
    private function createAllSlot(CPlageconsult $plage_consult)
    {
        $date       = CMbDT::date($plage_consult->date);
        $time_start = CMbDT::time($plage_consult->debut);
        $time_end   = CMbDT::time($plage_consult->fin);
        while ($time_start < $time_end) {
            $time_end_slot         = CMbDT::addTime($plage_consult->freq, $time_start);
            $datetime_start        = $date . " " . $time_start;
            $datetime_end          = $date . " " . $time_end_slot;
            $slot                  = new CSlot();
            $slot->plageconsult_id = $plage_consult->_id;
            $slot->start           = $datetime_start;
            $slot->end             = $datetime_end;

            $slot->loadMatchingObject();
            $slot->overbooked = "";

            if ($msg = $slot->store()) {
                return $msg;
            }

            $time_start = $time_end_slot;
        }
    }

    /**
     * Ajoute une consultation � un slot
     *
     * @return string|void|null
     * @throws Exception
     */
    public function addConsultToSlot(CConsultation $consultation)
    {
        //R�cup�ration des cr�neaux actuel de la consultation
        $old_slot  = new CSlot();
        $old_slots = $old_slot->loadList(["consultation_id" => "= '$consultation->_id'"]);

        $consultation->loadRefPlageConsult();
        $new_slots = [];

        if ($consultation->_ref_plageconsult && $consultation->_ref_plageconsult->_id) {
            if (!count($consultation->_ref_plageconsult->loadRefsSlots())) {
                $this->createAllSlot($consultation->_ref_plageconsult);
            }
            $heure_debut = $consultation->heure;
            $heure_fin   = CMbDT::addTime($consultation->heure, $consultation->_ref_plageconsult->freq);
            for ($i = 1; $i <= $consultation->duree; $i++) {
                //Chargement des nouveaux slots
                $new_slot                  = new CSlot();
                $new_slot->plageconsult_id = $consultation->_ref_plageconsult->_id;
                $new_slot->start           = $consultation->_ref_plageconsult->date . " " . $heure_debut;
                $new_slot->end             = $consultation->_ref_plageconsult->date . " " . $heure_fin;
                $new_slot->loadMatchingObject();
                if ($new_slot->_id) {
                    //Le cr�neau est libre
                    if (!$new_slot->consultation_id) {
                        $new_slot->consultation_id = $consultation->_id;
                        $new_slot->status          = "busy";
                        $new_slots[]               = $new_slot->_id;
                        if ($msg = $new_slot->store()) {
                            return $msg;
                        }
                    } elseif ($new_slot->consultation_id && $new_slot->consultation_id == $consultation->_id) {
                        //Le cr�neau n'est pas libre mais contient d�j� la consultation
                        $new_slots[] = $new_slot->_id;
                    } elseif ($new_slot->consultation_id && $new_slot->consultation_id != $consultation->_id) {
                        //Le cr�neau n'est pas libre, on cr�e un cr�neau overbooked
                        $new_slots[] = $this->createSlotOverbooked($consultation, $heure_debut, $heure_fin);
                    }
                } else {
                    //Consultation imm�diate ne rentrant pas dans un cr�neau
                    $new_slots[] = $this->createSlotOverbooked($consultation, $heure_debut, $heure_fin);
                }
                $heure_debut = $heure_fin;
                $heure_fin   = CMbDT::addTime($heure_debut, $consultation->_ref_plageconsult->freq);
            }
        }
        //V�rification des anciens cr�neaux pour surppimer la lien avec la consultation
        foreach ($old_slots as $_old_slot) {
            //Le cr�neau est toujours pris par la consultation
            if (in_array($_old_slot->_id, $new_slots)) {
                continue;
            }
            //Consultation non overbooked
            if (!$_old_slot->overbooked) {
                $this->verifyOverbooked($_old_slot);
            } else {
                //Consultation overbooked donc on la supprime
                if ($msg = $_old_slot->delete()) {
                    return $msg;
                }
            }
        }
    }

    /**
     * Cr�ation d'un slot overbooked
     *
     * @return int|string|null
     * @throws Exception
     */
    private function createSlotOverbooked(CConsultation $consultation, string $heure_debut, string $heure_fin)
    {
        $slot                  = new CSlot();
        $slot->plageconsult_id = $consultation->_ref_plageconsult->_id;
        $slot->start           = $consultation->_ref_plageconsult->date . " " . $heure_debut;
        $slot->end             = $consultation->_ref_plageconsult->date . " " . $heure_fin;
        $slot->status          = "busy";
        $slot->overbooked      = 1;
        $slot->consultation_id = $consultation->_id;
        if ($msg = $slot->store()) {
            return $msg;
        }

        return $slot->_id;
    }

    /**
     * V�rifie si un slot est overbooked pour une m�me heure et une m�me plage de consult
     *
     * @return string|void|null
     * @throws Exception
     */
    public function verifyOverbooked(CSlot $slot)
    {
        //R�ucp�ration des cr�neaux overbooked
        $where = [
            "plageconsult_id" => " = '$slot->plageconsult_id'",
            "start"           => "= '$slot->start'",
            "end"             => "= '$slot->end'",
            "overbooked"      => "= '1'",
        ];

        $search_slot = new CSlot();
        $slots       = $search_slot->loadList($where);
        //S'il existe un cr�neau overbooked on place la consultation dans le cr�neau de base
        //et on supprime le cr�neau overbooked
        if (count($slots)) {
            $first_slot_overbooker = reset($slots);
            $slot->consultation_id = $first_slot_overbooker->consultation_id;
            $first_slot_overbooker->delete();
        } else {
            //On v�rifie que le cr�neau et bien compris dans la plage sinon on le supprime
            $heure_start_slot = CMbDT::format($slot->start, "%H:%M:%S");
            $heure_end_slot   = CMbDT::format($slot->end, "%H:%M:%S");
            $slot->loadRefPlageconsult();
            if (
                $slot->_ref_plageconsult->fin < $heure_start_slot
                || $slot->_ref_plageconsult->debut > $heure_end_slot
            ) {
                if ($msg = $slot->delete()) {
                    return $msg;
                }

                return;
            }
            //Si pas de cr�neau overbooked, le cr�neau devient libre
            $slot->consultation_id = "";
            $slot->status          = "free";
        }
        if ($msg = $slot->store()) {
            return $msg;
        }
    }

    /**
     * Supprime une consultation d'un slot
     *
     * @return string|void|null
     * @throws Exception
     */
    public function deleteConsultationOfASlot(CConsultation $consultation)
    {
        $slots = $consultation->loadRefSlots();
        foreach ($slots as $_slot) {
            //Si non overbooked on v�rifie si il n'y a pas de cr�neau overbooked
            if (!$_slot->overbooked) {
                $this->verifyOverbooked($_slot);
            } else {
                if ($msg = $_slot->delete()) {
                    return $msg;
                }
            }
        }
    }
}
