<?php

/**
 * PAM - ITI-30 - Tests
 *
 * @category IHE
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org
 */

namespace Ox\Interop\Ihe\Tests;

use Exception;
use Ox\Core\CMbException;
use Ox\Interop\Connectathon\CCnStep;
use Ox\Interop\Connectathon\Tests\Unit\ITI30Test;

/**
 * Class CITI30Test
 * PAM - ITI-30 - Tests
 */
class CITI30Test extends CIHETestCase
{
    /**
     * Test A24 - Link the two patients
     *
     * @return void
     * @throws CMbException
     */
    public static function testA24(): void
    {
        ITI30Test::testStep50();
    }

    /**
     * Test A28 - Create patient with full demographic data
     *
     * @return void
     * @throws CMbException
     */
    public static function testA28(CCnStep $step): void
    {
        if ($step->number == 10) {
            ITI30Test::testStep10();
        } else {
            ITI30Test::testStep40();
        }
    }

    /**
     * Test A31 - Update patient demographics
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA31(): void
    {
        ITI30Test::testStep20();
    }

    /**
     * Test A37 - Unlink the two previously linked patients
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function testA37(CCnStep $step): void
    {
        ITI30Test::testStep60();
    }

    /**
     * Test A47 - Changes one of the identifiers
     *
     *
     * @return void
     * @throws CMbException|Exception
     *
     */
    public static function testA47(): void
    {
        ITI30Test::testStep30();
    }
}
