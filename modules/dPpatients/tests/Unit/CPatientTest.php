<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use DateTimeImmutable;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CModelObject;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\Services\PatientMergeService;
use Ox\Mediboard\Patients\Services\PatientSearchService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class CPatientTest extends OxUnitTestCase
{
    /** @var array $patients_example */
    protected static $patients_example = [];

    /**
     * Load a couple patient examples
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $patient                = new CPatient();
        self::$patients_example = array_values($patient->loadList(null, null, 10));
    }

    /**
     * Tests if it count the amount of ids in a specific array
     * @throws CMbException
     */
    public function testCountBloodRelatives(): void
    {
        $total = [
            "bros"     => [[101, 'biologique'], [102, 'civil']],
            "children" => [],
            "parent_1" => [200, 'biologique'],
            "parent_2" => 0,
        ];

        $this->assertEquals(3, CPatient::countBloodRelatives($total));
    }

    /**
     * Tests if it can transform Ids from a specific array to objects
     * @throws CMbModelNotFoundException
     */
    public function testTransformRelativesPatient(): void
    {
        $ex = &self::$patients_example;

        $total = [
            "bros"     => [[$ex[0]->_id, 'biologique'], [$ex[1]->_id, 'civil']],
            "children" => [[$ex[2]->_id, 'biologique']],
            "parent_1" => null,
            "parent_2" => [$ex[3]->_id, 'biologique'],
        ];

        $p1 = CPatient::find($ex[0]->_id);
        $p2 = CPatient::find($ex[1]->_id);
        $p3 = CPatient::find($ex[2]->_id);
        $p4 = CPatient::find($ex[3]->_id);


        $expected = [
            "bros"     => [[$p1, 'biologique'], [$p2, 'civil']],
            "children" => [[$p3, 'biologique']],
            "parent_1" => null,
            "parent_2" => [$p4, 'biologique'],
        ];

        $this->assertEquals($expected, CPatient::transformRelativesPatient($total));
    }

    /**
     * @param string $cp CP to test
     *
     * @config       dPpatients INSEE france 1
     * @config       dPpatients INSEE suisse 1
     * @config       dPpatients INSEE allemagne 1
     * @config       dPpatients INSEE espagne 1
     * @config       dPpatients INSEE portugal 1
     * @config       dPpatients INSEE gb 1
     *
     * @dataProvider cpProvider
     */
    public function testCPSize(string $cp): void
    {
        $cp_fields = ['cp', 'cp_naissance', 'assure_cp', 'assure_cp_naissance'];

        // Ugly way to empty the props cache due to change in configurations used in props
        CModelObject::$spec['CPatient'] = null;

        $patient = new CPatient();
        foreach ($cp_fields as $_cp) {
            $patient->{$_cp} = $cp;
        }
        $patient->repair();

        foreach ($cp_fields as $_cp) {
            $this->assertEquals($cp, $patient->{$_cp});
        }
    }

    /**
     * @return array
     */
    public function cpProvider(): array
    {
        return [
            ["3750-012"],
            ["12"],
            ["17000"],
            ["6534887"],
        ];
    }

    /**
     * Tests the rest age function
     *
     * @throws Exception
     */
    public function testGetRestAge(): void
    {
        $patient            = new CPatient();
        $patient->naissance = "2019-09-10";

        // < 1 year
        $value    = $patient->getRestAge(DateTimeImmutable::createFromFormat("Y-m-d", "2019-10-15"));
        $expected = ["rest_months" => 1, "rest_weeks" => 0, "rest_days" => 5, "locale" => "1 month"];
        $this->assertEquals($expected, $value);

        // < 2 year
        $value    = $patient->getRestAge(DateTimeImmutable::createFromFormat("Y-m-d", "2021-05-15"));
        $expected = ["rest_months" => 8, "rest_weeks" => 0, "rest_days" => 5, "locale" => "1 years, 8 months"];
        $this->assertEquals($expected, $value);

        // < 3 year
        $value    = $patient->getRestAge(DateTimeImmutable::createFromFormat("Y-m-d", "2025-02-03"));
        $expected = ["rest_months" => 4, "rest_weeks" => 3, "rest_days" => 3, "locale" => "5 years, 4 months"];
        $this->assertEquals($expected, $value);
    }

    /**
     * Test to verify that the date of birth is not greater than the current year.
     *
     * @throws Exception
     */
    public function testCheckBirthdate(): void
    {
        $patient                  = new CPatient();
        $patient->nom             = 'ACDC';
        $patient->nom_jeune_fille = "ACDC";
        $patient->prenom          = 'boba-fetta';
        $patient->prenom_usuel    = 'BOBAFETTA';
        $patient->prenoms         = 'BOBAFETTA BOBBY';
        $patient->naissance       = "2002-09-10";
        $msg                      = $patient->store();

        // Ok
        $this->assertNull($msg);

        // Ok
        $patient->naissance = CMbDT::date("- 1 WEEK");
        $msg                = $patient->store();
        $this->assertNull($msg);

        // Not ok
        $patient->naissance = CMbDT::date('+2 years', $patient->naissance);
        $msg                = $patient->store();
        $this->assertNotNull($msg);
    }

    /**
     * Test to verify if, when merging two patient, their INS NIR are equal.
     *
     * @throws Exception
     */
    public function testCheckMergeINS(): void
    {
        $patient1 = (new CPatientGenerator())->generate();
        $patient2 = (new CPatientGenerator())->generate();

        $patient_ins_nir                     = new CPatientINSNIR();
        $patient_ins_nir->oid                = CPatientINSNIR::OID_INS_NIR;
        $patient_ins_nir->created_datetime   = CMbDT::dateTime();
        $patient_ins_nir->last_update        = CMbDT::dateTime();
        $patient_ins_nir->provider           = 'INSi';
        $patient_ins_nir->source_identite_id = $patient1->source_identite_id;

        $patient_ins_nir->ins_nir    = '123456789123456';
        $patient_ins_nir->patient_id = $patient1->_id;
        $patient_ins_nir->store();

        $patient_ins_nir_not_equal                     = $patient_ins_nir;
        $patient_ins_nir_not_equal->_id                = null;
        $patient_ins_nir_not_equal->ins_nir            = '111111111111111';
        $patient_ins_nir_not_equal->patient_id         = $patient2->_id;
        $patient_ins_nir_not_equal->source_identite_id = $patient2->source_identite_id;
        $patient_ins_nir_not_equal->store();

        $patient_merge_service = new PatientMergeService([$patient1, $patient2]);
        $warnings              = $patient_merge_service->getWarnings();

        self::assertEquals(['CPatient-merge-warning-INS-conflict'], $warnings);
    }

    /**
     * Tests on searching for a patient by name
     * @dataProvider searchNameProvider
     */
    public function testSearchName(CPatient $patient, string $order, string $field_searched, string $field_value): void
    {
        $search_service = new PatientSearchService();
        $search_service->setOrder("$order ASC");
        $nom = $search_service->reformatResearchValue($field_value);
        $search_service->addCpFilter($patient->cp);
        $search_service->addSexFilter($patient->sexe);
        $search_service->addBirthFilter($patient->naissance);
        $field_searched === 'last_name' ?
            $search_service->addLastNameFilter($nom, null, $nom) :
            $search_service->addFirstNameFilter($nom, null);
        $search_service->queryPatients(false, false, null, null, null, null, null, null, null);
        self::assertTrue(in_array($patient->_id, array_column($search_service->getPatients(), '_id')));
    }

    public function searchNameProvider(): array
    {
        $patient                  = new CPatient();
        $patient->naissance       = CMbDT::date('-110 YEARS');
        $patient->nom             = 'IVDSBJVKBD';
        $patient->nom_jeune_fille = 'CDNOJBFQMK';
        $patient->prenom          = 'CCCAZE-SDSFDQ';
        $patient->prenom_usuel    = 'MLJKLNJK';
        $patient->prenoms         = 'EDIOZXN XWPOIWR';
        $patient->sexe            = 'f';
        $patient->cp              = '17000';
        $this->storeOrFailed($patient);

        return [
            'nom'             => [
                $patient,
                'nom',
                'last_name',
                'I\'D-BJVKBD',
            ],
            'nom_jeune_fille' => [
                $patient,
                'nom_jeune_fille',
                'last_name',
                'CDNOJBFQMK',
            ],
            'prenom'          => [
                $patient,
                'prenom',
                'first_name',
                'CCCAZE-SDSFDQ',
            ],
            'prenom_usuel'    => [
                $patient,
                'prenom_usuel',
                'first_name',
                'MLJKLNJK',
            ],
            'prenoms'         => [
                $patient,
                'prenoms',
                'first_name',
                'XWPOIWR',
            ],
        ];
    }

    /**
     * Testing the full age display under 1 year old
     */
    public function testDisplayFullAgePatientUnderOneYearOld(): void
    {
        $patient                  = new CPatient();
        $patient->naissance       = '2021-04-30';// 7 months old
        $patient->nom             = 'AAAAAAA';
        $patient->nom_jeune_fille = "BBBBBB";
        $patient->prenom          = 'CCCCC-CCCC';
        $patient->prenom_usuel    = 'DDDDDDD';
        $patient->prenoms         = 'EEEEEEE FFFFFFF';
        $patient->store();

        $full_age = $patient->getRestAge(new DateTimeImmutable('2021-11-30'));

        self::assertLessThanOrEqual(12, $full_age['rest_months']);
    }

    /**
     * Testing the full age display between 1 and 15 years old
     */
    public function testDisplayFullAgePatientBetweenOneAndFifteenYearsOld(): void
    {
        $patient                  = new CPatient();
        $patient->naissance       = '2020-04-30';// 1 year and 7 months old
        $patient->nom             = 'AAAAAAA';
        $patient->nom_jeune_fille = "BBBBBB";
        $patient->prenom          = 'CCCCC-CCCC';
        $patient->prenom_usuel    = 'DDDDDDD';
        $patient->prenoms         = 'EEEEEEE FFFFFFF';
        $patient->store();

        $full_age = $patient->getRestAge(new DateTimeImmutable('2021-11-30'));

        self::assertLessThanOrEqual(12, $full_age['rest_months']);
    }

    /**
     * @dataProvider alterTraitStrictProvider
     */
    public function testAlterTraitStrict(CPatient $patient, string $field, string $expected): void
    {
        $patient->$field = $expected;
        if ($msg = $patient->store()) {
            $this->assertNull($msg);
        } else {
            $this->assertEquals($expected, $patient->$field);
        }
    }

    public function alterTraitStrictProvider(): array
    {
        $patient = (new CPatientGenerator())->setForce(true)->generate();

        return [
            'nom'                     => [
                $patient,
                'nom',
                $patient->nom . 'AAA',
            ],
            'prenom'                  => [
                $patient,
                'prenom',
                strtoupper($patient->prenom) . 'AAA',
            ],
            'prenoms'                 => [
                $patient,
                'prenoms',
                strtoupper($patient->prenom) . 'AAA',
            ],
            'prenom_usuel'            => [
                $patient,
                'prenom_usuel',
                strtoupper($patient->prenom_usuel ?? '') . 'AAA',
            ],
            'naissance'               => [
                $patient,
                'naissance',
                '1970-01-01',
            ],
            'sexe'                    => [
                $patient,
                'sexe',
                ($patient->sexe === 'm') ? 'f' : 'm',
            ],
            'pays_naissance_insee'    => [
                $patient,
                'pays_naissance_insee',
                '724',
            ],
            'commune_naissance_insee' => [
                $patient,
                'commune_naissance_insee',
                '17274',
            ],
            'cp_naissance'            => [
                $patient,
                'cp_naissance',
                '17180',
            ],
        ];
    }

    /**
     * Test on the patient's membership of an establishment
     *
     * @config dPpatients CPatient function_distinct 2
     */
    public function testPatientGroup(): void
    {
        $patient                  = new CPatient();
        $patient->sexe            = 'f';
        $patient->nom             = 'tax';
        $patient->prenom          = 'tess';
        $patient->nom_jeune_fille = 'tax';
        $patient->naissance       = '1984-02-03';
        $patient->store();

        $group = CGroups::loadCurrent();
        $this->assertEquals($patient->group_id, $group->_id);
    }

    /**
     * Test on the patient's membership of an function
     *
     * @config dPpatients CPatient function_distinct 1
     */
    public function testPatientFunctions(): void
    {
        $patient                  = new CPatient();
        $patient->sexe            = 'f';
        $patient->nom             = 'tax';
        $patient->prenom          = 'tess';
        $patient->nom_jeune_fille = 'tax';
        $patient->naissance       = '1984-02-03';
        $patient->store();

        $function = CFunctions::getCurrent();
        $this->assertEquals($patient->function_id, $function->_id);
    }

    public function testAddLieuNassance(): void
    {
        $patient                          = reset(self::$patients_example);
        $patient->lieu_naissance          = 'La rochelle';
        $patient->commune_naissance_insee = 17300;
        $patient->store();

        $this->assertEquals(17000, $patient->cp_naissance);
    }

    public function testModifLieuNassance(): void
    {
        $patient                          = reset(self::$patients_example);
        $patient->lieu_naissance          = 'La rochelle';
        $patient->commune_naissance_insee = 17300;
        $patient->store();

        $patient->lieu_naissance          = 'Perigny';
        $patient->commune_naissance_insee = 17274;
        $patient->store();

        $this->assertEquals(17180, $patient->cp_naissance);
    }

    /**
     * @config       dPpatients CPatient function_distinct 0
     * @dataProvider getPhoningReturnArrayProvider
     * @throws Exception
     */
    public function testGetPhoningReturnArrayWithSeparationConfig(
        CPatient $patient,
        string $nom_soundex_expected,
        string $prenom_soundex_expected
    ): void {
        $actual = $patient->getPhoning(CMbDT::date());
        $this->assertIsArray($actual);

        foreach ($actual as $_patient) {
            $this->assertEquals($nom_soundex_expected, $_patient->nom_soundex2);
            $this->assertEquals($prenom_soundex_expected, $_patient->prenom_soundex2);
        }
    }

    /**
     * @config       dPpatients CPatient function_distinct 1
     * @dataProvider getPhoningReturnArrayProvider
     * @throws Exception
     */
    public function testGetPhoningReturnArrayWithIsCabinetConfig(
        CPatient $patient,
        string $nom_soundex_expected,
        string $prenom_soundex_expected
    ): void {
        $actual = $patient->getPhoning(CMbDT::date());
        $this->assertIsArray($actual);

        foreach ($actual as $_patient) {
            $this->assertEquals($nom_soundex_expected, $_patient->nom_soundex2);
            $this->assertEquals($prenom_soundex_expected, $_patient->prenom_soundex2);
        }
    }

    /**
     * @config       dPpatients CPatient function_distinct 2
     * @dataProvider getPhoningReturnArrayProvider
     * @throws Exception
     */
    public function testGetPhoningReturnArrayWithIsGroupConfig(
        CPatient $patient,
        string $nom_soundex_expected,
        string $prenom_soundex_expected
    ): void {
        $actual = $patient->getPhoning(CMbDT::date());
        $this->assertIsArray($actual);

        foreach ($actual as $_patient) {
            $this->assertEquals($nom_soundex_expected, $_patient->nom_soundex2);
            $this->assertEquals($prenom_soundex_expected, $_patient->prenom_soundex2);
        }
    }

    /**
     * @param CPatient $patient
     * @param string   $expected
     *
     * @throws TestsException
     * @throws ReflectionException
     * @dataProvider codeInseePrintableValueProvider
     */
    public function testGetCodeInseePrintableValueReturnStringValue(CPatient $patient, string $expected): void
    {
        $actual = $this->invokePrivateMethod($patient, "getCodeInseePrintableValue");
        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws CModelObjectException
     */
    public function codeInseePrintableValueProvider(): array
    {
        /** @var CPatient $patient */
        $patient              = CPatient::getSampleObject();
        $patient->_code_insee = "12345";

        /** @var CPatient $patient_2 */
        $patient_2              = CPatient::getSampleObject();
        $patient_2->_code_insee = null;

        /** @var CPatient $patient_3 */
        $patient_3              = CPatient::getSampleObject();
        $patient_3->_code_insee = "99999";

        return [
            "code 12345"   => [
                $patient,
                "CPatient-_code_insee-court : 12345",
            ],
            "code null" => [
                $patient_2,
                "CPatient-_code_insee-court : Unknown",
            ],
            "code 99999" => [
                $patient_3,
                "CPatient-_code_insee-court : Unknown",
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function getPhoningReturnArrayProvider(): array
    {
        /** @var CPatient $patient_1 */
        $patient_1              = CPatient::getSampleObject();
        $patient_1->nom         = "QRANZON";
        $patient_1->prenom      = "Xheros";
        $patient_1->group_id    = CMediusers::get()->loadRefFunction()->group_id;
        $patient_1->function_id = CFunctions::getCurrent()->_id;
        $patient_1->store();

        /** @var CSejour $sejour_1 */
        $sejour_1                = CSejour::getSampleObject();
        $sejour_1->patient_id    = $patient_1->_id;
        $sejour_1->group_id      = CMediusers::get()->loadRefFunction()->group_id;
        $sejour_1->entree        = CMbDT::dateTime("-2 DAYS");
        $sejour_1->sortie        = CMbDT::dateTime("+1 DAYS");
        $sejour_1->store();

        /** @var CPatient $patient_2 */
        $patient_2              = CPatient::getSampleObject();
        $patient_2->nom         = "QRINZYN";
        $patient_2->prenom      = "Xhyrus";
        $patient_2->group_id    = CMediusers::get()->loadRefFunction()->group_id;
        $patient_2->function_id = CFunctions::getCurrent()->_id;
        $patient_2->store();

        /** @var CSejour $sejour_2 */
        $sejour_2                = CSejour::getSampleObject();
        $sejour_2->patient_id    = $patient_2->_id;
        $sejour_2->group_id      = CMediusers::get()->loadRefFunction()->group_id;
        $sejour_2->entree        = CMbDT::dateTime("-1 DAYS");
        $sejour_2->sortie        = CMbDT::dateTime("+2 DAYS");
        $sejour_2->store();

        return [
            "$patient_1->nom" => [
                $patient_1,
                $patient_2->nom_soundex2,
                $patient_2->prenom_soundex2,
            ],
            "$patient_2->nom" => [
                $patient_2,
                $patient_1->nom_soundex2,
                $patient_1->prenom_soundex2,
            ],
        ];
    }
}
