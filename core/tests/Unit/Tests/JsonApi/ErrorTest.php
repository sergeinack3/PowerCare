<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Tests\JsonApi;

use Ox\Tests\JsonApi\Error;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class ErrorTest extends OxUnitTestCase
{
    public function testCreateFromArrayThrowError(): void
    {
        $this->expectExceptionObject(new TestsException('Errors must be the first key for errors.'));
        Error::createFromArray([]);
    }

    public function testCreateFromArray(): void
    {
        $error = Error::createFromArray(
            [
                Error::ERRORS => [
                    Error::TYPE => 'foo',
                    Error::CODE => 42,
                    Error::MESSAGE => '42 foo bar !',
                ]
            ]
        );

        $this->assertEquals('foo', $this->getPrivateProperty($error, 'type'));
        $this->assertEquals(42, $this->getPrivateProperty($error, 'code'));
        $this->assertEquals('42 foo bar !', $this->getPrivateProperty($error, 'message'));
    }
}
