<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CSmartyDPTest extends OxUnitTestCase
{
    private Crawler $crawler;

    /**
     * @runInSeparateProcess
     */
    public function testMbField(): void
    {
        $user = new CUser();
        $user->user_username = 'test';
        $user->user_type = 14;
        $user->user_sexe = 'u';

        $mediuser = new CMediusers();

        $smarty = new CSmartyDP(dirname(__DIR__) . '/Resources');
        $smarty->assign('user', $user);
        $smarty->assign('mediuser', $mediuser);
        $data = $smarty->fetch('smarty_dp_mb_field_test');

        $this->crawler = new Crawler($data);

        $this->withCStrSpec();
        $this->withCNumSpec();
        $this->withCBoolSpec();
        $this->withCEnumSpec();
        $this->withCSetSpec();
        $this->withCPasswordSpec();
    }

    private function withCStrSpec(): void
    {
        // CStrSpec
        $input_node = $this->crawler->filterXPath('//div[@id="mb-field-str"][1]//input');
        $this->assertEquals('text', $input_node->attr('type'));
        $this->assertEquals('test', $input_node->attr('value'));
        $this->assertEquals(80, $input_node->attr('maxlength'));

        $this->assertFalse(str_contains($input_node->attr('class'), 'notNull'));
    }

    private function withCNumSpec(): void
    {
        // CNumSpec without form
        $input_node = $this->crawler->filterXPath('//div[@id="mb-field-num"][1]//input');
        $this->assertEquals('number', $input_node->attr('type'));
        $this->assertEquals(14, $input_node->attr('value'));

        $this->assertTrue(str_contains($input_node->attr('class'), 'notNull'));
    }

    private function withCEnumSpec(): void
    {
        // CEnumSpec Select
        $select_node = $this->crawler->filterXPath('//div[@id="mb-field-enum-select"][1]//select');
        $this->assertEquals('test_user_sexe', $select_node->attr('name'));

        // Values are sorted alphabeticaly
        $possible_values = ['f', 'm', 'u'];
        $i = 0;
        foreach ($select_node->filterXPath('//option') as $option_node) {
            $value = $option_node->getAttribute('value');
            $this->assertEquals($possible_values[$i], $value);

            if ($value === 'u') {
                $this->assertEquals('selected', $option_node->getAttribute('selected'));
            }

            $i++;
        }

        // CEnumSpec Radio
        $node = $this->crawler->filterXPath('//div[@id="mb-field-enum-radio"][1]');
        $i = 0;
        foreach ($node->filterXPath('//input') as $input_node) {
            $this->assertEquals('user_sexe', $input_node->getAttribute('name'));

            $value = $input_node->getAttribute('value');
            $this->assertEquals($possible_values[$i], $value);

            if ($value === 'u') {
                $this->assertEquals('checked', $input_node->getAttribute('checked'));
            }

            $i++;
        }
    }

    private function withCBoolSpec(): void
    {
        // CBoolSpec Radio
        $node = $this->crawler->filterXPath('//div[@id="mb-field-bool-radio"][1]');

        // The text is the data without the html elements
        $this->assertEquals('bool.1 | bool.0', $node->text());

        foreach ($node->filterXPath('//input[@type="radio"]') as $input_node) {
            $value = $input_node->getAttribute('value');
            $this->assertTrue(in_array($value, [0, 1]));

            if ($value === '0') {
                $this->assertEquals('checked', $input_node->getAttribute('checked'));
            }
        }

        // CBoolSpec checkbox
        $root_node = $this->crawler->filterXPath('//div[@id="mb-field-bool-checkbox"][1]');

        $checkbox_node = $root_node->filterXPath('//input[@type="checkbox"]');
        $this->assertEquals('__template', $checkbox_node->attr('name'));
        $this->assertNotNull($checkbox_node->attr('onclick'));

        $hidden_node = $root_node->filterXPath('//input[@type="hidden"]');
        $this->assertEquals('template', $hidden_node->attr('name'));
        $this->assertEquals(0, $hidden_node->attr('value'));

        // CBoolSpec select
        $root_node = $this->crawler->filterXPath('//div[@id="mb-field-bool-select"][1]//select');
        $this->assertEquals('template', $root_node->attr('name'));
        foreach ($root_node->filterXPath('//option') as $option_node) {
            $value = $option_node->getAttribute('value');

            $this->assertTrue(in_array($value, [0, 1]));
            if ($value === '0') {
                $this->assertEquals('selected', $option_node->getAttribute('selected'));
            }
        }
    }

    private function withCSetSpec(): void
    {
        // CSetSpec select
        $root_node = $this->crawler->filterXPath('//div[@id="mb-field-set-select"][1]');

        $input_node = $root_node->filterXPath('//input[@type="hidden"][1]');
        $this->assertEquals('_ldap_bound', $input_node->attr('name'));

        $select_node = $root_node->filterXPath('//select[@multiple="multiple"][1]');
        $uuid = $select_node->attr('data-select_set');
        $this->assertNotNull($uuid);

        $script = $root_node->filterXPath('//script[1]');
        $this->assertTrue(str_contains($script->text(), "$$('select[data-select_set=$uuid]')"));

        // CSetSpec checkbox
        $root_node = $this->crawler->filterXPath('//div[@id="mb-field-set-checkbox"][1]');
        $container_node = $root_node->filterXPath('//span[1]');

        $span_id = $container_node->attr('id');
        $this->assertStringStartsWith('set-container-', $span_id);
        $uuid = str_replace('set-container-', '', $span_id);

        $input_node = $container_node->filterXPath('//input[@type="hidden"][1]');
        $this->assertEquals('_ldap_bound', $input_node->attr('name'));

        $script = $container_node->filterXPath('//script[1]');
        $this->assertTrue(str_contains($script->text(), "$('set-container-$uuid')"));

        foreach ($container_node->filterXPath('//input[@type="checkbox"]') as $input_node) {
            $value = $input_node->getAttribute('value');
            $this->assertTrue(in_array($value, [0, 1]));
            $this->assertEquals('__ldap_bound_' . $value, $input_node->getAttribute('name'));
            $this->assertStringContainsString('token' . $uuid, $input_node->getAttribute('class'));
        }
    }

    private function withCPasswordSpec(): void
    {
        $root_node = $this->crawler->filterXPath('//div[@id="mb-field-password"][1]');

        $input_node = $root_node->filterXPath('//input[@type="password"][1]');
        $this->assertEquals('_user_password', $input_node->attr('name'));

        $random_node = $root_node->filterXPath('//button[@type="button"][1]');
        $this->assertEquals('dice notext', $random_node->attr('class'));
        $this->assertEquals("getRandomPassword(this, 'CMediusers', '_user_password');", $random_node->attr('onclick'));

        $span_node = $root_node->filterXPath('//span[1]');
        $this->assertEquals('_user_password_message', $span_node->attr('id'));
    }
}
