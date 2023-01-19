<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Html;

use Ox\Core\Html\HtmlPurifierAdapter;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class HtmlPurifierAdapterTest extends OxUnitTestCase
{
    public function getHtmlSamplesToPurify(): array
    {
        return [
            'no html'                       => [
                'sample',
                'sample',
            ],
            'html with br'                  => [
                'sample<br />sample',
                'sample<br />sample',
            ],
            'valid html'                    => [
                '<!DOCTYPE html><html lang="fr"><head><title>Title</title></head><body><div><p>Lorem ipsum.</p><p>Lorem ipsum.<br />Lorem ipsum</p></div></body></html>',
                '<div><p>Lorem ipsum.</p><p>Lorem ipsum.<br />Lorem ipsum</p></div>',
            ],
            'html with script'              => [
                '<script>alert("sample");</script>sample',
                'sample',
            ],
            'html with script and br'       => [
                '<script>alert("sample");</script>sample<br />sample',
                'sample<br />sample',
            ],
            'invalid html'                  => [
                '<tbody>',
                '',
            ],
            'html with normal attribute'    => [
                '<img src="sample" alt="sample" />',
                '<img src="sample" alt="sample" />',
            ],
            'html with dangerous attribute' => [
                '<img src="sample" alt="sample" onerror="script" />',
                '<img src="sample" alt="sample" />',
            ],
            'html with space s'             => [
                '<script >alert("sample")</script >',
                '',
            ],
            'html with special chars'       => [
                'sample > sample && sample <= \'sample\'',
                'sample &gt; sample &amp;&amp; sample &lt;= \'sample\'',
            ],
        ];
    }

    public function getHtmlSamplesToRemove(): array
    {
        return [
            'no html'                       => [
                'sample',
                'sample',
            ],
            'html with br'                  => [
                'sample<br />sample',
                'samplesample',
            ],
            'valid html'                    => [
                '<!DOCTYPE html><html lang="fr"><head><title>Title</title></head><body><div><p>Lorem ipsum.</p><p>Lorem ipsum.<br />Lorem ipsum</p></div></body></html>',
                'Lorem ipsum.Lorem ipsum.Lorem ipsum',
            ],
            'html with script'              => [
                '<script>alert("sample");</script>sample',
                'sample',
            ],
            'html with script and br'       => [
                '<script>alert("sample");</script>sample<br />sample',
                'samplesample',
            ],
            'invalid html'                  => [
                '<tbody>',
                '',
            ],
            'html with normal attribute'    => [
                '<img src="sample" alt="sample" />',
                '',
            ],
            'html with dangerous attribute' => [
                '<img src="sample" alt="sample" onerror="script" />',
                '',
            ],
            'html with space s'             => [
                '<script >alert("sample")</script >',
                '',
            ],
            'html with special chars'       => [
                'sample > sample && sample <= \'sample\'',
                'sample &gt; sample &amp;&amp; sample &lt;= \'sample\'',
            ],
        ];
    }

    /**
     * @dataProvider getHtmlSamplesToPurify
     */
    public function testPurify(string $html, string $expected): void
    {
        $adapter = new HtmlPurifierAdapter();

        $this->assertEquals($expected, $adapter->purify($html));
    }

    /**
     * @dataProvider getHtmlSamplesToRemove
     */
    public function testRemoveHtml(string $html, string $expected): void
    {
        $adapter = new HtmlPurifierAdapter();

        $this->assertEquals($expected, $adapter->removeHtml($html));
    }
}
