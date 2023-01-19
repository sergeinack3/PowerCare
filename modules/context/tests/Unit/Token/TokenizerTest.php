<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context\Tests\Unit\Token;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Context\Token\Tokenizer;
use Ox\Tests\OxUnitTestCase;

class TokenizerTest extends OxUnitTestCase
{
    /**
     * @dataProvider getDateTimeEndProvider
     */
    public function testGetDateTimeEnd(string $current_datetime, ?int $token_lifetime, string $expected_result): void
    {
        $tokenizer = new Tokenizer();
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($tokenizer, 'getDatetimeEnd', $current_datetime, $token_lifetime)
        );
    }

    /**
     * @dataProvider buildParametersProvider
     */
    public function testBuildParameters(array $parameters, string $expected_result): void
    {
        $tokenizer = new Tokenizer();
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($tokenizer, 'buildParameters', $parameters)
        );
    }

    public function testTokenizeInvalidUser(): void
    {
        $user = new CUser();
        $tokenizer = new Tokenizer();

        $this->expectExceptionObject(new CMbException('Tokenize-Error-User must exists'));

        $tokenizer->tokenize($user, []);
    }

    public function testTokenize(): void
    {
        $user = CUser::get();

        $token = (new Tokenizer())->tokenize($user, []);

        $this->assertNotNull($token->_id);
        $this->assertEquals($user->_id, $token->user_id);
    }

    public function getDateTimeEndProvider(): array
    {
        $current_datetime = '2022-10-07 08:00:00';

        $max_lifetime = (ini_get('session.gc_maxlifetime')) ? (int)(ini_get('session.gc_maxlifetime') / 60) : 10;

        return [
            'no_lifetime'       => [$current_datetime, null, CMbDT::dateTime("+{$max_lifetime} minutes", '2022-10-07 08:00:00')],
            'lifetime_negative' => [$current_datetime, -10, CMbDT::dateTime("+{$max_lifetime} minutes", '2022-10-07 08:00:00')],
            'lifetime_ok'       => [$current_datetime, 5, '2022-10-07 08:10:00'],
            'lifetime_too_long' => [$current_datetime, 15, '2022-10-07 08:15:00'],
        ];
    }

    public function buildParametersProvider(): array
    {
        return [
            'no_parameters' => [[], "m=context\na=call\nview=none"],
            'raw_view' => [['view' => 'documents', 'foo' => 'bar'], "m=context\nraw=call\nview=documents\nfoo=bar"],
            'get_info' => [
                ['view' => 'get_infos', 'foo' => 'bar'],
                "m=context\nm=planningOp\na=get_dhe_recently_create\nview=get_infos\nfoo=bar"
            ],
            'get_docs' => [
                ['view' => 'get_docs', 'foo' => 'bar'],
                "m=context\nm=planningOp\na=get_dhe_docs_recently_create\nview=get_docs\nfoo=bar"
            ],
            'with_tabs' => [
                ['foo' => 'bar', 'tabs' => ['tab1', 'tab2', 'tab3']],
                "m=context\na=call\nview=none\nfoo=bar\ntabs[]=tab1\ntabs[]=tab2\ntabs[]=tab3"
            ],
        ];
    }
}
