<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Tests\Unit;

use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Tests\OxUnitTestCase;

class CHL7v2MessageTest extends OxUnitTestCase
{
    public function testEscapeSequences(): void
    {
        $message = new CHL7v2Message();
        $message->initEscapeSequences();

        $escaped   = 'START \F\ \S\ \T\ \E\ \R\ END';
        $unescaped = $message->unescape($escaped);

        $this->assertEquals($unescaped, 'START | ^ & \ ~ END');
        $this->assertEquals($message->escape($unescaped), $escaped);
    }

    public function testEscapeASCII(): void
    {
        $message = new CHL7v2Message();

        $ascii = 'ASCII escape \X41\ ';
        $this->assertEquals($message->unescape($ascii), 'ASCII escape A ');
    }

    public function testFormat(): void
    {
        $message = new CHL7v2Message();

        $format = 'test \H\I\'m strong\N\ test \.br\ new line';

        $this->assertEquals($message->format($format), "test <strong>I'm strong</strong> test <br /> new line");
    }
}
