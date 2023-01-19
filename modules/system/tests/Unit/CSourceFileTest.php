<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Mediboard\System\Sources\CSourceFile;
use Ox\Tests\OxUnitTestCase;

class CSourceFileTest extends OxUnitTestCase
{
    public function providerTimestampsFilename(): array
    {
        $pattern_dt = '\d{8}_\d{6}';

        return [
            'With no filename'                   => ['', "/^$pattern_dt$/"],
            'With no filename and extension'     => ['.xml', "/^$pattern_dt.xml$/"],
            'With no filename and two extension' => ['.xml.gpg', "/^$pattern_dt.xml.gpg$/"],
            'With no extension'                  => ['foo', "/^foo_$pattern_dt$/"],
            'With empty extension'               => ['foo.', "/^foo._$pattern_dt$/"],
            'With one extension'                 => ['foo.xml', "/^foo_$pattern_dt.xml$/"],
            'With two extensions'                => ['foo.xml.gpg', "/^foo_$pattern_dt.xml.gpg$/"],
            'With three extensions'              => ['foo.xml.gpg.rar', "/^foo_$pattern_dt.xml.gpg.rar$/"],
        ];
    }

    /**
     * @param string $actual
     * @param string $expected
     *
     * @dataProvider providerTimestampsFilename
     *
     * @return void
     */
    public function testTimestampsFilename(string $actual, string $expected): void
    {
        $this->assertMatchesRegularExpression($expected, CSourceFile::timestampFileName($actual));
    }
}
