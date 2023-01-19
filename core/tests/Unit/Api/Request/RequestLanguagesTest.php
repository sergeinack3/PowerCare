<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\RequestLanguages;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestLanguagesTest extends OxUnitTestCase
{
    /**
     * @param string $query_content
     * @param array  $expected
     *
     * @dataProvider languagesProvider
     */
    public function testFormats($query_content, $expected)
    {
        $header = ($query_content !== null) ? ['HTTP_' . RequestLanguages::HEADER_KEY_WORD => $query_content] : [];
        $req    = new Request([], [], [], [], [], $header);

        $req_lang = new RequestLanguages($req);
        // Must compare strict array
        $this->assertTrue($expected['languages'] === $req_lang->getLanguage());
        $this->assertTrue($expected['languages_weighting'] === $req_lang->getWeithtingLanguages());

        $weighted_keys = array_keys($expected['languages_weighting']);
        $this->assertEquals(reset($weighted_keys), $req_lang->getExpected());
    }

    /**
     * @return array
     */
    public function languagesProvider()
    {
        return [
            'noLanguage'             => [
                '',
                [
                    'languages'           => [''],
                    'languages_weighting' => ['' => null],
                ],
            ],
            'languageNull'           => [
                null,
                [
                    'languages'           => [RequestLanguages::SHORT_TAG_FR],
                    'languages_weighting' => [RequestLanguages::SHORT_TAG_FR => null],
                ],
            ],
            'singleLanguageNoWeight' => [
                RequestLanguages::SHORT_TAG_FR,
                [
                    'languages'           => [RequestLanguages::SHORT_TAG_FR],
                    'languages_weighting' => [RequestLanguages::SHORT_TAG_FR => null],
                ],
            ],
            'singleLanguageWeighted' => [
                RequestLanguages::LONG_TAG_FR . ';' . 'q=0.5',
                [
                    'languages'           => [RequestLanguages::LONG_TAG_FR . ';' . 'q=0.5'],
                    'languages_weighting' => [RequestLanguages::LONG_TAG_FR => '0.5'],
                ],
            ],
            'multiLanguageNoWeight'  => [
                RequestLanguages::SHORT_TAG_FR . ',' . RequestLanguages::LONG_TAG_EN . ',' . RequestLanguages::SHORT_TAG_EN,
                [
                    'languages'           => [
                        RequestLanguages::SHORT_TAG_FR,
                        RequestLanguages::LONG_TAG_EN,
                        RequestLanguages::SHORT_TAG_EN,
                    ],
                    'languages_weighting' => [
                        RequestLanguages::SHORT_TAG_FR => null,
                        RequestLanguages::LONG_TAG_EN  => null,
                        RequestLanguages::SHORT_TAG_EN => null,
                    ],
                ],
            ],
            'multiLanguageWeighted'  => [
                RequestLanguages::SHORT_TAG_FR . ';q=0.2,' . RequestLanguages::LONG_TAG_EN . ';q=0.8,'
                . RequestLanguages::SHORT_TAG_EN,
                [
                    'languages'           => [
                        RequestLanguages::SHORT_TAG_FR . ';q=0.2',
                        RequestLanguages::LONG_TAG_EN . ';q=0.8',
                        RequestLanguages::SHORT_TAG_EN,
                    ],
                    'languages_weighting' => [
                        RequestLanguages::LONG_TAG_EN  => '0.8',
                        RequestLanguages::SHORT_TAG_FR => '0.2',
                        RequestLanguages::SHORT_TAG_EN => null,
                    ],
                ],
            ],
            'multipleSingleLanguage' => [
                RequestLanguages::SHORT_TAG_FR . ';q=0.2,' . RequestLanguages::SHORT_TAG_FR . ';q=0.8',
                [
                    'languages'           => [
                        RequestLanguages::SHORT_TAG_FR . ';q=0.2',
                        RequestLanguages::SHORT_TAG_FR . ';q=0.8',
                    ],
                    'languages_weighting' => [
                        RequestLanguages::SHORT_TAG_FR => '0.8',
                    ],
                ],
            ],
        ];
    }
}
