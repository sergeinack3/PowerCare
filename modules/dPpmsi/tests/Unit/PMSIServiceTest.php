<?php

/**
 * @package Mediboard\Ameli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Unit;

use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Pmsi\PMSIService;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Mediboard\Populate\Generators\CSejourGenerator;
use Ox\Tests\OxUnitTestCase;

class PMSIServiceTest extends OxUnitTestCase
{
    /**
     *
     * @return void
     * @throws CMbModelNotFoundException
     */
    public function testGetStayDossierPMSI(): void
    {
        $patient = (new CPatientGenerator())->generate();

        $sejour = (new CSejourGenerator())->generate();
        $sejour->patient_id = $patient->_id;
        $sejour->store();

        $stay_dossier_pmsi = (new PMSIService())->getStayDossierPMSI($patient->_id, $sejour->_id, false);

        $this->assertNotEmpty($stay_dossier_pmsi["patient"]);
        $this->assertNotEmpty($stay_dossier_pmsi["sejour"]);
    }

    /**
     * @return void
     * @throws CMbModelNotFoundException
     */
//    public function testGetMotherStayDossier(): void
//    {
//        // En attente des fixtures pour corriger ce test
//        $this->markTestSkipped('Wait Fixture');
//
//        $patient_grossesse = (new CGrossesseGenerator())->generate();
//
//        $sejour = (new CSejourGenerator())->generate();
//        $sejour->patient_id = $patient_grossesse->parturiente_id;
//        $sejour->grossesse_id = $patient_grossesse->grossesse_id;
//        $sejour->store();
//
//        $stay_dossier_pmsi = (new PMSIService())->getStayDossierPMSI($patient_grossesse->parturiente_id, $sejour->_id, false);
//
//        $this->assertNotEmpty($stay_dossier_pmsi["patient"]);
//        $this->assertNotEmpty($stay_dossier_pmsi["sejour"]);
//        $this->assertEquals($stay_dossier_pmsi["sejour"]->_ref_grossesse->_id, $patient_grossesse->grossesse_id);
//    }

    /**
     * @return void
     * @throws CMbModelNotFoundException
     */
//    public function testGetMaternityStayDossier(): void
//    {
//        // En attente des fixtures pour corriger ce test
//        $this->markTestSkipped('Wait Fixture');
//
//        $naissance = (new CNaissanceGenerator())->generate();
//
//        $stay_dossier_pmsi = (new PMSIService())->getStayDossierPMSI($naissance->_ref_sejour_enfant->patient_id, $naissance->_ref_sejour_enfant->_id, false);
//
//        $this->assertNotEmpty($stay_dossier_pmsi["patient"]);
//        $this->assertNotEmpty($stay_dossier_pmsi["sejour"]);
//        $this->assertNotEmpty($stay_dossier_pmsi["sejour_maman"]);
//    }
}
