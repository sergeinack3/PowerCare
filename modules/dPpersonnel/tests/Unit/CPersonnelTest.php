<?php

/**
 * @package Mediboard\\${Module}
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Tests\Unit;

use Exception;
use Ox\Core\CMbException;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\Personnel\Tests\Fixtures\AffectationPersonnelFixtures;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Tests\OxUnitTestCase;

class CPersonnelTest extends OxUnitTestCase
{
    /**
     * Create personnel object
     *
     * @return CPersonnel
     * @throws Exception
     */
    public function testCreatePersonnel(): CPersonnel
    {
        $personnel = $this->getObjectFromFixturesReference(
            CPersonnel::class,
            AffectationPersonnelFixtures::TAG_PERSONNEL
        );

        $this->assertNotNull($personnel->_id);

        return $personnel;
    }

    /**
     * Test of update form field
     */
    public function testUpdateFormFields(): void
    {
        $personnel = $this->getObjectFromFixturesReference(
            CPersonnel::class,
            AffectationPersonnelFixtures::TAG_PERSONNEL
        );

        $personnel->updateFormFields();

        $this->assertNotNull($personnel->_view);
    }

    /**
     * Test of load list personnel
     */
    public function testLoadListPers(): void
    {
        $list_nurse_sspi = CPersonnel::loadListPers("reveil");

        $this->assertArrayNotHasKey("personnel_id", $list_nurse_sspi);
    }

    /**
     * Test of load list emplacement
     *
     * @depends testCreatePersonnel
     */
    public function testLoadListEmplacement(CPersonnel $personnel): void
    {
        $list_emplacements = $personnel->loadListEmplacement();

        $this->assertNotNull($list_emplacements);
    }

    /**
     * Test of mass load list emplacement
     */
    public function testMassLoadListEmplacement(): void
    {
        $mediusers = (new CMediusersGenerator())->generate();
        $personnels = [];
        for ($i = 0; $i < 5; $i++) {
            /** @var CPersonnel $personnel */
            $personnel = CPersonnel::getSampleObject();
            $personnel->user_id = $mediusers->_id;
            $personnel->emplacement = CPersonnel::$_types[$i];
            if ($msg = $personnel->store()) {
                throw new CMbException($msg);
            }

            $personnels[] = $personnel;
        }

        CPersonnel::massLoadListEmplacement($personnels);

        foreach ($personnels as $_personnel) {
            $_personnel->loadListEmplacement();
            $this->assertNotNull($_personnel->_emplacements);
        }
    }
}
