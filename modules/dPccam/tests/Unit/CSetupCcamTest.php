<?php
/**
 * @package Mediboard\Ccam\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Mediboard\Ccam\CSetupCcam;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CSetupCcamTest extends OxUnitTestCase {
  /**
   * From an array of versions, make a request which will be used to check
   * the version of the CCAM
   *
   * @throws TestsException
   */
  public function testMakeLastCcamBVersionRequest(): void {
    $versions = [
      63 => [
        [
          'table_name' => 'p_acte',
          'filters'    => [
            'code' => "= 'CCCCAA'",
          ],
        ],
      ],
      64 => [
        [
          'table_name' => 'p_acte',
          'filters'    => [
            'CODE' => "= 'ABABAB'",
          ],
          'ljoin' => [
            'acte2 ON p_acte.id = acte2.id'
          ]
        ]
      ],
    ];

    $actual = $this->invokePrivateMethod(new CSetupCcam(), 'makeLastCcamDBVersionRequest', $versions);

    $this->assertEquals(
      "-- CCAM v64 --\nSELECT *\nFROM `p_acte`\nLEFT JOIN acte2 ON p_acte.id = acte2.id\nWHERE (`CODE` = 'ABABAB')",
      $actual
    );
  }

  /**
   * The method expects an array as a parameter
   *
   * @throws TestsException
   */
  public function testBaldyFormArrayForVersionRequest(): void {
    $versions = [
      64 => [
        'table_name' => 'p_acte',
        'filters'    => [
          'CODE' => "= 'ABABAB'",
        ],
      ],
    ];

    $this->expectException(CMbException::class);

    $this->invokePrivateMethod(new CSetupCcam(), 'makeLastCcamDBVersionRequest', $versions);
  }
}
