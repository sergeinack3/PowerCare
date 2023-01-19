<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Test;

use Exception;
use Ox\Core\CMbException;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CCategorieGroupePatientTest extends OxUnitTestCase
{
    /**
     * Test to create patient group category object
     *
     * @return CCategorieGroupePatient
     * @throws TestsException
     * @throws Exception
     */
    public function testCreateCategorieGroupePatient(): CCategorieGroupePatient
    {
        $categorie_groupe = CCategorieGroupePatient::getSampleObject();
        $categorie_groupe->group_id = CGroups::loadCurrent()->_id;
        $msg = $categorie_groupe->store();

        if ($msg) {
            $this->fail($msg);
        }

        $this->assertInstanceOf(CCategorieGroupePatient::class, $categorie_groupe);
        $this->assertNotNull($categorie_groupe->_id);

        return $categorie_groupe;
    }

    /**
     * Test to load the group ranges
     * @throws CModelObjectException
     * @throws Exception
     */
    public function testLoadRefPlagesGroupe(): void
    {
        $category_groupe = CCategorieGroupePatient::getSampleObject();
        $category_groupe->group_id = CGroups::loadCurrent()->_id;
        if ($msg = $category_groupe->store()) {
            throw new CMbException($msg);
        }

        /** @var CPlageGroupePatient $plage */
        $plage_groupe = CPlageGroupePatient::getSampleObject();
        $plage_groupe->categorie_groupe_patient_id = $category_groupe->_id;
        if ($msg = $plage_groupe->store()) {
            throw new CMbException($msg);
        }

        $plages = $category_groupe->loadRefPlagesGroupe();

        $this->assertInstanceOf(CPlageGroupePatient::class, reset($plages));
        $this->assertEquals($category_groupe->_id, reset($plages)->categorie_groupe_patient_id);
    }
}
