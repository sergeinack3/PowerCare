<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\CGroupsGenerator;
use Ox\Mediboard\Populate\Generators\CLitGenerator;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Mediboard\Soins\Services\AffectationService;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class AffectationTest extends OxUnitTestCase
{
    /** @var CPatient */
    protected static $patient;

    /** @var CMediusers */
    protected static $praticien;

    /** @var CGroups */
    protected static $group;

    /** @var CLit */
    protected static $lit;

    /**
     * Set Up
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$patient   = (new CPatientGenerator())->setForce(true)->generate();
        static::$praticien = (new CMediusersGenerator())->generate();
        static::$group    = (new CGroupsGenerator())->generate();
        static::$lit       = (new CLitGenerator())->generate();
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testUpdateSejoursFromAffectations(): void
    {
        $affectation1 = $this->createAffectation(CMbDT::dateTime('+10 days'));
        $affectation2 = $this->createAffectation(CMbDT::dateTime('+20 days'));

        $affectations        = [$affectation1, $affectation2];
        $affectation_service = new AffectationService();
        $actual              = array_keys($affectation_service->updateSejoursFromAffectations($affectations, []));
        $expected            = array_unique(array_column($affectations, 'sejour_id'));
        $this->assertEquals($expected, $actual);

        $expected = [
            $this->createSejour(CMbDT::dateTime('+30 days')),
            $this->createSejour(CMbDT::dateTime('+40 days')),
            $this->createSejour(CMbDT::dateTime('+50 days')),
        ];
        $actual   = $affectation_service->updateSejoursFromAffectations([], $expected);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test to load affectation
     *
     * @throws TestsException
     * @throws Exception
     */
    public function testLoadAffectations(): void
    {
        $group_id = static::$group->_id;
        $new_affectation =  $this->createAffectation();
        $sejour_id = $new_affectation->sejour_id;

        $ljoin            = [];
        $ljoin["lit"]     = "affectation.lit_id = lit.lit_id";
        $ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
        $ljoin["sejour"]  = "affectation.sejour_id = sejour.sejour_id";

        $where                          = [];
        $where["affectation.sejour_id"] = "= '$sejour_id'";
        $where["sejour.group_id"]       = "= '$group_id'";

        $affectation_service = new AffectationService();
        $affectations        = $affectation_service->loadAffectations($where, $ljoin, [], null, "", "");

        $this->assertEquals($new_affectation->_id, reset($affectations)->_id);
    }

    /**
     * Create Sejour object
     *
     * @throws Exception
     */
    protected static function createSejour(?string $entree_prevue = null): CSejour
    {
        $sejour                = new CSejour();
        $sejour->patient_id    = static::$patient->_id;
        $sejour->praticien_id  = static::$praticien->_id;
        $sejour->group_id      = static::$group->_id;
        $sejour->type          = "comp";
        $sejour->entree_prevue = $entree_prevue ?: CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+ 5 DAYS", $entree_prevue);

        if ($msg = $sejour->store()) {
            self::fail($msg);
        }

        return $sejour;
    }

    /**
     * Create Affectation object
     *
     * @throws Exception
     */
    protected function createAffectation(?string $entree_prevue = null): CAffectation
    {
        $sejour = $this->createSejour($entree_prevue);

        $entree                  = CMbDT::dateTime($sejour->entree_prevue);
        $sortie                  = CMbDT::dateTime("+ 1 days", $entree);
        $affectation             = new CAffectation();
        $affectation->sejour_id  = $sejour->_id;
        $affectation->service_id = static::$lit->loadRefChambre()->service_id;
        $affectation->lit_id     = static::$lit->_id;
        $affectation->entree     = $entree;
        $affectation->sortie     = $sortie;
        $affectation->store();

        return $affectation;
    }
}
