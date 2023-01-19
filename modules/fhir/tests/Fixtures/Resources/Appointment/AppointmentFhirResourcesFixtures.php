<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Appointment;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Appointment\CFHIRResourceAppointment;
use Ox\Interop\Fhir\Tests\Fixtures\FhirApiFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures
 */
class AppointmentFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_CANCELED_APPOINTMENT_FHIR_RESOURCE = 'fhir_canceled_appointment_resource';
    public const  REF_APPOINTMENT_FHIR_RESOURCE          = 'fhir_appointment_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourceAppointment::class,
            'object_class'  => 'CConsultation',
            'fixture_ref'   => self::REF_APPOINTMENT_FHIR_RESOURCE,
        ],
        [
            'fhir_resource' => CFHIRResourceAppointment::class,
            'object_class'  => 'CConsultation',
            'fixture_ref'   => self::REF_CANCELED_APPOINTMENT_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $this->generateAppointment();
        $this->generateCanceledAppointment();
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['fhir_resources'];
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateAppointment(): CConsultation
    {
        $fhir_api_fixtures = new FhirApiFixtures();
        $sejour            = $fhir_api_fixtures->generateSejour();

        $user_id = $this->getUser()->_id;

        $consult_category               = FhirResourcesHelper::getSampleFhirConsultationCategorie();
        $consult_category->praticien_id = $user_id;
        $this->store($consult_category);

        $schedule          = FhirResourcesHelper::getSampleFhirPlageconsult();
        $schedule->chir_id = $this->getUser()->_id;
        $this->store($schedule);

        $appointment                  = FhirResourcesHelper::getSampleFhirConsultation();
        $appointment->plageconsult_id = $schedule->_id;
        $appointment->_praticien_id   = $user_id;
        $appointment->categorie_id    = $consult_category->_id;
        $appointment->sejour_id       = $sejour->_id;
        $this->store($appointment, self::REF_APPOINTMENT_FHIR_RESOURCE);

        return $appointment;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateCanceledAppointment(): void
    {
        $fhir_api_fixtures = new FhirApiFixtures();
        $sejour            = $fhir_api_fixtures->generateSejour();

        $user_id = $this->getUser()->_id;

        $consult_category               = FhirResourcesHelper::getSampleFhirConsultationCategorie();
        $consult_category->praticien_id = $user_id;
        $this->store($consult_category);

        $schedule          = FhirResourcesHelper::getSampleFhirPlageconsult();
        $schedule->chir_id = $user_id;
        $this->store($schedule);

        $canceled_appointment                   = FhirResourcesHelper::getSampleFhirConsultation();
        $canceled_appointment->plageconsult_id  = $schedule->_id;
        $canceled_appointment->_praticien_id    = $user_id;
        $canceled_appointment->sejour_id        = $sejour->_id;
        $canceled_appointment->annule           = 1;
        $canceled_appointment->motif_annulation = 'by_patient';
        $this->store($canceled_appointment, self::REF_CANCELED_APPOINTMENT_FHIR_RESOURCE);
    }
}
