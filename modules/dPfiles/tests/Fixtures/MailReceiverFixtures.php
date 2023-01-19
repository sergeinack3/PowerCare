<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Fixtures for testing the MailReceiverService
 */
class MailReceiverFixtures extends Fixtures implements GroupFixturesInterface
{
    public const GROUP_NAME = 'mail_receiver';

    public const GROUP_TAG          = 'mail_receiver_group';
    public const FUNCTION_TAG       = 'mail_receiver_function';
    public const SURGEON_TAG        = 'mail_receiver_surgeon';
    public const ANESTHETIST_TAG    = 'mail_receiver_anesthetist';
    public const GENERALIST_TAG     = 'mail_receiver_generalist';
    public const SERVICE_TAG        = 'mail_receiver_service';
    public const ROOM_TAG           = 'mail_receiver_room';
    public const BED_TAG            = 'mail_receiver_bed';
    public const BLOC_TAG           = 'mail_receiver_bloc';
    public const OPERATING_ROOM_TAG = 'mail_receiver_operating_room';
    public const PATIENT_TAG        = 'mail_receiver_patient';
    public const SEJOUR_TAG         = 'mail_receiver_sejour';
    public const OPERATION_TAG      = 'mail_receiver_operation';
    public const PRESCRIPTION_TAG   = 'mail_receiver_prescription';
    public const CONSULT_TAG        = 'mail_receiver_consultation';
    public const CONSULT_ANESTH_TAG = 'mail_receiver_consult_anesth';
    public const INVOICE_TAG        = 'mail_receiver_facture';
    public const PATIENT_EVENT_TAG  = 'mail_receiver_patient_event';

    protected CGroups $group;
    protected CFunctions $function;
    protected CService $service;
    protected CBlocOperatoire $bloc;
    protected CSalle $operating_room;
    protected CLit $bed;
    protected CPatient $patient;
    protected CMediusers $surgeon;
    protected CMediusers $anesthetist;

    /**
     * @return void
     * @throws FixturesException
     */
    public function load(): void
    {
        $this->generateGroup();
        $this->generateFunction();

        $this->generateSurgeon();
        $this->generateAnesthetist();
        $this->generateGeneralist();

        $this->generateService();
        $this->generateBloc();

        $this->generatePatient();

        $this->generateOperation();
        $this->generateConsultations();
        $this->generatePatientEvent();
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateGroup(): void
    {
        $this->group                 = new CGroups();
        $this->group->_name          = 'Clinique Test';
        $this->group->raison_sociale = $this->group->_name;
        $this->group->code           = 'clinique_test';

        $this->store($this->group, self::GROUP_TAG);
    }

    /**
     * @throws FixturesException
     */
    public function generateFunction(): void
    {
        $this->function           = new CFunctions();
        $this->function->group_id = $this->group->_id;
        $this->function->text     = 'Cabinet test';
        $this->function->type     = 'cabinet';
        $this->function->color    = 'FFFFFF';

        $this->store($this->function, self::FUNCTION_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateSurgeon(): void
    {
        $this->surgeon                  = new CMediusers();
        $this->surgeon->_user_username  = 'surgeon_fixtures';
        $this->surgeon->_user_last_name = 'CHIRURGIEN';
        $this->surgeon->_user_last_name = 'Jean';
        $this->surgeon->function_id     = $this->function->_id;
        $this->surgeon->_user_type      = 3;
        $this->surgeon->adeli           = '991111170';
        $this->surgeon->spec_cpam_id    = 41;
        $this->surgeon->secteur         = '1';
        $this->surgeon->_user_email     = 'jchirurgien@example.com';
        $this->surgeon->mail_apicrypt   = 'jchirurgien@apicrypt.fr';
        $this->surgeon->mssante_address = 'jchirurgien@mssante.fr';
        $this->store($this->surgeon, self::SURGEON_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateAnesthetist(): void
    {
        $this->anesthetist                  = new CMediusers();
        $this->anesthetist->_user_username  = 'anesthesist_fixtures';
        $this->anesthetist->_user_last_name = 'ANESTHESISTE';
        $this->anesthetist->_user_last_name = 'Michel';
        $this->anesthetist->function_id     = $this->function->_id;
        $this->anesthetist->_user_type      = 4;
        $this->anesthetist->adeli           = '991111170';
        $this->anesthetist->spec_cpam_id    = 2;
        $this->anesthetist->secteur         = '1';
        $this->anesthetist->_user_email     = 'manesthesiste@example.com';
        $this->anesthetist->mail_apicrypt   = 'manesthesiste@apicrypt.fr';
        $this->anesthetist->mssante_address = 'manesthesiste@mssante.fr';
        $this->store($this->anesthetist, self::ANESTHETIST_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateGeneralist(): void
    {
        $user                           = new CMediusers();
        $user->_user_username           = 'generalist_fixtures';
        $user->_user_last_name          = 'GENERALISTE';
        $user->_user_last_name          = 'Edouard';
        $user->function_id              = $this->function->_id;
        $user->_user_type               = 13;
        $user->adeli                    = '991111170';
        $user->spec_cpam_id             = 1;
        $user->secteur                  = '1';
        $this->surgeon->_user_email     = 'jchirurgien@example.com';
        $this->surgeon->mail_apicrypt   = 'jchirurgien@apicrypt.fr';
        $this->surgeon->mssante_address = 'jchirurgien@mssante.fr';
        $this->store($user, self::GENERALIST_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateService(): void
    {
        $this->service           = new CService();
        $this->service->group_id = $this->group->_id;
        $this->service->nom      = 'CHIR 1';
        $this->store($this->service, self::SERVICE_TAG);

        $room             = new CChambre();
        $room->service_id = $this->service->_id;
        $room->nom        = '1';

        $this->store($room, self::ROOM_TAG);

        $this->bed             = new CLit();
        $this->bed->chambre_id = $room->_id;
        $this->bed->nom        = 'CP';
        $this->store($this->bed, self::BED_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateBloc(): void
    {
        $this->bloc           = new CBlocOperatoire();
        $this->bloc->group_id = $this->group->_id;
        $this->bloc->nom      = 'AMBU 1';
        $this->bloc->type     = 'chir';

        $this->store($this->bloc, self::BLOC_TAG);

        $this->operating_room          = new CSalle();
        $this->operating_room->bloc_id = $this->bloc->_id;
        $this->operating_room->nom     = 'A';
        $this->operating_room->stats   = '1';
        $this->operating_room->dh      = '1';

        $this->store($this->operating_room, self::OPERATING_ROOM_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generatePatient(): void
    {
        $generalist                  = new CMedecin();
        $generalist->group_id        = $this->group->_id;
        $generalist->spec_cpam_id    = 1;
        $generalist->nom             = 'GENE';
        $generalist->prenom          = 'Alain';
        $generalist->email           = 'agene@example.com';
        $generalist->email_apicrypt  = 'agene@apicrypt.fr';
        $generalist->mssante_address = 'agene@mssante.fr';

        $this->store($generalist);

        $place                            = new CExercicePlace();
        $place->exercice_place_identifier = 'Cabinet';
        $this->store($place);

        $generalist_place                    = new CMedecinExercicePlace();
        $generalist_place->medecin_id        = $generalist->_id;
        $generalist_place->exercice_place_id = $place->_id;
        $generalist_place->mssante_address   = 'cabinet_generalist@mssante.fr';
        $this->store($generalist_place);

        $specialist                  = new CMedecin();
        $specialist->group_id        = $this->group->_id;
        $specialist->spec_cpam_id    = 41;
        $specialist->nom             = 'ORTHO';
        $specialist->prenom          = 'Albert';
        $specialist->email           = 'aortho@example.com';
        $specialist->email_apicrypt  = 'aortho@apicrypt.fr';
        $specialist->mssante_address = 'aortho@mssante.fr';

        $this->store($specialist);

        $this->patient                                     = new CPatient();
        $this->patient->group_id                           = $this->group->_id;
        $this->patient->nom                                = 'TEST';
        $this->patient->prenom                             = 'Gilbert';
        $this->patient->sexe                               = 'm';
        $this->patient->naissance                          = '1980-01-01';
        $this->patient->_code_insee                        = 17000;
        $this->patient->cp                                 = '17000';
        $this->patient->email                              = 'gtest@example.com';
        $this->patient->medecin_traitant                   = $generalist->_id;
        $this->patient->medecin_traitant_declare           = '1';
        $this->patient->medecin_traitant_exercice_place_id = $generalist_place->_id;

        $this->store($this->patient, self::PATIENT_TAG);

        $dossier               = new CDossierMedical();
        $dossier->object_class = $this->patient->_class;
        $dossier->object_id    = $this->patient->_id;

        $this->store($dossier);
        $this->patient->_ref_dossier_medical = $dossier;

        $corresp             = new CCorrespondant();
        $corresp->patient_id = $this->patient->_id;
        $corresp->medecin_id = $specialist->_id;
        $this->store($corresp);

        $patient_corresp_1             = new CCorrespondantPatient();
        $patient_corresp_1->patient_id = $this->patient->_id;
        $patient_corresp_1->nom        = 'CONJOINT';
        $patient_corresp_1->relation   = 'confiance';
        $patient_corresp_1->prenom     = 'Gertrude';
        $patient_corresp_1->email      = 'gconjoint@example.com';
        $this->store($patient_corresp_1);

        $patient_corresp_2             = new CCorrespondantPatient();
        $patient_corresp_2->patient_id = $this->patient->_id;
        $patient_corresp_2->nom        = 'EMPLOYEUR';
        $patient_corresp_2->relation   = 'employeur';
        $this->store($patient_corresp_2);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateOperation(): void
    {
        $sejour                = new CSejour();
        $sejour->patient_id    = $this->patient->_id;
        $sejour->praticien_id  = $this->surgeon->_id;
        $sejour->group_id      = $this->group->_id;
        $sejour->service_id    = $this->service->_id;
        $sejour->type          = 'ambu';
        $sejour->entree_prevue = CMbDT::format(null, '%Y-%m-%d 08:00:00');
        $sejour->sortie_prevue = CMbDT::format(null, '%Y-%m-%d 12:00:00');
        $sejour->libelle       = 'Test mailReceiver';

        $this->store($sejour, self::SEJOUR_TAG);

        $operation                 = new COperation();
        $operation->chir_id        = $this->surgeon->_id;
        $operation->sejour_id      = $sejour->_id;
        $operation->salle_id       = $this->operating_room->_id;
        $operation->date           = CMbDT::date();
        $operation->time_operation = '10:00:00';
        $operation->libelle        = 'Test MailReceiver';

        $this->store($operation, self::OPERATION_TAG);

        $prescription               = new CPrescription();
        $prescription->praticien_id = $this->surgeon->_id;
        $prescription->group_id     = $this->group->_id;
        $prescription->object_class = $sejour->_class;
        $prescription->object_id    = $sejour->_id;
        $prescription->type         = 'sejour';

        $this->store($prescription, self::PRESCRIPTION_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    public function generateConsultations(): void
    {
        $plage          = new CPlageconsult();
        $plage->chir_id = $this->anesthetist->_id;
        $plage->date    = CMbDT::date();
        $plage->freq    = '00:05:00';
        $plage->debut   = '08:00:00';
        $plage->fin     = '18:00:00';

        $this->store($plage);

        $consultation                  = new CConsultation();
        $consultation->owner_id        = $this->anesthetist->_id;
        $consultation->plageconsult_id = $plage->_id;
        $consultation->patient_id      = $this->patient->_id;
        $consultation->heure           = '16:00:00';
        $consultation->duree           = 1;
        $consultation->chrono          = 8;

        $this->store($consultation, self::CONSULT_TAG);

        $anesth                  = new CConsultAnesth();
        $anesth->consultation_id = $consultation->_id;
        $anesth->chir_id         = $this->anesthetist->_id;

        $this->store($anesth, self::CONSULT_ANESTH_TAG);

        $invoice               = new CFactureCabinet();
        $invoice->group_id     = $this->group->_id;
        $invoice->patient_id   = $this->patient->_id;
        $invoice->praticien_id = $this->anesthetist->_id;
        $invoice->numero       = 1;
        $invoice->ouverture    = CMbDT::dateTime();
        $invoice->remise       = 0;
        $invoice->type_facture = 'maladie';
        $invoice->statut_envoi = 'non_envoye';
        $invoice->du_patient   = 0;
        $invoice->du_tiers     = 0;

        $this->store($invoice, self::INVOICE_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     */
    public function generatePatientEvent(): void
    {
        $event                     = new CEvenementPatient();
        $event->date               = CMbDT::dateTime('+1 day');
        $event->libelle            = 'Test MailReceiver';
        $event->praticien_id       = $this->surgeon->_id;
        $event->dossier_medical_id = $this->patient->_ref_dossier_medical->_id;
        $event->owner_id           = $this->surgeon->_id;
        $event->creation_date      = CMbDT::dateTime();

        $this->store($event, self::PATIENT_EVENT_TAG);
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return [self::GROUP_NAME];
    }
}
