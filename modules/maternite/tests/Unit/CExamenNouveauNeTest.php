<?php

/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Maternite\CExamenNouveauNe;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CExamenNouveauNeTest extends OxUnitTestCase
{
    /**
     * @throws TestsException
     */
    public function testLoadRefGrossesse(): void
    {
        $grossesse = new CGrossesse();
        $grossesse->_id = '1';

        $examen = new CExamenNouveauNe();
        $examen->grossesse_id = '1';
        $examen->_fwd['grossesse_id'] = $grossesse;

        $this->assertIsString($examen->grossesse_id);

        $grossesse = $examen->loadRefGrossesse();
        $this->assertEquals($grossesse->_id, $examen->grossesse_id);
    }

    /**
     * @throws TestsException
     */
    public function testLoadRefNaissance(): void
    {
        $naissance = new CNaissance();
        $naissance->sejour_maman_id = '1';
        $naissance->sejour_enfant_id = '2';
        $naissance->_id = '3';

        $examen = new CExamenNouveauNe();
        $examen->naissance_id = '3';
        $examen->_fwd['naissance_id'] = $naissance;

        $this->assertIsString($examen->naissance_id);

        $naissance = $examen->loadRefNaissance();
        $this->assertEquals($naissance->_id, $examen->naissance_id);
        $this->assertNotEmpty($naissance->sejour_maman_id);
        $this->assertNotEmpty($naissance->sejour_enfant_id);
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testLoadRefGuthrieUser(): void
    {
        $mediuser = (new CMediusersGenerator())->generate();

        $examen = new CExamenNouveauNe();
        $examen->guthrie_user_id = $mediuser->_id;
        $examen->_fwd['guthrie_user_id'] = $mediuser;


        $guthrie_user = $examen->loadRefGuthrieUser();

        if ($examen->guthrie_user_id) {
            $this->assertEquals($guthrie_user->_id, $examen->guthrie_user_id);
        } else {
            $this->assertNull($guthrie_user->_id);
        }
    }

    /**
     * @throws TestsException
     */
//    public function testCheckGuthrieExam(): void
//    {
//        $this->markTestSkipped("Voir avec Valentin pour config élément de prescription");
//    }

    /**
     * @throws TestsException
     */
//    public function testGetOEAExam(): void
//    {
//        $this->markTestSkipped("Voir avec Valentin pour config élément de prescription");
//    }

    /**
     * @throws TestsException
     */
    public function testGetJours(): void
    {
        $naissance = new CNaissance();
        $naissance->date_time = CMbDT::date('-10 DAYS');
        $naissance->_id = '1';

        $examen = new CExamenNouveauNe();
        $examen->date = CMbDT::date();
        $examen->naissance_id = '1';
        $examen->_fwd['naissance_id'] = $naissance;

        $this->assertEmpty($examen->_jours);

        $examen->getJours();

        $this->assertEquals(11, $examen->_jours);
    }
}
