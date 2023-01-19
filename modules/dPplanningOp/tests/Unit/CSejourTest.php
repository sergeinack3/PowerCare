<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Ccam\CBillingPeriod;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\CMedecinFixtures;
use Ox\Mediboard\PlanningOp\CRegleSectorisation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\CGroupsGenerator;
use Ox\Mediboard\Populate\Generators\CLitGenerator;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Populate\Generators\COperationGenerator;
use Ox\Mediboard\Populate\Generators\CServiceGenerator;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class CSejourTest extends OxUnitTestCase
{
    /** @var CMediusers */
    protected static $praticien;

    /** @var CMedecin */
    protected static $medecin;

    /** @var CGroups */
    protected static $group;

    /** @var CLit */
    protected static $lit;

    /** @var CService */
    protected static $service;

    /**
     * @throws TestsException
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::$medecin = $this->getObjectFromFixturesReference(CMedecin::class, CMedecinFixtures::TAG_MEDECIN);
    }

    protected function createSejour(?int $group_id = null, string $entree = null, string $sortie = null): CSejour
    {
        $pat = CPatient::getSampleObject();
        $this->storeOrFailed($pat);

        $sejour                = new CSejour();
        $sejour->patient_id    = $pat->_id;
        $sejour->praticien_id  = static::$praticien->_id;
        $sejour->group_id      = $group_id ?: static::$group->_id;
        $sejour->type          = "comp";
        $sejour->entree_prevue = $entree ?: static::getEntreePrevue();
        $sejour->sortie_prevue = $sortie ?: static::getSortiePrevue();

        if ($msg = $sejour->store()) {
            self::fail($msg);
        }

        return $sejour;
    }

    protected function createAffectations(
        CSejour $sejour,
        string $entree = null,
        int $count = 1,
        string $sortie_affectation = null
    ): array {
        $entree = $entree ?? $sejour->entree_prevue;

        $affectations = [];

        for ($i = 0; $i < $count; $i++) {
            $entree                  = CMbDT::dateTime("+ " . ($i == 0 ? 0 : 1) . " days", $entree);
            $sortie                  = $sortie_affectation ?: CMbDT::dateTime("+ 1 days", $entree);
            $affectation             = new CAffectation();
            $affectation->sejour_id  = $sejour->_id;
            $affectation->service_id = static::$lit->loadRefChambre()->service_id;
            $affectation->lit_id     = static::$lit->_id;
            $affectation->entree     = $entree;
            $affectation->sortie     = $sortie;
            $affectation->store();

            $affectations[] = $affectation;
        }

        return $affectations;
    }

    protected function createBillingPeriod(CSejour $sejour, int $period_statement = 1): void
    {
        $billing_period = new CBillingPeriod();
        $billing_period->codable_class    = $sejour->_class;
        $billing_period->codable_id       = $sejour->_id;
        $billing_period->period_start     = CMbDT::date($sejour->entree);
        $billing_period->period_end       = CMbDT::date($sejour->sortie);
        $billing_period->period_statement = $period_statement;

        $this->storeOrFailed($billing_period);
    }

    protected static function getEntreePrevue(): string
    {
        return CMbDT::transform(null, null, '%Y-%m-%d %H:%M:00');
    }

    public static function getSortiePrevue(): string
    {
        return CMbDT::transform(null, CMbDT::dateTime('+3 days'), '%Y-%m-%d %H:%M:00');
    }

    public function modifyAffectation(string $type_affectation, string $type_modification = '+', int $nb_entries = 1): void
    {
        $sejour       = $this->createSejour();
        $affectations = $this->createAffectations($sejour, null, $nb_entries);

        if ($nb_entries > 1) {
            $affectation = $affectations[1];
        } else {
            $affectation = reset($affectations);
        }
        $affectation->$type_affectation = CMbDT::dateTime($type_modification . '1 hour', $affectation->$type_affectation);

        $affectation->store();

        $reverse_type_affectation = $type_affectation == 'entree' ? 'sortie' : 'entree';

        if ($nb_entries > 1) {
            if ($type_modification == '+') {
                $this->assertEquals($affectation->$type_affectation, array_values($this->getAffectation($sejour))[0]->$reverse_type_affectation);
            } else {
                $this->assertEquals($affectation->$type_affectation, array_values($this->getAffectation($sejour))[2]->$reverse_type_affectation);
            }
        } else {
            $this->assertEquals($affectation->$type_affectation, $affectation->loadRefSejour(false)->$type_affectation);
        }
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeEntryAffectation(): void
    {
        $this->modifyAffectation('entree');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeExitAffectation(): void
    {
        $this->modifyAffectation('sortie');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceEntryAffectation(): void
    {
        $this->modifyAffectation('entree', '-');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceExitAffectation(): void
    {
        $this->modifyAffectation('sortie', '-');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     * @config [CConfiguration] dPpatients sharing multi_group full
     */
    public function testModifyMedecinTraitant(): void
    {
        $sejour      = $this->createSejour();
        $old_medecin = $sejour->medecin_traitant_id;

        $patient                   = $sejour->loadRefPatient();
        $patient->medecin_traitant = static::$medecin->_id;
        $patient->store();

        $new_sejour      = new CSejour();
        $new_sejour->_id = $sejour->_id;
        $new_sejour->loadMatchingObject();

        $new_medecin = $new_sejour->medecin_traitant_id;

        $this->assertNotEquals($old_medecin, $new_medecin);
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testAddAffectation(): void
    {
        $sejour = $this->createSejour();
        $this->createAffectations($sejour);

        $middle_date = CMbDT::dateTime('+1 day', $sejour->entree_prevue);

        $this->createAffectations($sejour, $middle_date);

        $affectations = array_values($this->getAffectation($sejour));

        $this->assertEquals($middle_date, $affectations[0]->sortie);
        $this->assertEquals($affectations[0]->loadRefSejour(false)->sortie_prevue, $affectations[1]->sortie);
    }

    protected function getAffectation(CSejour $sejour): array
    {
        $affectation            = new CAffectation();
        $affectation->sejour_id = $sejour->_id;

        return $affectation->loadMatchingList('affectation_id ASC');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testDeleteFirsAff(): void
    {
        $sejour       = $this->createSejour();
        $affectations = $this->createAffectations($sejour, null, 3);

        $affectations[0]->delete();
        $this->assertEquals($sejour->entree_prevue, array_values($this->getAffectation($sejour))[0]->entree);
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testDeleteSecondAff(): void
    {
        $sejour       = $this->createSejour();
        $affectations = $this->createAffectations($sejour, null, 3);

        $second_affectation = $affectations[1];

        $second_affectation->delete();

        $affectations = array_values($this->getAffectation($sejour));

        $this->assertEquals($affectations[1]->entree, $affectations[0]->sortie);
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testDeleteThirdAff(): void
    {
        $sejour       = $this->createSejour();
        $affectations = $this->createAffectations($sejour, null, 3);

        $third_affectation = $affectations[2];

        $third_affectation->delete();

        $this->assertEquals($affectations[1]->loadRefSejour(false)->sortie_prevue, array_values($this->getAffectation($sejour))[1]->sortie);
    }

    public function modifySejour(string $type = 'entree_prevue', string $type_modification = '+', string $quantity = '1 hour'): CSejour
    {
        $sejour       = $this->createSejour();
        $affectations = $this->createAffectations($sejour, null, 3);

        if ($type == 'sortie_reelle') {
            $sejour->entree_reelle = $sejour->entree_prevue;
            $sejour->sortie_reelle = $sejour->sortie_prevue;
        }
        if ($type == 'entree_prevue') {
            $num = 0;
        } else {
            $num = count($affectations) - 1;
        }

        if ($quantity != '1 hour' && $num > 0) {
            $num--;
        }
        $sejour->$type                  = CMbDT::dateTime($type_modification . $quantity, $sejour->$type);
        $sejour->_back['affectations']  = null;
        $sejour->_count['affectations'] = null;
        $sejour->updateFormFields();
        $sejour->store();
        $sejour->_back['affectations']  = null;
        $sejour->_count['affectations'] = null;

        $type_aff = explode('_', $type)[0];

        $this->assertEquals($sejour->$type, array_values($this->getAffectation($sejour))[$num]->$type_aff);

        return $sejour;
    }

    //Prevue

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeEntrySejourPrevue(): void
    {
        self::modifySejour();
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeExitSejourPrevue(): void
    {
        self::modifySejour('sortie_prevue', '+');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceEntrySejourPrevue(): void
    {
        self::modifySejour('entree_prevue', '-');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceExitSejourPrevue(): void
    {
        self::modifySejour('sortie_prevue', '-');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceALotEntrySejourPrevue(): void
    {
        $sejour = self::modifySejour('entree_prevue', '+', '1 day + 1 hour');
        $this->assertEquals(2, $sejour->countBackRefs('affectations'));
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceALotExitSejourPrevue(): void
    {
        $sejour = self::modifySejour('sortie_prevue', '-', '1 day - 1 hour');
        $this->assertEquals(2, $sejour->countBackRefs('affectations'));
    }

    // Réelle

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeExitSejourReelle(): void
    {
        self::modifySejour('sortie_reelle', '+');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceExitSejourReelle(): void
    {
        self::modifySejour('sortie_reelle', '-');
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceALotExitSejourReelle(): void
    {
        $sejour = self::modifySejour('sortie_reelle', '-', '1 day - 1 hour');
        $this->assertEquals(2, $sejour->countBackRefs('affectations'));
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testRemoveExitSejourReelle(): void
    {
        $sejour = $this->createSejour();
        $this->createAffectations($sejour, null, 2);

        $sejour->entree_reelle = $sejour->entree_prevue;
        $sejour->store();

        $sejour->sortie_reelle = $sejour->sortie_prevue;
        $sejour->store();

        $sejour->_back['affectations']  = null;
        $sejour->_count['affectations'] = null;

        $sejour->sortie_reelle = '';
        $sejour->store();

        $sejour->_back['affectations']  = null;
        $sejour->_count['affectations'] = null;

        $this->assertEquals(2, $sejour->countBackRefs('affectations'));
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testEnlargeEntry(): void
    {
        self::modifyAffectation('entree', '+', 3);
    }

    /**
     * @config [CConfiguration] dPplanningOp CSejour multiple_affectation_pread 1
     * @config [CConfiguration] dPplanningOp CSejour check_collisions no
     */
    public function testReduceExit(): void
    {
        self::modifyAffectation('sortie', '-', 3);
    }

    /**
     * @throws TestsException
     */
    static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$praticien = (new CMediusersGenerator())->generate();
        static::$group     = (new CGroupsGenerator())->generate();
        static::$lit       = (new CLitGenerator())->generate();
        static::$service   = (new CServiceGenerator())->generate();
    }

    /**
     * Test to check for a sectorisation rules to find a service_id
     *
     * @config dPplanningOp CRegleSectorisation use_sectorisation 1
     */
    public function testGetServiceFromSectorisationRules(): void
    {
        $regle                 = new CRegleSectorisation();
        $regle->service_id     = static::$service->_id;
        $regle->function_id    = static::$praticien->function_id;
        $regle->priority       = 1;
        $regle->type_admission = "comp";
        $regle->store();

        $sejour = $this->createSejour($regle->group_id);

        $this->assertEquals($regle->service_id, $sejour->service_id);
    }

    /**
     * Test to check for a sectorisation rules to not find a service inactive
     *
     * @config dPplanningOp CRegleSectorisation use_sectorisation 1
     */
    public function testDontGetServiceFromSectorisationRules(): void
    {
        $service = static::$service;
        $service->cancelled = 1;
        $service->store();

        $regle                 = new CRegleSectorisation();
        $regle->service_id     = $service->_id;
        $regle->function_id    = static::$praticien->function_id;
        $regle->priority       = 1;
        $regle->type_admission = "comp";
        $regle->store();

        $sejour = $this->createSejour($regle->group_id);

        $this->assertNull($sejour->service_id);
    }

    /**
     * Test to load the last operation not canceled
     */
    public function testLoadLastOperationNotCanceled(): void
    {
        $sejour = $this->createSejour();

        $operation  = (new COperationGenerator())->generate();
        $operation2 = (new COperationGenerator())->generate();

        // the same sejour_id
        $operation->sejour_id = $sejour->_id;
        $operation->store();

        $operation2->sejour_id = $sejour->_id;
        $operation2->annulee    = 1;
        $operation2->store();

        $sejour->loadRefLastOperation(true);

        $this->assertEquals($sejour->_ref_last_operation->_id, $operation->_id);
        $this->assertNotEquals($sejour->_ref_last_operation->_id, $operation2->_id);
    }

    protected function getSejourBilling(int $period_statement = 1): CSejour
    {
        $sejour = $this->createSejour(null, CMbDT::dateTime('-1 days -1 hour'), CMbDT::dateTime('+1 hour'));
        $this->createAffectations($sejour, $sejour->entree, 1, $sejour->sortie);
        $this->createBillingPeriod($sejour, $period_statement);

        return $sejour;
    }

    public function testStoreNotBilledSejour(): void
    {
        $sejour = $this->getSejourBilling(0);

        $sejour->clearBackRefCache('affectations');
        $sejour->entree_reelle = $sejour->entree_prevue;
        $sejour->sortie_reelle = CMbDT::dateTime('-1 minute');

        $this->assertNull($sejour->store());

        $affectations = array_values($this->getAffectation($sejour));
        $this->assertEquals($sejour->sortie_reelle, $affectations[0]->sortie);
    }

    public function testStoreBilledSejour(): void
    {
        $sejour = $this->getSejourBilling();

        $sejour->clearBackRefCache('affectations');
        $sejour->entree_reelle = $sejour->entree_prevue;
        $sejour->sortie_reelle = CMbDT::dateTime('-1 minute');

        $this->assertNull($sejour->store());

        $affectations = array_values($this->getAffectation($sejour));
        $this->assertEquals($sejour->sortie_reelle, $affectations[0]->sortie);

        $sejour->uf_medicale_id = 1;

        $this->assertNotNull($sejour->store());
    }
}
