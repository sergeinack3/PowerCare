<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Ihe\CPDQm;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CSenderHTTP;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures
 */
class FhirApiFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const REF_USER_FHIR = 'fhir_user_api';

    /** @var string */
    public const REF_PATIENT_FHIR = 'fhir_patient_api';

    /** @var CUser */
    private $user_fhir;

    /** @var CSenderHTTP */
    private $sender_http;

    /** * @var CPatient */
    private $patient;

    public function load()
    {
        $this->user_fhir = $this->generateUser();

        $this->patient = $this->generatePatient();

        $this->generateActors();
    }

    /**
     * @return CUser
     * @throws FixturesException
     */
    private function generateUser(): CUser
    {
        /** @var CMediusers $mediuser */
        $mediuser                 = $this->getUsers(1)[0];
        $mediuser->_id            = null;
        $mediuser->_user_username = 'fhir_api_user_fixture';
        $mediuser->_user_password = 'fhir_mdp_1234';
        $this->store($mediuser, self::REF_USER_FHIR);

        $perm_module = new CPermModule();
        $perm_module->user_id = $mediuser->_id;
        $perm_module->mod_id = null;
        $perm_module->view = 2;
        $perm_module->permission = 2;
        $this->store($perm_module);


        return $mediuser->loadRefUser();
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['fhir_api', 100];
    }

    private function generateActors()
    {
        $this->sender_http = $this->generateSender();
        $list_messages     = $this->getListMessages();
        $this->generateMessage($this->sender_http, $list_messages);
        //$this->generateReceiver();
    }

    /**
     * @return array[]
     */
    private function getListMessages(): array
    {
        $patient_fhir = new CFHIRResourcePatient();

        return [
            [
                'version'     => '4.0',
                'profil'      => CPDQm::TYPE,
                'message'     => 'CFHIRInteractionSearch',
                'transaction' => $patient_fhir->getProfile(),
            ],
            [
                'version'     => '4.0',
                'profil'      => CPDQm::TYPE,
                'message'     => 'CFHIRInteractionRead',
                'transaction' => $patient_fhir->getProfile(),
            ],
        ];
    }

    /**
     * @return CSenderHTTP
     * @throws FixturesException
     */
    private function generateSender()
    {
        $sender           = new CSenderHTTP();
        $sender->nom      = 'fhir_fixture_sender';
        $sender->user_id  = $this->user_fhir->_id;
        $sender->role     = CAppUI::conf('instance_role');
        $sender->group_id = CGroups::loadCurrent()->_id;
        $this->store($sender);

        return $sender;
    }

    /**
     * @param CInteropActor $actor
     * @param array         $list_messages
     *
     * @return array
     * @throws FixturesException
     */
    private function generateMessage(CInteropActor $actor, array $list_messages)
    {
        $messages = [];
        foreach ($list_messages as $content_message) {
            $message = new CMessageSupported();
            $message->setObject($actor);
            $message->profil      = CMbArray::get($content_message, 'profil');
            $message->transaction = CMbArray::get($content_message, 'transaction');
            $message->message     = CMbArray::get($content_message, 'message');
            $message->version     = CMbArray::get($content_message, 'version');
            $message->active      = 1;
            $this->store($message);

            $messages[] = $message;
        }

        return $messages;
    }

    private function generatePatient(): CPatient
    {
        /** @var CPatient $patient */
        $patient                  = CPatient::getSampleObject();
        $patient->nom             = 'fhir_patient_fixtures';
        $patient->nom_jeune_fille = 'fhir_patient_fixtures';
        $patient->prenom          = 'fhir_patient_prenom';
        $patient->naissance       = CMbDT::date('2000-01-01');
        $this->store($patient, self::REF_PATIENT_FHIR);

        return $patient;
    }

    /**
     * @return CSejour
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function generateSejour(): CSejour
    {
        $user_id = $this->getUser()->_id;

        $patient = FhirResourcesHelper::getSamplePatient();
        $this->store($patient);

        $group = FhirResourcesHelper::getSampleFhirGroups();
        $this->store($group);

        $sejour               = FhirResourcesHelper::getSampleFhirSejour();
        $sejour->patient_id   = $patient->_id;
        $sejour->praticien_id = $user_id;
        $sejour->group_id     = $group->_id;
        $this->store($sejour);

        return $sejour;
    }

    public function purge()
    {
        parent::purge();

        $users = new CUser();
        $users->user_username = 'fhir_api_user_fixture';
        if ($users->loadMatchingObject()) {
            if ($msg = $users->purge()) {
                throw new FixturesException($msg);
            }
        }

    }
}
