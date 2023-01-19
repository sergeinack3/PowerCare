<?php

namespace Ox\Mediboard\Hospi\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Tests\OxUnitTestCase;

class CServiceTest extends OxUnitTestCase {
  public function test__construct() {
    $service = new CService();
    $this->assertInstanceOf(CService::class, $service);
  }

  public function testLoadGroupList() {
    $service = new CService();
    $this->assertContainsOnlyInstancesOf(CService::class, $service->loadGroupList());
  }

  public function testLoadServicesUrgence() {
    $services_urgences = CService::loadServicesUrgence();
    $this->assertEquals(count($services_urgences), array_sum(CMbArray::pluck($services_urgences, "urgence")));
  }

  public function testLoadServicesUHCD() {
    $services_UHCD = CService::loadServicesUHCD();
    $this->assertEquals(count($services_UHCD), array_sum(CMbArray::pluck($services_UHCD, "uhcd")));
  }

  public function testLoadServicesImagerie() {
    $services_imagerie = CService::loadServicesImagerie();
    $this->assertEquals(count($services_imagerie), array_sum(CMbArray::pluck($services_imagerie, "imagerie")));
  }

  public function testLoadServiceExterne() {
    $service_externe = CService::loadServiceExterne();
    $this->assertEquals("1", $service_externe->externe);
  }

  public function loadServiceRadiologie() {
    $service_radiologie = CService::loadServiceRadiologie();
    $this->assertEquals("1", $service_radiologie->radiologie);
  }
}
