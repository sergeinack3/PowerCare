<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Tests\OxUnitTestCase;

class CSourceIdentiteTest extends OxUnitTestCase
{
    /**
     * @throws \Ox\Tests\TestsException
     */
    public function testEvolutionNIAToNIR(): void
    {
        $patient = (new CPatientGenerator())->generate();

        // Source d'identité INS NIA
        $source_identite             = new CSourceIdentite();
        $source_identite->patient_id = $patient->_id;

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_source => $_field_patient) {
            $source_identite->$_field_source = $patient->$_field_patient;
        }

        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;

        $source_identite->mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $source_identite->active         = '1';
        $source_identite->_oid           = CPatientINSNIR::OID_INS_NIA;
        $source_identite->_ins           = '123456789';
        $source_identite->_ins_type      = 'NIA';
        $source_identite->store();

        $patient->source_identite_id = $source_identite->_id;

        // Création de la source d'identité INS NIR
        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_patient) {
            $patient->{'_source_' . $_field_patient} = $patient->$_field_patient;
        }

        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $patient->_oid            = CPatientINSNIR::OID_INS_NIR;
        $patient->_ins            = '012345678';

        $source_identite_id = $patient->source_identite_id;

        CSourceIdentite::manageSource($patient, $patient->_mode_obtention);

        $this->assertNotEquals($patient->source_identite_id, $source_identite_id);
    }

    /**
     * @throws \Ox\Tests\TestsException
     */
    public function testDuplicateINSNIR(): void
    {
        $patient = (new CPatientGenerator())->generate();

        $source_identite             = new CSourceIdentite();
        $source_identite->patient_id = $patient->_id;

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_source => $_field_patient) {
            $source_identite->$_field_source = $patient->$_field_patient;
        }

        $source_identite->mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $source_identite->active         = '1';
        $source_identite->store();

        $patient_ins_nir                     = new CPatientINSNIR();
        $patient_ins_nir->ins_nir            = '123456789123456';
        $patient_ins_nir->oid                = CPatientINSNIR::OID_INS_NIR;
        $patient_ins_nir->created_datetime   = CMbDT::dateTime();
        $patient_ins_nir->last_update        = CMbDT::dateTime();
        $patient_ins_nir->patient_id         = $patient->_id;
        $patient_ins_nir->provider           = 'INSi';
        $patient_ins_nir->source_identite_id = $source_identite->_id;
        $patient_ins_nir->store();

        $source_identite_duplicate             = $source_identite;
        $source_identite_duplicate->_id        = null;
        $source_identite_duplicate->patient_id = $patient->_id;
        $source_identite_duplicate->_oid       = CPatientINSNIR::OID_INS_NIR;
        $source_identite_duplicate->_ins       = '123456789123456';

        $this->assertEquals(
            'CSourceIdentite-Cannot create duplicate source with same ins and oid',
            $source_identite->store()
        );
    }

    public function testAddSourceJustifWithValidation(): void
    {

        $patient = (new CPatientGenerator())->setForce(true)->generate();

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_patient) {
            $patient->{'_source_' . $_field_patient} = $patient->$_field_patient;
        }

        $patient->_mode_obtention            = CSourceIdentite::MODE_OBTENTION_MANUEL;
        $patient->_identity_proof_type_id    = 1;
        $patient->_source__validate_identity = 1;

        $root_path = CAppUI::conf('root_dir');

        copy($root_path . '/images/pictures/logo.jpg', $root_path . '/tmp/identite.jpg');

        $_FILES['formfile']['type'][0]     = 'image/jpeg';
        $_FILES['formfile']['name'][0]     = 'identite.jpg';
        $_FILES['formfile']['tmp_name'][0] = $root_path . '/tmp/identite.jpg';

        $msg = CSourceIdentite::manageSource($patient, $patient->_mode_obtention);

        if ($msg === 'CFile-Error-Unable to encrypt file') {
            $this->markTestSkipped('Unable to complete test because of lack of encrypt key');
        }

        $this->assertEquals('VALI', $patient->status);
    }

    public function testAddSourceJustifWithoutValidation(): void
    {

        $patient = (new CPatientGenerator())->setForce(true)->generate();

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_patient) {
            $patient->{'_source_' . $_field_patient} = $patient->$_field_patient;
        }

        $patient->_mode_obtention            = CSourceIdentite::MODE_OBTENTION_MANUEL;
        $patient->_identity_proof_type_id    = 1;
        $patient->_source__validate_identity = 0;

        $root_path = CAppUI::conf('root_dir');

        copy($root_path . '/images/pictures/logo.jpg', $root_path . '/tmp/identite.jpg');

        $_FILES['formfile']['type'][0]     = 'image/jpeg';
        $_FILES['formfile']['name'][0]     = 'identite.jpg';
        $_FILES['formfile']['tmp_name'][0] = $root_path . '/tmp/identite.jpg';

        CSourceIdentite::manageSource($patient, $patient->_mode_obtention);

        $this->assertEquals('PROV', $patient->status);
    }

    public function testAddSourceINSITemporaire(): void
    {
        $patient = (new CPatientGenerator())->setForce(true)->generate();

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_field_patient) {
            $patient->{'_source_' . $_field_patient} = $patient->$_field_patient;
        }

        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $patient->_ins            = '123456789123456';
        $patient->_ins_temporaire = true;
        $patient->_oid            = CPatientINSNIR::OID_INS_NIR;

        CSourceIdentite::manageSource($patient, $patient->_mode_obtention);

        $this->assertEquals('PROV', $patient->status);
    }
}
