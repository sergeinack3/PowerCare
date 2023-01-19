<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Tests\Unit;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Urgences\Services\PlacementPatientsService;
use Ox\Tests\OxUnitTestCase;

class PlacementPatientsServiceTest extends OxUnitTestCase
{
    /**
     * Create CLit object
     *
     * @throws Exception
     */
    protected static function createLit(CChambre $chambre): CLit
    {
        $lit             = new CLit();
        $lit->nom        = 'test';
        $lit->chambre_id = $chambre->_id;
        if ($msg = $lit->store()) {
            self::fail($msg);
        }

        return $lit;
    }

    /**
     * Create CAffectation object
     *
     * @throws Exception
     */
    protected static function createAffectationUrg(CService $service, CLit $lit): CAffectation
    {
        $affectation             = new CAffectation();
        $affectation->entree     = CMbDT::dateTime('-1 HOUR');
        $affectation->sortie     = CMbDT::dateTime('+1 HOUR');
        $affectation->service_id = $service->_id;
        $affectation->lit_id     = $lit->_id;
        if ($msg = $affectation->store()) {
            self::fail($msg);
        }

        return $affectation;
    }

    /**
     * Create CService object
     *
     * @throws Exception
     */
    protected static function createServiceUrg(): CService
    {
        $service              = new CService();
        $service->group_id    = CGroups::get()->_id;
        $service->nom         = "Service Urgence";
        $service->type_sejour = "urg";
        $service->urgence     = 1;

        if ($msg = $service->store()) {
            self::fail($msg);
        }

        return $service;
    }

    /**
     * Create CService object
     *
     * @throws Exception
     */
    protected static function createServiceUhcd(): CService
    {
        $service              = new CService();
        $service->group_id    = CGroups::get()->_id;
        $service->nom         = "Service UHCD";
        $service->type_sejour = "urg";
        $service->uhcd        = 1;

        if ($msg = $service->store()) {
            self::fail($msg);
        }

        return $service;
    }

    /**
     * Create CChambre object
     *
     * @throws Exception
     */
    protected static function createRoom(CService $service): CChambre
    {
        $chambre             = new CChambre();
        $chambre->service_id = $service->_id;
        $chambre->nom        = uniqid('chambre-');
        $chambre->annule     = 0;
        $chambre->code       = rand(1, 100000000);

        if ($msg = $chambre->store()) {
            self::fail($msg);
        }

        return $chambre;
    }

    /**
     * Test to get emergency rooms
     *
     * @throws Exception
     */
    public function testGetEmergencyRooms(): void
    {
        $this->createRoom(self::createServiceUrg());
        $placement_service = new PlacementPatientsService();
        $chambres = $placement_service->getEmergencyRooms();

        $this->assertNotEmpty($chambres);
    }

    /**
     * Test to get UHCD rooms
     *
     * @throws Exception
     */
    public function testGetUHCDRooms(): void
    {
        $this->createRoom(self::createServiceUhcd());
        $placement_service = new PlacementPatientsService();
        $chambres = $placement_service->getEmergencyRooms(true);

        $this->assertNotEmpty($chambres);
    }

    /**
     * @throws Exception
     */
    public function testAddBlockedBedRoomsDefault(): void
    {
        $service           = $this->createServiceUrg();
        $chambre           = $this->createRoom($service);
        $lit               = $this->createLit($chambre);
        $affectation       = $this->createAffectationUrg($service, $lit);
        $topologie         = ['urgence'];
        $placement_service = new PlacementPatientsService();
        $topologie         = $placement_service->addBlockedBedRooms('urgence', $topologie);
        $this->assertTrue(in_array($affectation->_id, CMbArray::pluck($topologie['urgence'], '_id')));
    }

    /**
     * @throws CMbException
     */
    public function testAddBlockedBedRoomsReturnException(): void
    {
        $placement_service = new PlacementPatientsService();
        $this->expectExceptionMessage('Type service is not accepted');
        $placement_service->addBlockedBedRooms('wrong_type_service', []);
    }
}
