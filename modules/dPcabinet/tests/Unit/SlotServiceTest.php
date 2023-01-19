<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CSlot;
use Ox\Mediboard\Cabinet\Tests\Fixtures\SlotServiceFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class SlotServiceTest extends OxUnitTestCase
{
    public function testVerifySlotWithNewPlageConsultWillCreateSlot(): void
    {
        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, SlotServiceFixtures::SLOT_USER);

        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $user->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:30:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "12:00:00";
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(6, count($slots));
        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 09:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 09:30:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 10:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 10:30:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 11:30:00", $heure_start));


        $this->assertTrue(in_array(CMbDT::date() . " 09:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 10:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 10:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 11:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_end));

        $this->deleteOrFailed($plage_consult);
        foreach ($slots as $_slot) {
            $this->deleteOrFailed($_slot);
        }
    }

    public function testVerifySlotWithNewFreqWillCreateSlotWithNewFreq(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_PLAGE_CONSULT_FREQ
        );

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(3, count($slots));

        $plage_consult->_freq = 15;
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(12, count($slots));

        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 14:15:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 14:30:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 14:45:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:15:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:30:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:45:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:15:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:30:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:45:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 14:15:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 14:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 14:45:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:15:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:45:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:15:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:30:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:45:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_end));
    }

    public function testVerifySlotWithNewStartTimeBeforeStartTimeWillCreateSlotAtTheNewStartTime(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_PLAGE_CONSULT_NEW_START_TIME_BEFORE_START_TIME
        );

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(3, count($slots));

        $plage_consult->debut = "12:00:00";
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(5, count($slots));

        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 17:00:00", $heure_end));
    }

    public function testVerifySlotWithNewStartTimeAfterStartTimeWillDeleteSlotToTheNewStartTime(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_PLAGE_CONSULT_NEW_START_TIME_AFTER_START_TIME
        );

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(6, count($slots));

        $plage_consult->debut = "14:00:00";
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(3, count($slots));

        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 17:00:00", $heure_end));
    }

    public function testVerifySlotWithNewEndTimeAfterEndTimeWillCreateSlotAtTheNewEndTime(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_PLAGE_CONSULT_NEW_END_TIME_AFTER_END_TIME
        );

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(4, count($slots));

        $plage_consult->fin = "18:00:00";
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(7, count($slots));

        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 17:00:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 15:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 16:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 17:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 18:00:00", $heure_end));
    }

    public function testVerifySlotWithNewEndTimeBeforeEndTimeWillDeleteSlotToTheNewEndTime(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_PLAGE_CONSULT_NEW_END_TIME_BEFORE_END_TIME
        );

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(6, count($slots));

        $plage_consult->fin = "13:00:00";
        $this->storeOrFailed($plage_consult);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(2, count($slots));

        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_end));
    }

    public function testAddConsultToSlotChangePlageConsultWillChangeTheConsultSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_CHANGE_PLAGE_CONSULT
        );

        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_CONSULT_CHANGE_PLAGE_CONSULT_REPLACE
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);

        $consultation->plageconsult_id = $plage_consult->_id;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals($plage_consult->_id, reset($slots)->plageconsult_id);
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
    }

    public function testAddConsultToSlotChangeDureeWillChangeTheConsultSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_CHANGE_DUREE
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);

        $consultation->duree = 4;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(4, count($slots));
        $heure_start = [];
        $heure_end   = [];
        foreach ($slots as $_slot) {
            $this->assertEquals("busy", $_slot->status);
            $this->assertEquals(0, $_slot->overbooked);
            $heure_start[] = $_slot->start;
            $heure_end[]   = $_slot->end;
        }

        $this->assertTrue(in_array(CMbDT::date() . " 10:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_start));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_start));

        $this->assertTrue(in_array(CMbDT::date() . " 11:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 12:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 13:00:00", $heure_end));
        $this->assertTrue(in_array(CMbDT::date() . " 14:00:00", $heure_end));
    }

    public function testAddConsultToSlotAnnuleAt1COnsultWillDeleteTheConsultSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_ANNULE
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);

        $consultation->annule = 1;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(0, count($slots));
    }

    public function testAddConsultToSlotAnnuleAt0COnsultWillCreateTheConsultSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_ANNULE
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(0, count($slots));

        $consultation->annule = 0;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $this->assertEquals(CMbDT::date() . " 10:00:00", reset($slots)->start);
        $this->assertEquals(CMbDT::date() . " 11:00:00", reset($slots)->end);
    }

    public function testCreateConsultationOverbookedWillCreateSlotOverbooked(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_CREATE_CONSULT_OVERBOOKED
        );

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:00:00";
        $consultation->chrono          = 8;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(1, reset($slots)->overbooked);
        $this->assertEquals(CMbDT::date() . " 10:00:00", reset($slots)->start);
        $this->assertEquals(CMbDT::date() . " 10:45:00", reset($slots)->end);
    }

    public function testDeleteConsultationOverbookedWillDeleteSlotOverbooked(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_DELETE_CONSULT_OVERBOOKED_TO_DELETE
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(1, reset($slots)->overbooked);

        $this->deleteOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(0, count($slots));
    }

    public function testDeleteConsultationNotOverbookedWillPutConsultationIdOfSlotOverbooked(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_DELETE_CONSULT_NOT_OVERBOOKED_TO_DELETE
        );
        /** @var CConsultation $consultation_overbooked */
        $consultation_overbooked = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_DELETE_CONSULT_NOT_OVERBOOKED
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $id_principal_slot = reset($slots)->_id;

        $this->deleteOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["slot_id" => "= '$id_principal_slot'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $this->assertEquals($consultation_overbooked->_id, reset($slots)->consultation_id);
    }

    public function testDeleteConsultationNotOverbookedWillSetFreeSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_DELETE_CONSULT
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $id_principal_slot = reset($slots)->_id;

        $this->deleteOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["slot_id" => "= '$id_principal_slot'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("free", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $this->assertNull(reset($slots)->consultation_id);
    }

    public function testChangeDureeConsultationOverbookedWillSetConsultationIDToSlot(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING_TO_CHANGE
        );

        /** @var CConsultation $consultation */
        $consultation_overbooking = $this->getObjectFromFixturesReference(
            CConsultation::class,
            SlotServiceFixtures::SLOT_CONSULT_CHANGE_DUREE_OVERBOOKING
        );

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(4, count($slots));
        $id_principal_slot = null;
        foreach ($slots as $_slot) {
            if ($_slot->start == CMbDT::date() . " 13:00:00") {
                $id_principal_slot = $_slot->_id;
            }
        }

        $consultation->duree = 3;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["slot_id" => "= '$id_principal_slot'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(0, reset($slots)->overbooked);
        $this->assertEquals($consultation_overbooking->_id, reset($slots)->consultation_id);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation_overbooking->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(5, count($slots));

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(3, count($slots));
    }

    public function testCreateImmediateConsultWillCreateNewSlot(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_IMMEDIATE_CONSULT
        );

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "10:23:00";
        $consultation->chrono          = 8;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["consultation_id" => "= '$consultation->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(1, count($slots));
        $this->assertEquals("busy", reset($slots)->status);
        $this->assertEquals(1, reset($slots)->overbooked);
        $this->assertEquals(CMbDT::date() . " 10:23:00", reset($slots)->start);
        $this->assertEquals(CMbDT::date() . " 10:38:00", reset($slots)->end);
    }

    public function testCreateConsultOutOfBoundsThenDeletedWIllDeleteSlotOutOfBounds(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            SlotServiceFixtures::SLOT_OUT_OF_BOUNDS
        );

        $consultation                  = new CConsultation();
        $consultation->plageconsult_id = $plage_consult->_id;
        $consultation->heure           = "16:00:00";
        $consultation->duree           = 3;
        $consultation->chrono          = 8;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(5, count($slots));

        $consultation->annule = 1;
        $this->storeOrFailed($consultation);

        $slot  = new CSlot();
        $where = ["plageconsult_id" => "= '$plage_consult->_id'"];
        $slots = $slot->loadList($where);

        $this->assertEquals(3, count($slots));
    }
}
