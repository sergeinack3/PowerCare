<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Tests\OxUnitTestCase;

class CodeCIM10ATIHTest extends OxUnitTestCase
{
    /**
     * @dataProvider providerConstructCategoryAtih
     *
     * @param string $code
     * @param array  $expected
     * @param int    $level
     *
     * @return void
     */
    public function testConstruct(string $code, int $level, array $expected): void
    {
        $cim10ATIH = new CCodeCIM10ATIH($code, $level);
        $this->assertEquals($expected['libelle'], $cim10ATIH->libelle);
        $this->assertEquals($expected['exist'], $cim10ATIH->exist);
        $this->assertEquals($expected['categorie_id'], $cim10ATIH->_category);
        $this->assertEquals($expected['parent_code'], $cim10ATIH->_parent_code);
    }

    /**
     * @param string      $code
     * @param string      $key
     * @param int|null    $max_length
     * @param array       $expected
     * @param int|null    $category
     * @param int|null    $chapter
     * @param string|null $sejour_type
     * @param string|null $field_type
     * @param array|null  $not_expected
     *
     * @return void
     * @dataProvider providerFindCodes
     */
    public function testFindCodes(
        string $code,
        string $key,
        ?int $max_length,
        array $expected,
        int $category = null,
        int $chapter = null,
        string $sejour_type = null,
        string $field_type = null,
        ?array $not_expected = null
    ): void {
        $result = CCodeCIM10ATIH::findCodes(
            $code,
            $key,
            $chapter,
            $category,
            $max_length,
            '',
            '',
            $sejour_type,
            $field_type,
        );
        $this->assertContains($expected, $result);
        $this->assertNotContains($not_expected, $result);
    }

    /**
     * @dataProvider providerAuthorizeCode
     *
     * @param $expected
     * @param $not_expected
     * @param $sejour_type
     * @param $field_type
     *
     * @return void
     */
    public function testGetAuthorizedCode(
        string $expected,
        string $not_expected,
        string $sejour_type,
        string $field_type
    ): void {
        $result = CCodeCIM10ATIH::getAuthorizedCodes($sejour_type, $field_type);

        $this->assertContains($expected, $result);
        $this->assertNotContains($not_expected, $result);
    }
    /**
     * @dataProvider providerAuthorizeCode
     *
     * @param $expected
     * @param $not_expected
     * @param $sejour_type
     * @param $field_type
     *
     * @return void
     */
    public function testGetForbiddenCode(
        string $expected,
        string $not_expected,
        string $sejour_type,
        string $field_type
    ): void {
        $result = CCodeCIM10ATIH::getForbiddenCodes($sejour_type, $field_type);

        $this->assertContains($not_expected, $result);
        $this->assertNotContains($expected, $result);
    }

    public function providerConstructCategoryAtih(): array
    {
        return [
            "codeExist"            => [
                'code'     => "A00",
                'level'    => CCodeCIM10::LITE,
                'expected' => [
                    'libelle'      => "Choléra",
                    'exist'        => true,
                    'categorie_id' => null,
                    'parent_code'  => null,
                ],

            ],
            "codeNotExist"         => [
                'code'     => "test",
                'level'    => CCodeCIM10::LITE,
                'expected' => [
                    'libelle'      => CAppUI::tr('CCodeCIM10.no_exist'),
                    'exist'        => false,
                    'categorie_id' => null,
                    'parent_code'  => null,
                ],
            ],
            "levelDifferentOfLite" => [
                'code'     => "A009",
                'level'    => CCodeCIM10::MEDIUM,
                'expected' => [
                    'libelle'      => "Choléra, sans précision",
                    'exist'        => true,
                    'categorie_id' => new CCIM10CategoryATIH(23),
                    'parent_code'  => 'A00',
                ],
            ],
        ];
    }

    public function providerFindCodes(): array
    {
        return [
            'find_code_with_code'                => [
                'code'       => 'A00',
                'key'        => '',
                'max_length' => 4,
                'expected'   => ["code" => "A00"],
            ],
            'find_code_with_key'                 => [
                'code'       => '',
                'key'        => 'Choléra',
                'max_length' => 4,
                'expected'   => ["code" => "A00"],
            ],
            'find_code_with_key_code'            => [
                'code'       => 'A00',
                'key'        => 'Choléra',
                'max_length' => 4,
                'expected'   => ["code" => "A00"],
            ],
            'finc_code_with_key_category'        => [
                'code'       => '',
                'key'        => 'poumon',
                'max_length' => 5,
                'expected'   => ["code" => "A065"],
                'category'   => 23,
            ],
            'finc_code_with_chapter'             => [
                'code'       => '',
                'key'        => 'poumon',
                'max_length' => 5,
                'expected'   => ["code" => "A065"],
                'category'   => null,
                'chapter'    => 1,
            ],
            'finc_code_sejour_psy_field_type_da' => [
                'code'        => 'A0',
                'key'         => 'fievre',
                'max_length'  => null,
                'expected'    => ["code" => "A010"],
                'category'    => null,
                'chapter'     => null,
                'sejour_type' => "psy",
                'field_type'  => "da",
            ],
        ];
    }

    public function providerAuthorizeCode(): array
    {
        return [
            'sejour_psy_field_type_dp'           => [
                'expected'     => "A000",
                'not_expected' => "A01",
                'sejour_type'  => "psy",
                'field_type'   => "dp",
            ],
            'sejour_ssr_field_type_mmp'          => [
                'expected'     => "A000",
                'not_expected' => "A02",
                'sejour_type'  => "ssr",
                'field_type'   => "mmp",
            ],
            'sejour_ssr_field_type_das'          => [
                'expected'     => "A010",
                'not_expected' => "A01",
                'sejour_type'  => "ssr",
                'field_type'   => "das",
            ],
            'sejour_ssr_field_type_ae'           => [
                'expected'     => "A010",
                'not_expected' => "A01",
                'sejour_type'  => "ssr",
                'field_type'   => "ae",
            ],
            'sejour_ssr_field_type_fppec'        => [
                'expected'     => "Z018",
                'not_expected' => "Z02",
                'sejour_type'  => "ssr",
                'field_type'   => "fppec",
            ],
            'finc_code_sejour_mco_field_type_da' => [
                'expected'     => "A000",
                'not_expected' => "A00",
                'sejour_type'  => "mco",
                'field_type'   => "da",
            ],
            'sejour_mco_field_type_dr'           => [
                'expected'     => "A000",
                'not_expected' => "A00",
                'sejour_type'  => "mco",
                'field_type'   => "dr",
            ],
            'sejour_mco_field_type_dp'           => [
                'expected'     => "A000",
                'not_expected' => "A01",
                'sejour_type'  => "mco",
                'field_type'   => "dp",
            ],
        ];
    }
}
