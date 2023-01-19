<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Tests\Unit\Matcher;

use Ox\Core\Cache;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Import\GenericImport\Exception\GenericMatcherException;
use Ox\Import\GenericImport\Matcher\GenericMatcher;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\OxUnitTestCase;

/**
 * @runTestsInSeparateProcesses 
 */
class GenericMatcherTest extends OxUnitTestCase
{
    /** @var GenericMatcher */
    private $genericMatcher;

    public function setUp(): void
    {
        $cache = new Cache('CPatient.getTagIPP', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache->put("tag_ipp");
        $cache_sejour = new Cache('CSejour.getTagNDA', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache_sejour->put("tag_nda");
        $this->genericMatcher = new GenericMatcher();

        parent::setUp();
    }

    /**
     * @config eai use_domain 0
     * @config dPpatients CPatient tag_ipp tag_ipp
     * @config dPpatients CPatient function_distinct 0
     *
     * @throws GenericMatcherException|CModelObjectException
     */
    public function testMatchPatient(): void
    {
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        if ($msg = $patient->store()) {
            $this->fail($msg);
        }

        $patient_2            = new CPatient();
        $patient_2->nom       = $patient->nom;
        $patient_2->prenom    = $patient->prenom;
        $patient_2->naissance = $patient->naissance;

        $patient_after = $this->genericMatcher->matchPatient($patient_2);

        $this->assertEquals($patient->_id, $patient_after->_id);
    }

    /**
     * @config eai use_domain 0
     * @config dPpatients CPatient tag_ipp tag_ipp
     * @config dPpatients CPatient function_distinct 0
     *
     * @throws GenericMatcherException
     */
    public function testMatchPatientHasAlreadyAnIpp(): void
    {
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        if ($msg = $patient->store()) {
            $this->fail($msg);
        }

        $idsante400               = new CIdSante400();
        $idsante400->object_class = $patient->_class;
        $idsante400->object_id    = $patient->_id;
        $idsante400->tag          = "tag_ipp";
        $idsante400->id400        = uniqid();
        if ($msg = $idsante400->store()) {
            $this->fail($msg);
        }

        $patient2            = new CPatient();
        $patient2->nom       = $patient->nom;
        $patient2->prenom    = $patient->prenom;
        $patient2->naissance = $patient->naissance;
        $patient2->_IPP      = uniqid();

        $this->expectExceptionMessage(
            "GenericMatcherException-Error-Patient unabled to import ipp, patient has already an ipp"
        );
        $this->genericMatcher->matchPatient($patient2);
    }

    /**
     * @config eai use_domain 0
     * @config dPpatients CPatient tag_ipp tag_ipp
     * @config dPpatients CPatient function_distinct 0
     *
     * @throws GenericMatcherException
     */
    public function testMatchPatientFromIpp(): void
    {
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        if ($msg = $patient->store()) {
            $this->fail($msg);
        }

        $patient2       = new CPatient();
        $patient2->_IPP = $patient->_IPP;

        $patient_after = $this->genericMatcher->matchPatient($patient2);
        $patient_after->loadIPP();

        $this->assertEquals($patient->_IPP, $patient_after->_IPP);
    }

    /**
     * @config eai use_domain 0
     * @config dPplanningOp CSejour tag_dossier tag_nda
     *
     * @throws GenericMatcherException|CModelObjectException
     */
    public function testMatchSejour(): void
    {
        $sejour = $this->buildSejour();
        if ($msg = $sejour->store()) {
            $this->fail($msg);
        }

        $sejour_2             = new CSejour();
        $sejour_2->patient_id = $sejour->patient_id;
        $sejour_2->group_id   = $sejour->group_id;
        $sejour_2->entree     = $sejour->entree;

        $sejour_after = $this->genericMatcher->matchSejour($sejour_2);

        $this->assertEquals($sejour->_id, $sejour_after->_id);
    }

    /**
     * @config eai use_domain 0
     * @config dPplanningOp CSejour tag_dossier tag_nda
     *
     * @throws GenericMatcherException|CModelObjectException
     */
    public function testMatchSejourHasAlreadyAnNda(): void
    {
        $sejour = $this->buildSejour();
        if ($msg = $sejour->store()) {
            $this->fail($msg);
        }

        $idsante400               = new CIdSante400();
        $idsante400->object_class = $sejour->_class;
        $idsante400->object_id    = $sejour->_id;
        $idsante400->tag          = "tag_nda";
        $idsante400->id400        = uniqid();
        if ($msg = $idsante400->store()) {
            $this->fail($msg);
        }

        $sejour_2                = new CSejour();
        $sejour_2->patient_id    = $sejour->patient_id;
        $sejour_2->group_id      = $sejour->group_id;
        $sejour_2->entree_prevue = $sejour->entree_prevue;
        $sejour_2->_NDA          = uniqid();

        $this->expectExceptionMessage(
            "GenericMatcherException-Error-Sejour unabled to import NDA, sejour has already an NDA"
        );
        $this->genericMatcher->matchSejour($sejour_2);
    }

    /**
     * @config eai use_domain 0
     * @config dPplanningOp CSejour tag_dossier tag_nda
     *
     * @throws GenericMatcherException|CModelObjectException
     */
    public function testMatchSejourFromNda(): void
    {
        $sejour = $this->buildSejour();
        if ($msg = $sejour->store()) {
            $this->fail($msg);
        }

        $sejour_2       = new CSejour();
        $sejour_2->_NDA = $sejour->_NDA;

        $sejour_after = $this->genericMatcher->matchSejour($sejour_2);
        $sejour_after->loadNDA();

        $this->assertEquals($sejour->_NDA, $sejour_after->_NDA);
    }

    /**
     * @return CSejour
     * @throws CModelObjectException
     */
    private function buildSejour(): CSejour
    {
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        if ($msg = $patient->store()) {
            $this->fail($msg);
        }

        /** @var CSejour $sejour */
        $sejour                = CSejour::getSampleObject();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = (new CMediusersGenerator())->generate()->_id;
        $sejour->group_id      = CMediusers::get()->loadRefFunction()->group_id;
        $sejour->entree_prevue = $sejour->entree_reelle = $sejour->entree = CMbDT::dateTime("-1 hours");
        $sejour->sortie_prevue = $sejour->sortie_reelle = $sejour->sortie = CMbDT::dateTime("+2 hours");

        return $sejour;
    }
}
