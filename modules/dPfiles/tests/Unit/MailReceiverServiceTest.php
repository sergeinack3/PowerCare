<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CDestinataire;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\MailReceiverService;
use Ox\Mediboard\Files\Tests\Fixtures\MailReceiverFixtures;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class MailReceiverServiceTest extends OxUnitTestCase
{
    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorUnhandledClass(): void
    {
        $this->expectExceptionMessage('MailReceiverService-error-class_not_handled');

        new MailReceiverService(new CFile());
    }

    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorWithPrescription(): void
    {
        $prescription = $this->getObjectFromFixturesReference(
            CPrescription::class,
            MailReceiverFixtures::PRESCRIPTION_TAG
        );

        $service = new MailReceiverService($prescription);
        $this->assertInstanceOf(CSejour::class, $service->getObject());
    }

    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorWithEvenementPatient(): void
    {
        $event = $this->getObjectFromFixturesReference(
            CEvenementPatient::class,
            MailReceiverFixtures::PATIENT_EVENT_TAG
        );

        $service = new MailReceiverService($event);
        $this->assertInstanceOf(CPatient::class, $service->getObject());
    }

    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorWithOperation(): void
    {
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            MailReceiverFixtures::OPERATION_TAG
        );

        $service = new MailReceiverService($operation);
        $this->assertInstanceOf(CSejour::class, $service->getObject());
    }

    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorWithConsultAnesth(): void
    {
        $consult = $this->getObjectFromFixturesReference(
            CConsultAnesth::class,
            MailReceiverFixtures::CONSULT_ANESTH_TAG
        );

        $service = new MailReceiverService($consult);
        $this->assertInstanceOf(CConsultation::class, $service->getObject());
    }

    /**
     * @return void
     * @throws CMbException
     * @throws TestsException
     */
    public function testConstructorWithFactureCabinet(): void
    {
        $invoice = $this->getObjectFromFixturesReference(
            CFactureCabinet::class,
            MailReceiverFixtures::INVOICE_TAG
        );

        $service = new MailReceiverService($invoice);
        $this->assertInstanceOf(CPatient::class, $service->getObject());
    }

    /**
     * @dataProvider getReceiversProvider
     *
     * @param MailReceiverService $service
     * @param string              $address_type
     * @param string              $type
     * @param array               $expected
     *
     * @return void
     */
    public function testGetReceiversFromPatient(
        MailReceiverService $service,
        string $address_type,
        string $type,
        array $expected
    ): void {
        $receivers = $service->getReceivers(
            $address_type,
            $type
        );

        $this->assertEquals($expected, $receivers);
    }

    public function getReceiversProvider(): array
    {
        /** @var CPatient $patient */
        $patient         = $this->getObjectFromFixturesReference(
            CPatient::class,
            MailReceiverFixtures::PATIENT_TAG
        );
        $service_patient = new MailReceiverService($patient);

        /** @var CConsultation $consult */
        $consult         = $this->getObjectFromFixturesReference(
            CConsultation::class,
            MailReceiverFixtures::CONSULT_TAG
        );
        $service_consult = new MailReceiverService($consult);

        $patient->loadRefsCorrespondantsPatient();
        $patient->loadRefsCorrespondants();
        $patient->_ref_medecin_traitant->setExercicePlace($patient->loadRefMedecinTraitantExercicePlace());

        $patient_receiver = CDestinataire::getFromPatient($patient);

        $praticien = $consult->loadRefPraticien();

        $patient_receivers = [];
        foreach ($patient->_ref_correspondants_patient as $corresp) {
            $patient_receivers[$corresp->_guid] = CDestinataire::getFromCorrespondantPatient($corresp);
        }

        $medical_receiver_mail = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_MAIL
            ),
        ];

        $medical_receiver_mail_consult = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_MAIL,
                $praticien->_id
            ),
        ];

        $medical_receiver_apicrypt = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_APICRYPT
            ),
        ];

        $medical_receiver_apicrypt_consult = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_APICRYPT,
                $praticien->_id
            ),
        ];

        $medical_receiver_mssante = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_MSSANTE
            ),
        ];

        $medical_receiver_mssante_consult = [
            'traitant' => CDestinataire::getFromCMedecin(
                $patient->_ref_medecin_traitant,
                'traitant',
                CDestinataire::ADDRESS_TYPE_MSSANTE,
                $praticien->_id
            ),
        ];

        foreach ($patient->_ref_medecins_correspondants as $correspondant) {
            $medical_receiver_mail[$correspondant->_guid]     = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_MAIL
            );
            $medical_receiver_mail_consult[$correspondant->_guid]     = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_MAIL,
                $praticien->_id
            );
            $medical_receiver_apicrypt[$correspondant->_guid] = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_APICRYPT
            );
            $medical_receiver_apicrypt_consult[$correspondant->_guid] = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_APICRYPT,
                $praticien->_id
            );
            $medical_receiver_mssante[$correspondant->_guid]  = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_MSSANTE
            );
            $medical_receiver_mssante_consult[$correspondant->_guid]  = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                CDestinataire::ADDRESS_TYPE_MSSANTE,
                $praticien->_id
            );
        }

        $consult->loadRefPraticien();
        $surgeon_receiver_mail     = CDestinataire::getFromMediuser(
            $consult->_ref_praticien,
            'praticien',
            CDestinataire::ADDRESS_TYPE_MAIL
        );
        $surgeon_receiver_apicrypt = CDestinataire::getFromMediuser(
            $consult->_ref_praticien,
            'praticien',
            CDestinataire::ADDRESS_TYPE_APICRYPT
        );
        $surgeon_receiver_mssante  = CDestinataire::getFromMediuser(
            $consult->_ref_praticien,
            'praticien',
            CDestinataire::ADDRESS_TYPE_MSSANTE
        );

        return [
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                [],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                [],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                ['CPatient' => array_replace([$patient_receiver], $patient_receivers)],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_mssante],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_apicrypt],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_mail],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_ALL,
                ['CMedecin' => $medical_receiver_mssante],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_ALL,
                ['CMedecin' => $medical_receiver_apicrypt],
            ],
            [
                $service_patient,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_ALL,
                [
                    'CPatient' => array_replace([$patient_receiver], $patient_receivers),
                    'CMedecin' => $medical_receiver_mail,
                ],
            ],

            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                [],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                [],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_CASUAL,
                ['CPatient' => array_replace([$patient_receiver], $patient_receivers)],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_mssante_consult, 'CMediusers' => [$surgeon_receiver_mssante]],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_apicrypt_consult, 'CMediusers' => [$surgeon_receiver_apicrypt]],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_MEDICAL,
                ['CMedecin' => $medical_receiver_mail_consult, 'CMediusers' => [$surgeon_receiver_mail]],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MSSANTE,
                MailReceiverService::RECEIVER_TYPE_ALL,
                ['CMedecin' => $medical_receiver_mssante_consult, 'CMediusers' => [$surgeon_receiver_mssante]],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_APICRYPT,
                MailReceiverService::RECEIVER_TYPE_ALL,
                ['CMedecin' => $medical_receiver_apicrypt_consult, 'CMediusers' => [$surgeon_receiver_apicrypt]],
            ],
            [
                $service_consult,
                MailReceiverService::ADDRESS_TYPE_MAIL,
                MailReceiverService::RECEIVER_TYPE_ALL,
                [
                    'CPatient' => array_replace([$patient_receiver], $patient_receivers),
                    'CMediusers' => [$surgeon_receiver_mail],
                    'CMedecin' => $medical_receiver_mail_consult,
                ]
            ],
        ];
    }
}
