<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Drc\CDRC;
use Ox\Mediboard\Cim10\Drc\CDRCConsultationResult;
use Ox\Mediboard\Cim10\Drc\CDRCCriterion;
use Ox\Mediboard\Cim10\Drc\CDRCCriticalDiagnosis;
use Ox\Mediboard\Cim10\Drc\CDRCResultClass;
use Ox\Mediboard\Cim10\Drc\CDRCSynonym;
use Ox\Mediboard\Cim10\Drc\CDRCTranscoding;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class CDRCConsultationResultTest extends OxUnitTestCase
{
    public function testConstructLoadNull(): void
    {
        $consultation_result = new CDRCConsultationResult(12);
        $this->assertInstanceOf(CDRCConsultationResult::class, $consultation_result);
        $this->assertEquals(12, $consultation_result->result_id);
    }


    public function testSearchByTitle(): void
    {
        $result_expected = [['result_id' => 325, 'title' => "PROCEDURE ADMINISTRATIVE"]];
        $result          = CDRCConsultationResult::search('ADMINISTRATIVE', null, null, null, true);
        $this->assertEquals($result_expected, $result);
    }

    public function testGet(): void
    {
        $expected = $this->CDRCConsultationResult534();

        $consultation_result = CDRCConsultationResult::get(534);

        $consultation_result->details = "";
        $this->assertInstanceOf(CDRCConsultationResult::class, $consultation_result);

        $this->assertEquals($expected, $consultation_result);
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testLoadResultClass(): void
    {
        $consultation_result             = new CDRCConsultationResult(20);
        $cdrc_result_expected            = new CDRCResultClass();
        $cdrc_result_expected->class_id  = 10;
        $cdrc_result_expected->chapter   = "(I00-I99)";
        $cdrc_result_expected->libelle   = "Cardio-vasculaire";
        $cdrc_result_expected->beginning = "I00";
        $cdrc_result_expected->end       = "I99";
        $cdrc_result_expected->text      = "maladies de l'appareil circulatoire";
        $this->invokePrivateMethod($consultation_result, 'loadResultClass');
        $this->assertEquals($cdrc_result_expected, $consultation_result->_class);
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testLoadCriticalDiagonses(): void
    {
        $consultation_result     = new CDRCConsultationResult(43);
        $diagnosis               = new CDRCCriticalDiagnosis();
        $diagnosis->libelle      = "Il n'y a aucun Diagnostic Critique pour ce Résultat de consultation";
        $diagnosis->diagnosis_id = 81;
        $diagnosis->criticality  = null;
        $diagnosis->group        = 0;
        $diagnosis_expected      = [$diagnosis];
        $this->invokePrivateMethod($consultation_result, 'loadCriticalDiagnoses');
        $this->assertEquals($diagnosis_expected, $consultation_result->_critical_diagnoses);
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testLoadSiblings(): void
    {
        $consultation_result           = new CDRCConsultationResult(20);
        $consultation_sibling_expected = [new CDRCConsultationResult(506, CDRC::LOAD_LITE)];
        $this->invokePrivateMethod($consultation_result, 'loadSiblings');
        $this->invokePrivateMethod($consultation_result->_siblings[0], 'formatDetails');
        $this->assertEquals($consultation_sibling_expected, $consultation_result->_siblings);
    }

    /**
     * @throws ReflectionException
     * @throws TestsException
     */
    public function testLoadTranscodings(): void
    {
        $expected_transcoding = $this->loadTranscodingResult20();
        $consultation_result  = new CDRCConsultationResult(20);
        $this->invokePrivateMethod($consultation_result, 'loadTranscodings');
        $this->assertEquals($expected_transcoding, $consultation_result->_transcodings);
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testLoadSynonyms(): void
    {
        $expected_synonym    = [new CDRCSynonym(12, CDRC::LOAD_LITE)];
        $consultation_result = new CDRCConsultationResult(6);
        $this->invokePrivateMethod($consultation_result, 'loadSynonyms');
        $this->assertEquals($expected_synonym, $consultation_result->_synonyms);
    }

    public function testLoadCriteria(): void
    {
        $expected_criterion  = $this->loadCritetionResult103();
        $consultation_result = new CDRCConsultationResult(103);
        $this->invokePrivateMethod($consultation_result, 'loadCriteria');
        $this->assertEquals($expected_criterion, $consultation_result->_criteria);
    }/*
    public function testSearch(): void{
    todo
    }*/

    public function CDRCConsultationResult534(): CDRCConsultationResult
    {
        $expected                      = new CDRCConsultationResult(534);
        $expected->title               = "COLIQUE NEPHRETIQUE";
        $expected->nature              = "1";
        $expected->sex                 = 3;
        $expected->episode_type        = "A";
        $expected->version             = 2;
        $expected->state               = 1;
        $expected->symptom             = 0;
        $expected->syndrome            = 1;
        $expected->disease             = 0;
        $expected->certified_diagnosis = 0;
        $expected->unpathological      = 0;
        $expected->details             = "";
        $expected->dur_prob_epis       = 0;
        $expected->age_min             = 18;
        $expected->age_max             = 78;
        $expected->cim10_code          = "N23";
        $expected->cisp_code           = "U14";

        return $expected;
    }

    public function loadCritetionResult103(): array
    {
        $expected_criterion = [];
        $id_criterion       = [1416, 1417, 1418, 1419, 8148, 8149];
        $order              = 1;
        foreach ($id_criterion as $id) {
            $expected_criterion[$order] = new CDRCCriterion($id, CDRC::LOAD_FULL);
            $order++;
        }

        return $expected_criterion;
    }

    public function loadTranscodingResult20(): array
    {
        $expected_transcoding = [];
        $id_transcoding       = [694, 695, 696, 725];

        foreach ($id_transcoding as $id) {
            $transcoding                                    = new CDRCTranscoding($id, CDRC::LOAD_FULL);
            $expected_transcoding[$transcoding->code_cim_1] = $transcoding;
        }

        return $expected_transcoding;
    }
}
