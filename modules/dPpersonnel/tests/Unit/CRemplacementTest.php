<?php

/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Personnel\CRemplacement;
use Ox\Mediboard\Personnel\Tests\Fixtures\CRemplacementFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CRemplacementTest extends OxUnitTestCase
{
    /**
     * Create affectation personnel object
     *
     * @return CRemplacement
     * @throws TestsException
     */
    public function testCreateRemplacement(): CRemplacement
    {
        /** @var CRemplacement $remplacement */
        $remplacement = $this->getObjectFromFixturesReference(
            CRemplacement::class,
            CRemplacementFixtures::TAG_REMPLACEMENT
        );

        $this->assertNotNull($remplacement->_id);
        $this->assertGreaterThanOrEqual($remplacement->debut, $remplacement->fin);

        return $remplacement;
    }

    /**
     * Test of the reference remplacant load
     *
     * @param CRemplacement $remplacement
     *
     * @depends testCreateRemplacement
     */
    public function testLoadRefRemplacant(CRemplacement $remplacement): void
    {
        $this->assertNull($remplacement->_ref_remplacant);
        $remplacement->loadRefRemplacant();
        $this->assertNotNull($remplacement->_ref_remplacant->_id);
        $this->assertEquals($remplacement->remplacant_id, $remplacement->_ref_remplacant->_id);
    }

    /**
     * Test to check
     *
     * @param CRemplacement $remplacement
     *
     * @depends testCreateRemplacement
     * @config [CConfiguration] personnel CRemplacement duree_max 5
     */
    public function testCheck(CRemplacement $remplacement): void
    {
        $remplacement->fin = CMbDT::date($remplacement->fin) . " " . CMbDT::time();
        $msg               = $remplacement->check();

        if (!empty($msg) && ($remplacement->debut <= $remplacement->fin)) {
            $this->assertNotEmpty($msg);
            $this->assertEquals("CRemplacement-depassement_duree_max 5", $msg);
        } elseif (empty($msg)) {
            $this->assertEmpty($msg);
        } else {
            $this->assertGreaterThanOrEqual($remplacement->fin, $remplacement->debut);
        }
    }

    /**
     * Test of the reference remplace load
     *
     * @param CRemplacement $remplacement
     *
     * @depends testCreateRemplacement
     */
    public function testLoadRefRemplace(CRemplacement $remplacement): void
    {
        $this->assertNull($remplacement->_ref_remplace);
        $remplacement->loadRefRemplace();
        $this->assertNotNull($remplacement->_ref_remplace->_id);
        $this->assertEquals($remplacement->remplace_id, $remplacement->_ref_remplace->_id);
    }

    /**
     * Test to update form field
     *
     * @param CRemplacement $remplacement
     *
     * @depends testCreateRemplacement
     */
    public function testUpdateFormFields(CRemplacement $remplacement): void
    {
        $remplacement->updateFormFields();

        $this->assertEquals($remplacement->_view, $remplacement->_shortview);
        $this->assertIsString($remplacement->_view);
    }
}
