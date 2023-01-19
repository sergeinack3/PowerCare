<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Tests\Fixtures\Fixtures;

class SlotServiceFixtures extends Fixtures
{
    public const SLOT_USER                                           = "slot_user";
    public const SLOT_PLAGE_CONSULT_FREQ                             = "slot_plage_consult_freq";
    public const SLOT_PLAGE_CONSULT_NEW_START_TIME_BEFORE_START_TIME = "slot_plage_consult_new_start_time_before_start_time";
    public const SLOT_PLAGE_CONSULT_NEW_START_TIME_AFTER_START_TIME  = "slot_plage_consult_new_start_time_after_start_time";
    public const SLOT_PLAGE_CONSULT_NEW_END_TIME_BEFORE_END_TIME     = "slot_plage_consult_new_end_time_before_end_time";
    public const SLOT_PLAGE_CONSULT_NEW_END_TIME_AFTER_END_TIME      = "slot_plage_consult_new_end_time_after_end_time";
    public const SLOT_CONSULT_CHANGE_PLAGE_CONSULT                   = "slot_consult_change_plage_consult";
    public const SLOT_CONSULT_CHANGE_PLAGE_CONSULT_REPLACE           = "slot_consult_change_plage_consult_replace";
    public const SLOT_CONSULT_CHANGE_DUREE                           = "slot_consult_duree";
    public const SLOT_CONSULT_ANNULE                                 = "slot_consult_annule";
    public const SLOT_CREATE_CONSULT_OVERBOOKED                      = "slot_create_consult_overbooked";
    public const SLOT_DELETE_CONSULT_OVERBOOKED                      = "slot_delete_consult_overbooked";
    public const SLOT_DELETE_CONSULT_OVERBOOKED_TO_DELETE            = "slot_delete_consult_overbooked_to_delete";
    public const SLOT_DELETE_CONSULT_NOT_OVERBOOKED                  = "slot_delete_consult_not_overbooked";
    public const SLOT_DELETE_CONSULT_NOT_OVERBOOKED_TO_DELETE        = "slot_delete_consult_not_overbooked_to_delete";
    public const SLOT_DELETE_CONSULT                                 = "slot_delete_consult";
    public const SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING               = "slot_consult_change_duree_overbooking";
    public const SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING_TO_CHANGE     = "slot_consult_change_duree_overbooking_to_change";
    public const SLOT_IMMEDIATE_CONSULT                              = "slot_immediate_consult";
    public const SLOT_OUT_OF_BOUNDS                                  = "slot_out_of_bounds";

    /**
     * @inheritDoc
     */
    public function load()
    {
        $user = $this->getUser(false);
        $this->store($user, self::SLOT_USER);

        $this->createPlageConsultFreq();
        $this->createPlageConsultNewStartTimeBeforeStartTime();
        $this->createPlageConsulthNewStartTimeAfterStartTime();
        $this->createPlageConsulthNewEndTimeAfterEndTime();
        $this->createPlageConsulthNewEndTimeBeforeEndTime();
        $this->createConsultForChangePlageConsult();
        $this->createConsultForChangeDuree();
        $this->createConsultForChangeAnnule();
        $this->createConsultForConsultOverbooked();
        $this->deleteConsultOverbooked();
        $this->deleteConsultNotOverbooked();
        $this->deleteConsult();
        $this->consultChangeDureeOverbooking();
        $this->createImmediateConsult();
        $this->createPlageconsultOutOfBounds();
    }

    private function createPlageConsultFreq(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "14:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_PLAGE_CONSULT_FREQ);
    }

    private function createPlageConsultNewStartTimeBeforeStartTime(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "14:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_PLAGE_CONSULT_NEW_START_TIME_BEFORE_START_TIME);
    }

    private function createPlageConsulthNewStartTimeAfterStartTime(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "11:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_PLAGE_CONSULT_NEW_START_TIME_AFTER_START_TIME);
    }

    private function createPlageConsulthNewEndTimeAfterEndTime(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "11:00:00";
        $plage_consult->fin     = "15:00:00";
        $this->store($plage_consult, self::SLOT_PLAGE_CONSULT_NEW_END_TIME_AFTER_END_TIME);
    }

    private function createPlageConsulthNewEndTimeBeforeEndTime(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "11:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_PLAGE_CONSULT_NEW_END_TIME_BEFORE_END_TIME);
    }

    private function createConsultForChangePlageConsult(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CONSULT_CHANGE_PLAGE_CONSULT);

        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CONSULT_CHANGE_PLAGE_CONSULT_REPLACE);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CONSULT_CHANGE_PLAGE_CONSULT);
    }

    private function createConsultForChangeDuree(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CONSULT_CHANGE_DUREE);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CONSULT_CHANGE_DUREE);
    }

    private function createConsultForChangeAnnule(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CONSULT_ANNULE);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CONSULT_ANNULE);
    }

    private function createConsultForConsultOverbooked(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:45:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CREATE_CONSULT_OVERBOOKED);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CREATE_CONSULT_OVERBOOKED);
    }

    private function deleteConsultOverbooked(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:45:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_DELETE_CONSULT_OVERBOOKED);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_DELETE_CONSULT_OVERBOOKED);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_DELETE_CONSULT_OVERBOOKED_TO_DELETE);
    }

    private function deleteConsultNotOverbooked(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:45:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_DELETE_CONSULT_NOT_OVERBOOKED);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_DELETE_CONSULT_NOT_OVERBOOKED_TO_DELETE);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_DELETE_CONSULT_NOT_OVERBOOKED);
    }

    private function deleteConsult(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:45:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_DELETE_CONSULT);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_DELETE_CONSULT);
    }

    private function consultChangeDureeOverbooking(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->duree           = 4;
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING_TO_CHANGE);

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->duree           = 5;
        $consultation->chrono          = 8;
        $this->store($consultation, self::SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING);
    }

    private function createImmediateConsult(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:15:00";
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_IMMEDIATE_CONSULT);
    }

    private function createPlageconsultOutOfBounds(): void
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $this->getUser(false)->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "01:00:00";
        $plage_consult->debut   = "14:00:00";
        $plage_consult->fin     = "17:00:00";
        $this->store($plage_consult, self::SLOT_OUT_OF_BOUNDS);
    }
}
