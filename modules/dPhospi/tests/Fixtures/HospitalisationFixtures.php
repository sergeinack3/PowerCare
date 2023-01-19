<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;

/**
 * @description Datas used for hospitalization
 */
class HospitalisationFixtures extends Fixtures
{
    public const SERVICE_HOSPITALISATION = 'hospitalisation_service';
    public const CHAMBRE_HOSPITALISATION = 'hospitalisation_chambre';
    public const LIT_HOSPITALISATION     = 'hospitalisation_lit';
    public const SEJOUR_HOSPITALISATION  = 'hospitalisation_sejour';

    /** @var CMediusers */
    private $mediuser;

    public function load()
    {
        $this->mediuser = $this->getUser();

        $service = $this->createService();
        $chambre = $this->createChambre($service);
        $this->createLit($chambre);

        $this->createSejour();
    }

    private function createService(): CService
    {
        $service            = CService::getSampleObject();
        $service->group_id  = CGroups::get()->_id;
        $service->cancelled = 0;
        $this->store($service, self::SERVICE_HOSPITALISATION);

        return $service;
    }

    private function createChambre(CService $service): CChambre
    {
        $chambre             = CChambre::getSampleObject();
        $chambre->service_id = $service->_id;
        $this->store($chambre, self::CHAMBRE_HOSPITALISATION);

        return $chambre;
    }

    private function createLit(CChambre $chambre): CLit
    {
        $lit             = CLit::getSampleObject();
        $lit->chambre_id = $chambre->_id;
        $this->store($lit, self::LIT_HOSPITALISATION);

        return $lit;
    }


    private function createSejour(): CSejour
    {
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        $date = CMbDT::date();

        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $this->mediuser->_id;
        $sejour->group_id      = $this->mediuser->loadRefFunction()->group_id;
        $sejour->entree_prevue = CMbDT::dateTime("00:00:00", $date);
        $sejour->sortie_prevue = CMbDT::dateTime("23:59:59", $date);
        $sejour->libelle       = uniqid();
        $this->store($sejour, self::SEJOUR_HOSPITALISATION);

        return $sejour;
    }
}
