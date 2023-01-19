<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\mediboard\System\Tests\Unit;

use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPreferences;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CPreferencesTest extends OxUnitTestCase
{
    /**
     * @pref LOCALES fr
     * @pref FALLBACK_LOCALES fr
     */
    public function testGetPrefValuesForListWithoutUser(): void
    {
        $prefs = CPreferences::getPrefValuesForList(['LOCALE', 'FALLBACK_LOCALE', uniqid()]);

        $expected_prefs = [
            'LOCALES' => 'fr',
            'FALLBACK_LOCALES' => 'fr',
        ];

        $this->assertEquals(ksort($expected_prefs), ksort($prefs));
    }

    public function testGetValuesForListWithEnmptyList(): void
    {
        $prefs = CPreferences::getPrefValuesForList([]);
        $this->assertEquals([], $prefs);
    }

    /**
     * @pref LOCALES fr
     * @pref FALLBACK_LOCALES en
     */
    public function testGetPrefValuesForListWithUser(): void
    {
        $prefs = CPreferences::getPrefValuesForList(['LOCALE', 'FALLBACK_LOCALE', uniqid()], CMediusers::get()->_id);

        $expected_prefs = [
            'LOCALES' => 'fr',
            'FALLBACK_LOCALES' => 'en',
        ];

        $this->assertEquals(ksort($expected_prefs), ksort($prefs));
    }

    /**
     * @pref allowed_check_entry_bloc 1
     */
    public function testGetPrefValuesForListWithRestricted(): void
    {
        $prefs = CPreferences::getPrefValuesForList(['allowed_check_entry_bloc'], CMediusers::get()->_id, true);

        $expected_prefs = [
            'allowed_check_entry_bloc' => '1',
        ];

        $this->assertEquals(ksort($expected_prefs), ksort($prefs));
    }

    public function testGetAllPrefsForList(): void
    {
        CPreferences::setPref('FALLBACK_LOCALE', null, 'en');

        $profile = CMediusers::get()->loadRefProfile();
        CPreferences::setPref('FALLBACK_LOCALE', $profile->_id, 'de');
        CPreferences::setPref('FALLBACK_LOCALE', CMediusers::get()->_id, 'it');

        $this->assertEquals(
            ['FALLBACK_LOCALE' => 'it'],
            CPreferences::getAllPrefsForList(CMediusers::get()->loadRefUser(), ['FALLBACK_LOCALE'])
        );
    }
}
