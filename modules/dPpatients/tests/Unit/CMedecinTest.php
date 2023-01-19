<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMedecinTest extends OxUnitTestCase
{
    /**
     * @param string $cp CP to test
     *
     * @config dPpatients INSEE france 1
     * @config dPpatients INSEE suisse 1
     * @config dPpatients INSEE allemagne 1
     * @config dPpatients INSEE espagne 1
     * @config dPpatients INSEE portugal 1
     * @config dPpatients INSEE gb 1
     *
     * @dataProvider cpProvider
     */
    public function testCPSize($cp)
    {
        $cp_fields = ['cp'];

        $medecin = new CMedecin();
        foreach ($cp_fields as $_cp) {
            $medecin->{$_cp} = $cp;
        }

        $medecin->repair();

        foreach ($cp_fields as $_cp) {
            $this->assertEquals($cp,
                $medecin->{$_cp});
        }
    }

    public function cpProvider()
    {
        return array(
            ["3750-012"], ["12"], ["17000"], ["6534887"]
        );
    }

    public function providerIsFictifRPPS(): array
    {
        return [
            "Fictif RPPS ok" => [
                'rpps'     => '22222000009',
                'expected' => 'true'
            ],
            "Fictif RPPS ok 2" => [
                'rpps'     => '99999000001',
                'expected' => 'true'
            ],
            "Fictif RPPS ko format" => [
                'rpps'     => '22220000009',
                'expected' => 'false'
            ],
            "Fictif RPPS ko length" => [
                'rpps'     => '2222200000',
                'expected' => 'false'
            ],
        ];
    }

    /**
     * @dataProvider providerIsFictifRPPS
     *
     * @param string $rpps
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testIsFictifRPPS(string $rpps, string $expected) {
        $this->assertEquals(CMedecin::isFictifRPPS($rpps), $expected === 'true'? true : false);
    }
}
