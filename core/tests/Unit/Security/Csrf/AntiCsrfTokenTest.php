<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Security\Csrf;

use Ox\Core\Security\Csrf\AntiCsrfToken;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class AntiCsrfTokenTest extends OxUnitTestCase
{
    public function getValidParameters(): array
    {
        return [
            'all parameters are submitted'                => [
                [
                    'parameter1'                 => null,
                    'parameter2'                 => null,
                    'enforced_parameter'         => 'enforced',
                    'loosely_enforced_parameter' => 0,
                    'enum_parameter'             => 'enum1',
                ],
            ],
            'missing one enforced parameter'              => [
                [
                    'parameter1'                 => null,
                    'parameter2'                 => null,
                    'loosely_enforced_parameter' => 0,
                    'enum_parameter'             => 'enum1',
                ],
            ],
            'with each enum #1'                           => [
                [
                    'parameter1'                 => null,
                    'parameter2'                 => null,
                    'enforced_parameter'         => 'enforced',
                    'loosely_enforced_parameter' => 0,
                    'enum_parameter'             => 'enum1',
                ],
            ],
            'with each enum #2'                           => [
                [
                    'parameter1'                 => null,
                    'parameter2'                 => null,
                    'enforced_parameter'         => 'enforced',
                    'loosely_enforced_parameter' => 0,
                    'enum_parameter'             => 'enum2',
                ],
            ],
            'non enforced parameters with value'          => [
                [
                    'parameter1'                 => 'value1',
                    'parameter2'                 => 222,
                    'enforced_parameter'         => 'enforced',
                    'loosely_enforced_parameter' => 0,
                    'enum_parameter'             => 'enum1',
                ],
            ],
            'enforced parameters with loosely comparison' => [
                [
                    'parameter1'                 => 'value1',
                    'parameter2'                 => 222,
                    'enforced_parameter'         => 'enforced',
                    'loosely_enforced_parameter' => '0',
                    'enum_parameter'             => 'enum1',
                ],
            ],
        ];
    }

    public function getInvalidParameters(): array
    {
        return [
            'empty'                  => [
                [],
            ],
            'superfluous parameter'  => [
                [
                    'parameter1'         => 'value1',
                    'parameter2'         => 222,
                    'enforced_parameter' => 'enforced',
                    'enum_parameter'     => 'enum1',
                    'superfluous'        => null,
                ],
            ],
            'missing enum parameter' => [
                [
                    'parameter1'         => 'value1',
                    'parameter2'         => 222,
                    'enforced_parameter' => 'enforced',
                ],
            ],
            'bad enforced parameter' => [
                [
                    'parameter1'         => 'value1',
                    'parameter2'         => 222,
                    'enforced_parameter' => 'bad enforced',
                    'enum_parameter'     => 'enum1',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getValidParameters
     * @dataProvider getInvalidParameters
     *
     * @param array $parameters
     */
    public function testTokenWithEmptyParametersIsValid(array $parameters): void
    {
        $token = AntiCsrfToken::generate('secret', [], 60);

        $this->assertTrue($token->isValid($parameters));
    }

    /**
     * @dataProvider getValidParameters
     *
     * @param array $parameters
     */
    public function testTokenIsValid(array $parameters): void
    {
        $token = AntiCsrfToken::generate(
            'secret',
            [
                'parameter1'                 => null,
                'parameter2'                 => null,
                'enforced_parameter'         => 'enforced',
                'loosely_enforced_parameter' => 0,
                'enum_parameter'             => ['enum1', 'enum2'],
            ],
            60
        );

        $this->assertTrue($token->isValid($parameters));
    }

    /**
     * @dataProvider getInvalidParameters
     *
     * @param array $parameters
     */
    public function testTokenIsInvalid(array $parameters): void
    {
        $token = AntiCsrfToken::generate(
            'secret',
            [
                'parameter1'                 => null,
                'parameter2'                 => null,
                'enforced_parameter'         => 'enforced',
                'loosely_enforced_parameter' => 0,
                'enum_parameter'             => ['enum1', 'enum2'],
            ],
            60
        );

        $this->assertFalse($token->isValid($parameters));
    }
}
