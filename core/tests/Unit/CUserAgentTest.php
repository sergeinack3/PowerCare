<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use Ox\Mediboard\System\CUserAgent;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CUserAgentTest extends OxUnitTestCase
{
    public function testDetect(): void
    {
        $ua_string = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.193 Safari/537.36";
        $infos     = CUserAgent::detect($ua_string);

        $this->assertEquals($infos->browser, "Chrome");
        $this->assertEquals($infos->version, "86.0");
        $this->assertEquals($infos->platform, "Win10");
        $this->assertEquals($infos->device_type, "Desktop");

        $this->assertObjectHasAttribute("platform_version", $infos);
        $this->assertObjectHasAttribute("device_name", $infos);
        $this->assertObjectHasAttribute("device_maker", $infos);
        $this->assertObjectHasAttribute("device_pointing_method", $infos);
    }

    public function testCreateNoUaString(): void
    {
        $this->assertEquals(new CUserAgent(), CUserAgent::create());
    }

    public function testMapDeviceType(): void
    {
        $this->assertEquals('unknown', CUserAgent::mapDeviceType(null));
        $this->assertEquals('mobile', CUserAgent::mapDeviceType('Mobile Device'));
        $this->assertEquals('desktop', CUserAgent::mapDeviceType('Desktop'));
    }

    public function testGetCodeName(): void
    {
        $ua = new CUserAgent();
        $ua->browser_name = 'Test';
        $this->assertEquals('test', $ua->getCodeName());

        $ua->browser_name = 'Firefox';
        $this->assertEquals('firefox', $ua->getCodeName());

        $ua->browser_name = null;
        $this->assertEquals('', $ua->getCodeName());
    }

    public function testDetectFalse(): void
    {
        $ua_string = "Lorem ipsum dolor set";
        $infos     = CUserAgent::detect($ua_string);
        $this->assertEquals($infos->browser, "Default Browser");
    }

    public function testIsObsolete(): void
    {
        $ua               = new CUserAgent();
        $ua->browser_name = 'Chrome';

        $ua->browser_version = '0.0'; // badly detect
        $this->assertNull($ua->isObsolete());

        $ua->browser_version = '1.1';
        $this->assertTrue($ua->isObsolete());

        $ua->browser_version = '999.999';
        $this->assertFalse($ua->isObsolete());
    }

    public function testIsTooRecent(): void
    {
        $ua               = new CUserAgent();
        $ua->browser_name = 'Chrome';

        $ua->browser_version = '0.0'; // badly detect
        $this->assertNull($ua->isTooRecent());

        $ua->browser_version = '999.999';
        $this->assertTrue($ua->isTooRecent());

        $ua->browser_version = '1.1';
        $this->assertFalse($ua->isTooRecent());
    }

    public function testSupportedBrowser(): void
    {
        // necessary to fix mapping ox <> browscap naming browser
        $supported = array_keys(CUserAgent::SUPPORTED_BROWSERS);
        ksort($supported);
        $this->assertEquals($supported, ['Firefox', 'Chrome', 'Edge']);
    }
}
