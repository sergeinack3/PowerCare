<?php

namespace Ox\Mediboard\Hospi\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Populate\Generators\CLitGenerator;
use Ox\Mediboard\Hotellerie\CBedCleanup;
use Ox\Tests\OxUnitTestCase;

class CLitTest extends OxUnitTestCase {

  public function test__construct() {
    $lit = new CLit();
    $this->assertInstanceOf(CLit::class, $lit);
  }

  public function testCheckOverBooking() {
    //On prends un lit avec des affectations
    $lit                    = new CLit();
    $lit->_ref_affectations = [new CAffectation(), new CAffectation(), new CAffectation()];

    //Les lits doivent être différents (id différents et date d'entree/sortie aussi)
    $date_entree = "2005-04-19 16:00:00";
    $date_sortie = "2005-04-19 20:00:00";
    foreach ($lit->_ref_affectations as $_number => $_aff) {
      $_aff->_id    = $_number;
      $_aff->entree = CMbDT::dateTime("+$_number DAY", $date_entree);
      $_aff->sortie = CMbDT::dateTime("+$_number DAY", $date_sortie);
    }
    $lit->checkOverBooking();

    //Toutes les affectations sont différentes, il n'y a donc pas d'overbooking
    $this->assertTrue($lit->_overbooking == 0);

    $lit->_ref_affectations[]          = new CAffectation();
    $lit->_ref_affectations[3]->_id    = 3;
    $lit->_ref_affectations[3]->entree = "2005-04-19 15:00:00";
    $lit->_ref_affectations[3]->sortie = "2005-04-19 18:00:00";
    $lit->checkOverBooking();

    //La date d'entree/sortie écrase celle de la première affectation : il y a un overbooking
    $this->assertTrue($lit->_overbooking == 1);
  }

  /**
   * @group schedules
   * @throws \Exception
   */
  public function testLoadCurrentCleanup() {
    $lit = (new CLitGenerator())->setForce(true)->generate();

    $cleanup                 = new CBedCleanup();
    $cleanup->date           = "2019-07-16";
    $cleanup->lit_id         = $lit->_id;
    $cleanup->store();

    $result = $lit->loadCurrentCleanup("2019-07-16");
    $this->assertSame($result, $lit->_ref_current_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanup->cleanup_bed_id, $result->cleanup_bed_id);

    $result = $lit->loadCurrentCleanup("2019-07-17");
    $this->assertNull($result->_id);
  }

  /**
   * @group schedules
   * @throws \Exception
   */
  public function testLoadLastEndedCleanup() {
    $lit = (new CLitGenerator())->setForce(true)->generate();

    $cleanup                 = new CBedCleanup();
    $cleanup->datetime_start = "2019-07-16 08:00:00";
    $cleanup->datetime_end   = "2019-07-16 09:30:00";
    $cleanup->lit_id         = $lit->_id;
    $cleanup->store();

    $result = $lit->loadLastEndedCleanup();

    $this->assertSame($result, $lit->_ref_last_ended_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanup->cleanup_bed_id, $result->cleanup_bed_id);
  }

  /**
   * @group   schedules
   * @throws \Exception
   */
  public function testLoadLastCleanup() {
    $lit = (new CLitGenerator())->setForce(true)->generate();

    $cleanup1                 = new CBedCleanup();
    $cleanup1->date           = "2019-07-15";
    $cleanup1->status_room    = "propre";
    $cleanup1->lit_id         = $lit->_id;
    $cleanup1->store();

    $result = $lit->loadLastCleanup();
    $this->assertNull($result->_id);

    $result = $lit->loadLastCleanup("2019-07-15");
    $this->assertSame($result, $lit->_ref_last_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanup1->_id, $result->_id);

    $cleanupEnded               = new CBedCleanup();
    $cleanupEnded->date         = "2019-07-15";
    $cleanupEnded->status_room  = "propre";
    $cleanupEnded->datetime_end = "2019-07-16 23:00:00";
    $cleanupEnded->lit_id       = $lit->_id;
    $cleanupEnded->store();

    $result = $lit->loadLastCleanup("2019-07-16");
    $this->assertSame($result, $lit->_ref_last_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanupEnded->_id, $result->_id);

    $cleanup2                 = new CBedCleanup();
    $cleanup2->date           = "2019-07-16";
    $cleanup2->status_room    = "faire";
    $cleanup2->lit_id         = $lit->_id;
    $cleanup2->store();
    $result = $lit->loadLastCleanup("2019-07-17");
    $this->assertSame($result, $lit->_ref_last_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanup2->_id, $result->_id);

    $cleanup3                 = new CBedCleanup();
    $cleanup3->date           = "2019-07-17";
    $cleanup3->status_room    = "validation";
    $cleanup3->lit_id         = $lit->_id;
    $cleanup3->store();
    $result = $lit->loadLastCleanup("2019-07-17");
    $this->assertSame($result, $lit->_ref_last_cleanup);
    $this->assertInstanceOf(CBedCleanup::class, $result);
    $this->assertEquals($cleanup3->_id, $result->_id);
  }
}
