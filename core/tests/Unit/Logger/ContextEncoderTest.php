<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger;

use Ox\Core\Logger\ContextEncoder;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class ContextEncoderTest extends OxUnitTestCase
{
    public function testEncodeObject(): array
    {
        $object      = new stdClass();
        $object->foo = 'éééààànn';
        $context     = [
            $object,
            'test' => [
                'toto' => [
                    'bar' => 'oké',
                ],
            ],
        ];

        $encoder = new ContextEncoder($context);

        $context = $encoder->encode();

        $this->assertEquals(
            [
                [
                    'stdClass' => [
                        'foo' => mb_convert_encoding('éééààànn', 'UTF-8', 'ISO-8859-1'),
                    ],
                ],
                'test' => [
                    'toto' => [
                        'bar' => mb_convert_encoding('oké', 'UTF-8', 'ISO-8859-1'),
                    ],
                ],
            ],
            $context
        );

        return $context;
    }

    public function testEncodeCModelObject(): void
    {
        $object = new CUser();
        $object->user_username = 'éééaa';

        $encoded = (new ContextEncoder([$object]))->encode();
        $this->assertArrayHasKey(CUser::class, $encoded[0]);
        $this->assertEquals(
            mb_convert_encoding('éééaa', 'UTF-8', 'ISO-8859-1'),
            $encoded[0][CUser::class]['user_username']
        );
    }

    public function testEncodeResource(): void
    {
        $resource = fopen('php://memory', 'r');

        $this->assertEquals(
            [
                ['resource' => 'stream'],
            ],
            (new ContextEncoder([$resource]))->encode()
        );

        fclose($resource);
    }

    public function testDecodeWithObjectContext(): void
    {
        $context      = new stdClass();
        $context->foo = 'bar';

        $encoder = new ContextEncoder($context);
        $this->assertEquals($context, $encoder->decode());
    }

    /**
     * @depends testEncodeObject
     */
    public function testDecodeContext(array $context): void
    {
        $encoder = new ContextEncoder($context);
        $this->assertEquals(
            [
                [
                    'stdClass' => [
                        'foo' => 'éééààànn',
                    ],
                ],
                'test' => [
                    'toto' => [
                        'bar' => 'oké',
                    ],
                ],
            ],
            $encoder->decode()
        );
    }
}
