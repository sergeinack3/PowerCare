<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Transformer;

use DateTime;
use Ox\Core\CMbDT;
use Ox\Import\Framework\Transformer\DefaultTransformer;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class AbstractTransformerTest extends OxUnitTestCase
{
    /**
     * @var DefaultTransformer
     */
    private $default_transformer;


    public function setUp(): void
    {
        $this->default_transformer = new DefaultTransformer();
    }

    /**
     * @dataProvider sanitizeTelProvider
     *
     * @param string $tel
     * @param string $expected_result
     *
     * @throws TestsException
     */
    public function testSanitizeTel(string $tel, string $expected_result): void
    {
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($this->default_transformer, 'sanitizeTel', $tel)
        );
        $this->assertMatchesRegularExpression(
            '/\d+/',
            $this->invokePrivateMethod(
                $this->default_transformer,
                'sanitizeTel',
                $tel
            )
        );
    }

    public function sanitizeTelProvider(): iterable
    {
        yield 'tel_ok' => ['0101010101', '0101010101'];
        yield 'tel_with_letters' => ["0dqsfsqfsdfsq21", "021"];
        yield 'tel_with_spec' => ["0é/dù*^%21", "021"];
        yield 'tel_with_space' => ["  0  2 1   ", "021"];
        yield 'tel_with_all' => [" %^^çà_ dazd0  2 1   ", "021"];
        //            'tel_null'     => [null, ""],

    }

    /**
     * @dataProvider formatDateTimeToStrProvider
     *
     * @param DateTime $date_time
     * @param string   $expected_result
     *
     * @throws TestsException
     */
    public function testFormatDateTimeToStr(DateTime $date_time, string $expected_result): void
    {
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($this->default_transformer, 'formatDateTimeToStr', $date_time)
        );
        $this->assertMatchesRegularExpression(
            '/\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}/',
            $this->invokePrivateMethod(
                $this->default_transformer,
                'formatDateTimeToStr',
                $date_time
            )
        );
    }

    public function formatDateTimeToStrProvider(): array
    {
        $date_time_insert = new DateTime('2020/12/12 22:22:22');

        return [
            'new_date_time'    => [new DateTime(), CMbDT::dateTime()],
            'date_time_insert' => [$date_time_insert, "2020-12-12 22:22:22"],
        ];
    }

    /**
     * @dataProvider formatDateTimeToStrDateProvider
     *
     * @param DateTime $date_time
     * @param string   $expected_result
     *
     * @throws TestsException
     */
    public function testFormatDateTimeToStrDate(DateTime $date_time, string $expected_result): void
    {
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($this->default_transformer, 'formatDateTimeToStrDate', $date_time)
        );
        $this->assertMatchesRegularExpression(
            '/\d{4}\-\d{2}\-\d{2}/',
            $this->invokePrivateMethod(
                $this->default_transformer,
                'formatDateTimeToStrDate',
                $date_time
            )
        );
    }

    public function formatDateTimeToStrDateProvider(): array
    {
        $date_time_insert = new DateTime('2020/12/12 22:22:22');

        return [
            'new_date_time'    => [new DateTime(), CMbDT::date()],
            'date_time_insert' => [$date_time_insert, "2020-12-12"],
        ];
    }

    /**
     * @dataProvider formatDateTimeToStrTimeProvider
     *
     * @param DateTime $date_time
     * @param string   $expected_result
     *
     * @throws TestsException
     */
    public function testFormatDateTimeToStrTime(DateTime $date_time, string $expected_result): void
    {
        $this->assertEquals(
            $expected_result,
            $this->invokePrivateMethod($this->default_transformer, 'formatDateTimeToStrTime', $date_time)
        );
        $this->assertMatchesRegularExpression(
            '/\d{2}:\d{2}:\d{2}/',
            $this->invokePrivateMethod(
                $this->default_transformer,
                'formatDateTimeToStrTime',
                $date_time
            )
        );
    }

    public function formatDateTimeToStrTimeProvider(): array
    {
        $date_time_insert = new DateTime('2020/12/12 22:22:22');

        return [
            'new_date_time'    => [new DateTime(), CMbDT::time()],
            'date_time_insert' => [$date_time_insert, "22:22:22"],
        ];
    }
}

