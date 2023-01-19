<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Tests\Unit;

use Exception;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\Services\MessagerieLinkPatientService;
use Ox\Mediboard\Messagerie\Tests\Fixtures\MessagerieContextFixtures;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

/**
 * Test class for MessagerieLinkPatientService.
 */
class MessagerieLinkPatientServiceTest extends OxUnitTestCase
{
    /** @var CMediusers $user User. */
    private CMediusers $user;

    /** @var CPatient $patient Patient. */
    private CPatient $patient;

    /**
     * @inheritDoc
     * @throws TestsException
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(
            CMediusers::class,
            MessagerieContextFixtures::MESSAGING_USER_TAG
        );

        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            MessagerieContextFixtures::MESSAGING_PATIENT_TAG
        );

        $this->user    = $user;
        $this->patient = $patient;
    }

    /**
     * Assert get an hospitalization
     *
     * @return void
     * @throws TestsException
     * @throws Exception
     */
    public function testGetHospitalizations(): void
    {
        /** @var CSejour $hospitalization */
        $hospitalization = $this->getObjectFromFixturesReference(
            CSejour::class,
            MessagerieContextFixtures::MESSAGING_HOSPITALIZATION_TAG
        );

        $service = new MessagerieLinkPatientService(
            $this->patient->_id,
            $this->user->loadRefFunction()->group_id,
            '0, 2'
        );

        $hospitalizations = $service->loadPatientHospitalizations();
        $this->assertArrayHasKey($hospitalization->_id, $hospitalizations['result']);
    }

    /**
     * Assert get an operation from an hospitalization
     *
     * @depends testGetHospitalizations
     *
     * @return void
     * @throws TestsException
     * @throws Exception
     */
    public function testGetHospitalizationsOperations(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            MessagerieContextFixtures::MESSAGING_OPERATION_TAG
        );

        $service = new MessagerieLinkPatientService(
            $this->patient->_id,
            $this->user->loadRefFunction()->group_id,
            '0, 2'
        );

        $hospitalizations = $service->loadPatientHospitalizations();

        /** @var CSejour $hospitalization */
        foreach ($hospitalizations['result'] as $hospitalization) {
            if ($hospitalization->_id === $operation->sejour_id) {
                $this->assertArrayHasKey($operation->_id, $hospitalization->_ref_operations);
            }
        }
    }

    /**
     * Assert get an consultation from an hospitalization
     *
     * @depends testGetHospitalizations
     *
     * @return void
     * @throws TestsException
     * @throws Exception
     */
    public function testGetHospitalizationsConsultations(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            MessagerieContextFixtures::MESSAGING_HOSPITALIZATION_CONSULTATION_TAG
        );

        $service = new MessagerieLinkPatientService(
            $this->patient->_id,
            $this->user->loadRefFunction()->group_id,
            '0, 2'
        );

        $hospitalizations = $service->loadPatientHospitalizations();

        /** @var CSejour $hospitalization */
        foreach ($hospitalizations['result'] as $hospitalization) {
            if ($hospitalization->_id === $consultation->sejour_id) {
                $this->assertArrayHasKey($consultation->_id, $hospitalization->_ref_consultations);
            }
        }
    }

    /**
     * Assert get an consultation
     *
     * @return void
     * @throws TestsException
     * @throws Exception
     */
    public function testGetConsultations(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            MessagerieContextFixtures::MESSAGING_CONSULTATION_TAG
        );

        $service = new MessagerieLinkPatientService(
            $this->patient->_id,
            $this->user->loadRefFunction()->group_id,
            '0, 2'
        );

        $consultations = $service->loadPatientConsultations();
        $this->assertArrayHasKey($consultation->_id, $consultations['result']);
    }

    /**
     * Assert get an event
     *
     * @return void
     * @throws TestsException
     * @throws Exception
     */
    public function testGetEvents(): void
    {
        /** @var CEvenementPatient $event */
        $event = $this->getObjectFromFixturesReference(
            CEvenementPatient::class,
            MessagerieContextFixtures::MESSAGING_EVENT_TAG
        );

        $service = new MessagerieLinkPatientService(
            $this->patient->_id,
            $this->user->loadRefFunction()->group_id,
            '0, 2'
        );

        $events = $service->loadPatientEvents();
        $this->assertArrayHasKey($event->_id, $events['result']);
    }
}
