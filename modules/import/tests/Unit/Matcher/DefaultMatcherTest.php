<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Matcher;

use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Import\Framework\Matcher\DefaultMatcher;
use Ox\Import\GenericImport\Tests\Fixtures\GenericImportFixtures;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class DefaultMatcherTest extends OxUnitTestCase
{
    /** @var DefaultMatcher */
    private $defaultMatcher;

    public function setUp(): void
    {
        $this->defaultMatcher = new DefaultMatcher();
    }

    private function generateUser(): CUser
    {
        return $this->getObjectFromFixturesReference(
            CUser::class,
            UsersFixtures::REF_USER_LOREM_IPSUM
        );
    }

    public function testMatchUser(): void
    {
        $user = $this->generateUser();

        $user_after = new CUser();
        $user_after->cloneFrom($user);

        $user_after = $this->defaultMatcher->matchUser($user_after);

        $this->assertEquals($user->_id, $user_after->_id);
    }

    public function testMatchUserNotMatch(): void
    {
        $user = $this->generateUser();

        $user_after = new CUser();
        $user_after->cloneFrom($user);
        $user_after->user_username = uniqid();

        $this->defaultMatcher->matchUser($user_after);

        $this->assertNull($user_after->_id);
    }

    protected function generateMedecin(): CMedecin
    {
        $medecin       = new CMedecin();
        $medecin->nom  = uniqid();
        $medecin->sexe = "m";

        return $medecin;
    }

    public function testMatchMedecinWithRpps(): void
    {
        $rpps = CMbString::createLuhn('1234567890');

        $medecin       = new CMedecin();
        $medecin->rpps = strval($rpps);
        $medecin->loadMatchingObjectEsc();

        if (!$medecin->_id) {
            $medecin       = $this->generateMedecin();
            $medecin->rpps = $rpps;
            $medecin->store();
        }

        $medecin_after = new CMedecin();
        $medecin_after->cloneFrom($medecin);

        $medecin_after = $this->defaultMatcher->matchMedecin($medecin_after);

        $this->assertEquals($medecin->_id, $medecin_after->_id);
    }

    public function testMatchMedecinWithAdeli(): void
    {
        $adeli = CMbString::createLuhn('12345678');

        $medecin        = new CMedecin();
        $medecin->adeli = strval($adeli);
        $medecin->loadMatchingObjectEsc();

        if (!$medecin->_id) {
            $medecin        = $this->generateMedecin();
            $medecin->adeli = $adeli;
            $medecin->store();
        }

        $medecin_after = new CMedecin();
        $medecin_after->cloneFrom($medecin);

        $medecin_after = $this->defaultMatcher->matchMedecin($medecin_after);

        $this->assertEquals($medecin->_id, $medecin_after->_id);
    }

    public function testMatchMedecinWithNameAndCp(): void
    {
        $medecin     = $this->generateMedecin();
        $medecin->cp = '17000';
        $medecin->store();

        $medecin_after = new CMedecin();
        $medecin_after->cloneFrom($medecin);

        $medecin_after = $this->defaultMatcher->matchMedecin($medecin_after);

        $this->assertEquals($medecin->_id, $medecin_after->_id);
    }

    public function testMatchMedecinWithNameAndCpPartial(): void
    {
        $medecin     = $this->generateMedecin();
        $medecin->cp = '17000';
        $medecin->store();

        $medecin_after = new CMedecin();
        $medecin_after->cloneFrom($medecin);
        $medecin_after->cp = '17500';

        $medecin_after = $this->defaultMatcher->matchMedecin($medecin_after);

        $this->assertEquals($medecin->_id, $medecin_after->_id);
    }

    public function testMatchMedecinNotMatch(): void
    {
        $medecin = $this->generateMedecin();
        $medecin->store();

        $medecin_after = new CMedecin();
        $medecin_after->cloneFrom($medecin);
        $medecin_after->nom = uniqid();

        $this->defaultMatcher->matchMedecin($medecin_after);

        $this->assertNull($medecin_after->_id);
    }

    protected function generatePlageConsult(): CPlageconsult
    {
        $user_chir                    = $this->getObjectFromFixturesReference(
            CMediusers::class,
            UsersFixtures::REF_USER_CHIR
        );

        $plage_consult                = new CPlageconsult();
        $plage_consult->chir_id       = $user_chir->_id;
        $plage_consult->date          = CMbDT::date('-5 YEAR');
        $plage_consult->loadMatchingObjectEsc();

        if (!$plage_consult->_id) {
            $plage_consult->debut         = CMbDT::time('-5 MIN');
            $plage_consult->fin           = CMbDT::time('+10 MIN');
            $plage_consult->freq          = CMbDT::time('00:15:00');
            $plage_consult->desistee      = 0;
            $plage_consult->remplacant_ok = 0;
            $plage_consult->color         = 'DDDDDD';
        }

        return $plage_consult;
    }

    public function testMatchPlageConsultNotMatch(): void
    {
        $plage_consult = $this->generatePlageConsult();
        $this->storeOrFailed($plage_consult);

        $plage_consult_after = new CPlageconsult();
        $plage_consult_after->load($plage_consult->_id);
        $plage_consult_after->_id     = null;
        $plage_consult_after->chir_id = null;
        $plage_consult_after->date    = CMbDT::date();

        $this->defaultMatcher->matchPlageConsult($plage_consult_after);

        $this->assertNull($plage_consult_after->_id);
    }

    public function testMatchPlageConsultMatchToDate(): void
    {
        $plage_consult = $this->generatePlageConsult();
        $this->storeOrFailed($plage_consult);

        $plage_consult_after = new CPlageconsult();
        $plage_consult_after->cloneFrom($plage_consult);

        $this->defaultMatcher->matchPlageConsult($plage_consult_after);

        $this->assertEquals($plage_consult->_id, $plage_consult_after->_id);
    }

    protected function generateConsultation(): CConsultation
    {
        return CConsultation::getSampleObject();
    }

    public function testMatchConsultationNotMatchWithOtherPatient(): void
    {
        $consultation = $this->generateConsultation();

        $consultation_after = new CConsultation();
        $consultation_after->cloneFrom($consultation);
        $consultation_after->patient_id = $this->getRandomId();

        $this->defaultMatcher->matchConsultation($consultation_after);

        $this->assertNull($consultation_after->_id);
    }

    public function testMatchConsultationNotMatchWithOtherPlageConsult(): void
    {
        $consultation = $this->generateConsultation();

        $consultation_after = new CConsultation();
        $consultation_after->cloneFrom($consultation);
        $consultation_after->plageconsult_id = $this->getRandomId();

        $this->defaultMatcher->matchConsultation($consultation_after);

        $this->assertNull($consultation_after->_id);
    }

    public function testMatchConsultationIfMatch(): void
    {
        $consultation = $this->generateConsultation();

        $consultation_after = new CConsultation();
        $consultation_after->cloneFrom($consultation);
        $this->defaultMatcher->matchConsultation($consultation_after);

        $this->assertEquals($consultation->_id, $consultation_after->_id);
    }

    protected function generateSejour(): CSejour
    {
        return $this->getObjectFromFixturesReference(CSejour::class, GenericImportFixtures::TAG_SEJOUR);
    }

    public function testSejourMatchNotMatchWithOtherPatient(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->patient_id = $this->getRandomId();

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNull($sejour_after->_id);
    }

    public function testSejourMatchNotMatchWithOtherGroup(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->group_id = $this->getRandomId();

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNull($sejour_after->_id);
    }

    public function testSejourMatchNotMatchWithOtherEntreeMoreOneDay(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->group_id = CMbDT::date('+10 DAYS');

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNull($sejour_after->_id);
    }

    public function testSejourMatchNotMatchWithOtherEntreeLessOneDay(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->group_id = CMbDT::date('-100000 DAYS');

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNull($sejour_after->_id);
    }

    public function testSejourMatchIfMatch(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNotNull($sejour_after->_id);
    }

    public function testSejourMatchIfMatchWithOneDayMore(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->entree_prevue = CMbDT::dateTime("+1 DAYS", $sejour->entree_prevue);

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNotNull($sejour_after->_id);
    }

    public function testSejourMatchIfMatchWithOneDayLess(): void
    {
        $sejour = $this->generateSejour();

        $sejour_after = new CSejour();
        $sejour_after->cloneFrom($sejour);
        $sejour_after->entree_prevue = CMbDT::dateTime("-1 DAY", $sejour->entree_prevue);

        $this->defaultMatcher->matchSejour($sejour_after);

        $this->assertNotNull($sejour_after->_id);
    }

    protected function generateAffectation(): CAffectation
    {
        return $this->getObjectFromFixturesReference(
            CAffectation::class,
            GenericImportFixtures::TAG_AFFECTATION
        );
    }

    public function testAffectationMatchIfMatch(): void
    {
        $affectation = $this->generateAffectation();

        $affectation_after            = new CAffectation();
        $affectation_after->sejour_id = $affectation->sejour_id;
        $affectation_after->entree    = $affectation->entree;
        $affectation_after->sortie    = $affectation->sortie;

        $this->defaultMatcher->matchAffectation($affectation_after);

        $this->assertEquals($affectation->_id, $affectation_after->_id);
    }

    public function testAffectationMatchWithDifferentSejourId(): void
    {
        $affectation = $this->generateAffectation();

        $affectation_after            = new CAffectation();
        $affectation_after->sejour_id = $affectation->sejour_id + 1;
        $affectation_after->entree    = $affectation->entree;
        $affectation_after->sortie    = $affectation->sortie;

        $this->defaultMatcher->matchAffectation($affectation_after);

        $this->assertNull($affectation_after->_id);
    }

    public function testAffectationMatchIfMatchWithOneDayLess(): void
    {
        $affectation = $this->generateAffectation();

        $affectation_after            = new CAffectation();
        $affectation_after->sejour_id = $affectation->sejour_id;
        $affectation_after->entree    = CMbDT::dateTime("-1 DAY", $affectation->entree);
        $affectation_after->sortie    = $affectation->sortie;

        $this->defaultMatcher->matchAffectation($affectation_after);

        $this->assertNull($affectation_after->_id);
    }

    protected function generateOperation(): COperation
    {
        return $this->getObjectFromFixturesReference(
            COperation::class,
            GenericImportFixtures::TAG_OPERATION
        );
    }

    public function testOperationMatchIfMatch(): void
    {
        $operation = $this->generateOperation();

        $operation_after                 = new COperation();
        $operation_after->sejour_id      = $operation->sejour_id;
        $operation_after->chir_id        = $operation->chir_id;
        $operation_after->date           = $operation->date;
        $operation_after->time_operation = $operation->time_operation;

        $this->defaultMatcher->matchOperation($operation_after);

        $this->assertEquals($operation->_id, $operation_after->_id);
    }

    public function testOperationMatchWithDifferentSejourId(): void
    {
        $operation = $this->generateOperation();

        $operation_after            = new COperation();
        $operation_after->sejour_id = $operation->sejour_id + rand(100, 9999);
        $operation_after->chir_id   = $operation->chir_id;
        $operation_after->date      = $operation->date;

        $this->defaultMatcher->matchOperation($operation_after);

        $this->assertNull($operation_after->_id);
    }

    public function testOperationMatchIfMatchWithOneDayLess(): void
    {
        $operation = $this->generateOperation();

        $operation_after            = new COperation();
        $operation_after->sejour_id = $operation->sejour_id;
        $operation_after->chir_id   = $operation->chir_id;
        $operation_after->date      = CMbDT::dateTime("-1 DAY", $operation->date);

        $this->defaultMatcher->matchOperation($operation_after);

        $this->assertNull($operation_after->_id);
    }

    private function getRandomId(): int
    {
        return rand(PHP_INT_MAX / 2, PHP_INT_MAX);
    }
}
