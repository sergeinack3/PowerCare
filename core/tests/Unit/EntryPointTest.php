<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Core\CSmartyMB;
use Ox\Core\EntryPoint;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\OxUnitTestCase;

class EntryPointTest extends OxUnitTestCase
{
    /**
     * @pref foo bar
     * @config config1 config1_value
     * @config [CConfiguration] config_context config_context_value
     */
    public function testBuildHtml(): void
    {
        $entry = new EntryPoint('test');
        $entry->setData(['foo' => 'bar'])
            ->addData('bar', 1)
            ->addMeta('meta', 'value')
            ->setMeta(['meta_replace' => 'true_value'])
            ->addPref('pref-foo', 'foo')
            ->addConfig('config-1', 'config1')
            ->addConfig('config-context', 'config_context', CGroups::loadCurrent()->_guid)
            ->addLinkValue('link', '/gui?foo=bar')
            ->setLocales(['AND', ['toto', 'test']]);

        $expected_div = '<div id="test"';
        $expected_div .= " vue-foo='bar' :vue-bar='1'";
        $expected_div .= " :vue-links='{\"link\":\"\/gui?foo=bar\"}'";
        $expected_div .= " :vue-configs='{\"config-1\":\"config1_value\",\"config-context\":\"config_context_value\"}'";
        $expected_div .= " :vue-prefs='{\"pref-foo\":\"bar\"}'";
        $expected_div .= " :vue-meta='{\"meta_replace\":\"true_value\"}'";
        // Locales are not loaded for unit tests
        $expected_div .= " :vue-locales='{\"AND\":\"AND\",\"toto\":\"toto\"}'";
        $expected_div .= '></div>';

        $this->assertEquals($expected_div, (new CSmartyMB())->mb_entry_point(['entry_point' => $entry]));
    }

    public function testAddLinkWithoutRouter(): void
    {
        $entry = new EntryPoint('test');

        $this->expectExceptionObject(new CMbException('EntryPoint-Error-No router to generate link'));

        $entry->addLink('test', 'toto');
    }
}
