<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Exception;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class CPatientINSNIRTest extends OxUnitTestCase
{
    public function testCreateDatamatrixWithoutData(): void
    {
        $patient = new CPatient();

        $patient_ins               = new CPatientINSNIR();
        $patient_ins->_ref_patient = $patient;

        $this->assertNull($patient_ins->createDataForDatamatrix());
    }

    /**
     * @config ref_pays 1
     *
     * @return void
     */
    public function testCreateDatamatrix(): void
    {
        $patient                          = new CPatient();
        $patient->prenoms                 = "test1 Test2 TEST";
        $patient->nom_jeune_fille         = "dataMatrixTeSt";
        $patient->sexe                    = "m";
        $patient->naissance               = "2021-08-31";
        $patient->pays_naissance_insee    = "250";
        $patient->commune_naissance_insee = "17000";

        $patient_ins               = new CPatientINSNIR();
        $patient_ins->_ref_patient = $patient;
        $patient_ins->ins_nir      = "123549685274612";
        $patient_ins->oid          = "25978456325481569525";

        $expected = "IS010000000000000000000000S1123549685274612S225978456325481569525S3TEST1 TEST2 TEST\x1DS4DATAMATRIXTEST\x1DS5MS631-08-2021S717000";
        $this->assertEquals($expected, $patient_ins->createDataForDatamatrix());
    }

    /**
     * @config ref_pays 1
     *
     * @return void
     */
    public function testCreateDatamatrixForeignCountry(): void
    {
        $patient                       = new CPatient();
        $patient->prenoms              = "test1 Test2 TEST";
        $patient->nom_jeune_fille      = "dataMatrixTeSt";
        $patient->sexe                 = "m";
        $patient->naissance            = "2021-08-31";
        $patient->pays_naissance_insee = "268";

        $patient_ins               = new CPatientINSNIR();
        $patient_ins->_ref_patient = $patient;
        $patient_ins->ins_nir      = "123549685274612";
        $patient_ins->oid          = "2597845632548156952";

        $expected = "IS010000000000000000000000S1123549685274612S22597845632548156952\x1DS3TEST1 TEST2 TEST\x1DS4DATAMATRIXTEST\x1DS5MS631-08-2021S799255";
        $this->assertEquals($expected, $patient_ins->createDataForDatamatrix());
    }

    public function testSearchDatamatrixINS(): void
    {
        $patient_ins = new CPatientINSNIR();

        $data = [
            "matricule"      => "123549685274612",
            "oid"            => "2597845632548156952",
            "prenoms"        => "TEST1 TEST2 TEST",
            "nom"            => "TEST",
            "sexe"           => "F",
            "date_naissance" => "1990-06-25",
            'jour'           => '25',
            'mois'           => '06',
            'annee'          => '1990',
        ];

        $this->assertEquals(
            $data,
            $patient_ins->readDatamatrixINS(
                "IS010000000000000000000000S1123549685274612S22597845632548156952<GS>S3TEST1 TEST2 TEST<GS>S4TEST<GS>S5FS625-06-1990"
            )
        );
    }

    public function testSearchDatamatrixINSPrenomEnd(): void
    {
        $patient_ins = new CPatientINSNIR();
        $data        = [
            "matricule"      => "123549685274612",
            "oid"            => "2597845632548156952",
            "prenoms"        => "TEST1 TEST2 TEST",
            "nom"            => "TEST",
            "sexe"           => "F",
            "date_naissance" => "1990-06-25",
            'jour'           => '25',
            'mois'           => '06',
            'annee'          => '1990',

        ];

        $this->assertEquals(
            $data,
            $patient_ins->readDatamatrixINS(
                "IS010000000000000000000000S1123549685274612S22597845632548156952<GS>S4TEST<GS>S5FS625-06-1990S3TEST1 TEST2 TEST"
            )
        );
    }

    public function testSearchDatamatrixINSWithPaysINSEE(): void
    {
        $patient_ins = new CPatientINSNIR();

        $data = [
            "matricule"                => "254698753216548",
            "oid"                      => "5698751236548956215",
            "prenoms"                  => "TEST1 TEST2 TEST",
            "nom"                      => "TEST",
            "sexe"                     => "F",
            "date_naissance"           => "1990-06-25",
            "pays_naissance_insee"     => "268",
            'jour'                     => '25',
            'mois'                     => '06',
            'annee'                    => '1990',
            'code_insee'               => '99255',
            'nom_pays_naissance_insee' => 'Georgie',
        ];

        $this->assertEquals(
            $data,
            $patient_ins->readDatamatrixINS(
                "IS010000000000000000000000S1254698753216548S25698751236548956215<GS>S3TEST1 TEST2 TEST<GS>S4TEST<GS>S5FS625-06-1990S799255"
            )
        );
    }

    public function testSearchDatamatrixINSWithCommune(): void
    {
        $patient_ins = new CPatientINSNIR();

        $data = [
            "matricule"                => "254698753216548",
            "oid"                      => "5698751236548956215",
            "prenoms"                  => "TEST1 TEST2 TEST",
            "nom"                      => "TEST",
            "sexe"                     => "F",
            "date_naissance"           => "1990-06-25",
            "commune_naissance_insee"  => "14118",
            'jour'                     => '25',
            'mois'                     => '06',
            'annee'                    => '1990',
            'lieu_naissance'           => 'Caen',
            'cp_naissance'             => '14000',
            'pays_naissance_insee'     => '250',
            'nom_pays_naissance_insee' => 'France',
        ];

        $this->assertEquals(
            $data,
            $patient_ins->readDatamatrixINS(
                "IS010000000000000000000000S1254698753216548S25698751236548956215<GS>S3TEST1 TEST2 TEST<GS>S4TEST<GS>S5FS625-06-1990S714118"
            )
        );
    }

    /**
     * @param CPatientINSNIR $insnir
     * @param string         $expected
     *
     * @throws TestsException
     * @throws ReflectionException
     * @dataProvider getINSTypeProvider
     */
    public function testGetINSType(CPatientINSNIR $insnir, string $expected): void
    {
        $actual = $this->invokePrivateMethod($insnir, "getINSType");

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function getINSTypeProvider(): array
    {
        $patient = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        $insnir1             = new CPatientINSNIR();
        $insnir1->oid        = CPatientINSNIR::OID_INS_NIA;
        $insnir1->patient_id = $patient->_id;
        $insnir1->updateFormFields();

        $insnir2             = new CPatientINSNIR();
        $insnir2->oid        = CPatientINSNIR::OID_INS_NIR;
        $insnir2->patient_id = $patient->_id;
        $insnir2->updateFormFields();

        return [
            "return 'NIA'" => [$insnir1, "CPatientINSNIR-_ins_type.nia"],
            "return 'NIR'" => [$insnir2, "CPatientINSNIR-_ins_type.nir"],
        ];
    }

    /**
     * @config [CConfiguration] dPpatients CPatient check_code_insee 0
     */
    public function testRemoveINSTemporaire(): void
    {
        $patient = (new CPatientGenerator())->setForce(true)->generate();

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_patient) {
            $patient->{'_source_' . $_field_patient} = $patient->$_field_patient;
        }

        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $patient->_ins            = '123456789123456';
        $patient->_ins_temporaire = true;
        $patient->_oid            = CPatientINSNIR::OID_INS_NIR;
        $patient->store();

        /** @var CPatientINSNIR $patient_ins_nir */
        $patient_ins_nir                 = $patient->loadRefPatientINSNIR();
        $patient_ins_nir->ins_temporaire = 0;
        $patient_ins_nir->store();

        $patient->load($patient->_id);

        $this->assertEquals('RECUP', $patient->status);
    }
}
