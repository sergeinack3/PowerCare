<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use DateTime;
use Ox\Core\CSmartyMB;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\Error\Warning;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Description
 */
class CSmartyMBTest extends OxUnitTestCase
{
    public function testDoesNotWarnWhenNamespacedClasses(): void
    {
        $smarty = $this->getMockBuilder(CSmartyMB::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['_const', '_static', 'static_call'])
            ->getMock();

        $atom_format          = $smarty->_const('\Datetime', 'ATOM');
        $model_object_spec    = $smarty->_static('\Ox\Core\CModelObject', 'spec');
        $datetime_from_format = $smarty->static_call('\Datetime::createFromFormat', 'j-M-Y', '15-Feb-2009');

        $this->assertEquals("Y-m-d\TH:i:sP", $atom_format);
        $this->assertIsArray($model_object_spec);
        $this->assertInstanceOf(DateTime::class, $datetime_from_format);
    }

    public function testWarnsAboutNonNamespacedClassesWhenConst(): void
    {
        $smarty = $this->getMockBuilder(CSmartyMB::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['_const'])
            ->getMock();

        $this->expectWarning();
        $smarty->_const('Datetime', 'ATOM');
    }

    public function testWarnsAboutNonNamespacedClassesWhenStatic(): void
    {
        $smarty = $this->getMockBuilder(CSmartyMB::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['_static'])
            ->getMock();

        $this->expectWarning();
        $smarty->_static('CModelObject', 'spec');
    }

    public function testWarnsAboutNonNamespacedClassesWhenStaticCall(): void
    {
        $smarty = $this->getMockBuilder(CSmartyMB::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['static_call'])
            ->getMock();

        $this->expectWarning();
        $smarty->static_call('Datetime::createFromFormat', 'j-M-Y', '15-Feb-2009');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSmartyMb(): void
    {
        $smarty = new CSmartyMB(dirname(__DIR__) . '/Resources');
        $smarty->assign('object', CUser::get());
        $data = $smarty->fetch('smarty_mb_test');

        $crawler = new Crawler($data);

        // mb_default
        $this->assertEquals('foo', $crawler->filterXPath('//div[@id="mb-default"][1]')->text());

        // mb_ditto
        $this->assertEquals('ditto_value', $crawler->filterXPath('//div[@id="first-ditto"][1]')->text());
        $this->assertEquals('|', $crawler->filterXPath('//div[@id="second-ditto"][1]//div[1]')->text());
        $this->assertEquals('ditto_value', $crawler->filterXPath('//div[@id="third-ditto"][1]')->text());

        // mb_class
        $this->assertEquals('class_test', $crawler->filterXPath('//div[@id="mb-class"][1]//input')->attr('value'));
        $this->assertEquals('CUser', $crawler->filterXPath('//div[@id="mb-class-object"][1]//input')->attr('value'));

        // mb_path
        $this->assertEquals('foo/bar', $crawler->filterXPath('//div[@id="mb-path"][1]//input')->attr('value'));

        // tr
        // Translations are not loaded for unit tests ...
        $this->assertEquals('', $crawler->filterXPath('//div[@id="empty-tr"][1]')->text());
        $this->assertEquals('AND', $crawler->filterXPath('//div[@id="tr"][1]')->text());
        $this->assertEquals(
            'Browser-error-content-chrome',
            $crawler->filterXPath('//div[@id="tr-with-var"][1]')->text()
        );
        $this->assertEquals('This trad does not exists', $crawler->filterXPath('//div[@id="tr-markdown"][1]')->text());

        // emphasize
        $this->assertEquals('text', $crawler->filterXPath('//div[@id="emphasize-empty"][1]')->text());
        $this->assertEquals(
            'foo <em>bar</em> <em>is</em> a <em>bar</em><em>bar</em>',
            $crawler->filterXPath('//div[@id="emphasize"][1]')->html()
        );

        // mb_script
        $this->assertStringStartsWith(
            'modules/dPfoo/javascript/bar.js?build=',
            $crawler->filterXPath('//div[@id="mb-script"][1]//script')->attr('src')
        );

        // mb_include
        // Cannot test on an existing template because of hardcoded paths
        $this->assertEquals('', $crawler->filterXPath('//div[@id="mb-include-not-exists"][1]')->text());

        // thumblink
        // Need to use start with because of <div> generated at the end
        $this->assertStringStartsWith(
            '<a href="?m=dPfiles&amp;raw=thumbnail&amp;document_id=1&amp;document_class=CFile&amp;thumb=0&amp;download_raw=1&amp;page=5&amp;length=1" target="_blank" class="button print">This is a link</a>',
            $crawler->filterXPath('//div[@id="thumblink"][1]')->html()
        );

        // thumbnail
        $this->assertEquals(
            "<img src=\"?m=dPfiles&amp;raw=thumbnail&amp;document_guid=CFile-1&amp;profile=large&amp;_ts=0&amp;page=2&amp;crop=1&amp;quality=low&amp;rotate=180\" style=\"background-color: white;max-height:600px; max-width:600px; height: auto; width: auto; \" class=\"me-thumbnail \">",
            $crawler->filterXPath('//div[@id="thumbnail"][1]')->html()
        );

        // mb_return
        $this->assertEmpty($crawler->filterXPath('//div[@id="mb-return"]'));
    }
}
