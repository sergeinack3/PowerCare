<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use DateTime;
use Exception;
use Ox\Core\CMbDT;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CMbDTTest
 */
class CMbDTTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    public function testAchievedDurationsDT(): void
    {
        $from   = (new DateTime("10-10-2019"))->format("Y-m-d");
        $to     = (new DateTime("20-07-2021"))->format("Y-m-d");
        $result = CMbDT::achievedDurationsDT($from, $to);

        $expected = ["year" => 1, "month" => 21, "week" => 92, "day" => 649, "locale" => "21 months"];

        $this->assertEquals($expected, $result);
    }

    public function testRoundTime(): void
    {
        $time = '2022-10-19 23:50:10';

        $this->assertEquals('2022-01-01 00:00:00', CMbDT::roundTime($time, CMbDT::ROUND_YEAR));
        $this->assertEquals('2022-10-01 00:00:00', CMbDT::roundTime($time, CMbDT::ROUND_MONTH));
        $this->assertEquals('2022-10-19 00:00:00', CMbDT::roundTime($time, CMbDT::ROUND_DAY));
        $this->assertEquals('2022-10-19 23:00:00', CMbDT::roundTime($time, CMbDT::ROUND_HOUR));
    }

    public function testTransformDateToXML(): void
    {
        $this->assertEquals('2022-10-19T23:50:10', CMbDT::dateTimeXML('2022-10-19 23:50:10'));
    }

    public function testTransformXMLToDate(): void
    {
        $time = "2022-10-19T23:50:10";

        $this->assertEquals('2022-10-19 23:50:10', CMbDT::dateTimeFromXMLDateTime("{$time}.000000"));
        $this->assertNull(CMbDT::dateTimeFromXMLDateTime($time));
    }

    public function testConvertTimestampToDatetime(): void
    {
        $this->assertEquals('2022-10-20 23:50:10', CMbDT::dateTimeFromTimestamp('+1 DAY', 1666216210));
    }

    public function testConvertTimeToGMT(): void
    {
        $this->assertEquals('Wed, 19 Oct 2022 21:50:10 GMT', CMbDT::dateTimeGMT(1666216210));
    }

    public function testDaysRelative(): void
    {
        $date1 = '2022-10-19';
        $date2 = '2022-10-26';

        $this->assertEquals(7, CMbDT::daysRelative($date1, $date2));
        $this->assertEquals(5, CMbDT::daysRelative($date1, $date2, true));
    }

    public function testGetNearestMinsWithInterval(): void
    {
        $this->assertEquals("23:50:00", CMbDT::timeGetNearestMinsWithInterval('23:54:10', 10));
    }

    public function testCalculateWorkDays(): void
    {
        // Work days betweeen two dates
        $this->assertEquals('48', CMbDT::workDays('2022-02-01', '2022-04-01'));
        $this->assertEquals('69.5', CMbDT::workDays('2022-02-01', '2022-05-01'));
        // Work days in a specific month
        $this->assertEquals('22', CMbDT::workDaysInMonth('2022-02-01'));
        $this->assertEquals('23.5', CMbDT::workDaysInMonth('2022-01-01'));
    }

    public function testConvertDateTimeToLocale(): void
    {
        $this->assertEquals("19/10/2022", CMbDT::dateToLocale('2022-10-19'));
        $this->assertEquals("23h50", CMbDT::timeToLocale('23:50:10'));
        $this->assertEquals("2022-10-19", CMbDT::dateFromLocale('19/10/2022'));
    }

    public function testConvertDateTimeLDAP(): void
    {
        // convert ldap timestamp to datetime
        $this->assertEquals("19-10-2022 23:50:10", CMbDT::dateTimeFromLDAP(133106898100000000));

        // convert ldap timestamp to unix timestamp
        $this->assertEquals("1666216210", CMbDT::timestampFromLDAP(133106898100000000));
    }

    public function testGetHumanReadableDuration(): void
    {
        $this->assertEquals("05 h 48", CMbDT::getHumanReadableDuration(348));
        $this->assertEquals("5 j 0 h", CMbDT::getHumanReadableDuration(300, 'h', 'd'));
    }

    public function testCalculateDurations(): void
    {
        // calculate duration between two dates
        $duration = CMbDT::duration('2022-10-19 20:00:00', '2022-10-23 20:00:00');
        $this->assertEquals(4, $duration['day']);

        // calculate duration time (hh:mm:ss)
        $this->assertStringStartsWith("96:00", CMbDT::durationTime('2022-10-19 20:00:00', '2022-10-23 20:00:00'));
    }

    public function testLeapDate(): void
    {
        // test isYearBisextilles (true or false)
        $this->assertFalse(CMbDT::isYearBisextilles('2022-10-19 20:00:00'));
        $this->assertTrue(CMbDT::isYearBisextilles('2024-10-19 20:00:00'));

        // test is valid leap datetime
        $this->assertFalse(CMbDT::isValidLeapDatetime('2022-02-29 20:00:00'));
        $this->assertTrue(CMbDT::isValidLeapDatetime('2024-02-29 20:00:00'));
    }

    public function testGetDateTimeDetails(): void
    {
        $datetime = '2022-10-19 20:00:00';

        // test get day
        $this->assertEquals(19, CMbDT::dayNumber($datetime));

        // test get month
        $this->assertEquals(10, CMbDT::monthNumber($datetime));

        // test get week
        $this->assertEquals(42, CMbDT::weekNumber($datetime));

        // test get year
        $this->assertEquals(2022, CMbDT::yearNumber($datetime));
    }

    public function testDateFromWeekNumber(): void
    {
        $week = CMbDT::dateFromWeekNumber('42', '2022');
        $this->assertEquals("2022-10-17", $week['start']);
        $this->assertEquals("2022-10-23", $week['end']);
    }

    public function testWeekCount(): void
    {
        $this->assertEquals(52, CMbDT::weekCount("2022-10-17", "2023-10-17"));
    }

    public function testWeekNumberInMonth(): void
    {
        $this->assertEquals(3, CMbDT::weekNumberInMonth("2022-10-17"));
    }

    public function testGetWorkingDays(): void
    {
        // there is two ranges of working days
        $this->assertCount(2, CMbDT::getWorkingDays('2022-10-17', '2022-10-31'));
    }

    public function testGetNextWorkingDay(): void
    {
        // 2022-10-22 is saturday so skip weekend and return monday
        $this->assertEquals('2022-10-24', CMbDT::getNextWorkingDay('2022-10-22'));
    }

    public function testGetDaysBetween(): void
    {
        // count number of complete days between two dates
        $this->assertCount(9, CMbDT::getDaysBetween('2022-10-17', '2022-10-27'));

        // count number of complete non-opened days between two dates
        $this->assertCount(7, CMbDT::getOpenDaysBetween('2022-10-17', '2022-10-27'));
    }

    public function testDiracConvert(): void
    {
        $datetime = '2022-10-19 23:50:10';

        $this->assertEquals('2022-10-19 23:50:00', CMbDT::dirac('min', $datetime));
        $this->assertEquals('2022-10-19 23:00:00', CMbDT::dirac('hour', $datetime));
        $this->assertEquals('2022-10-19 00:00:00', CMbDT::dirac('day', $datetime));
        $this->assertEquals('2022-10-17 00:00:00', CMbDT::dirac('week', $datetime));
        $this->assertEquals('2022-10-01 00:00:00', CMbDT::dirac('month', $datetime));
        $this->assertEquals('2022-01-01 00:00:00', CMbDT::dirac('year', $datetime));
    }

    public function testToUTCTimestamp(): void
    {
        $this->assertEquals(1666202400000, CMbDT::toUTCTimestamp('2022-10-19 18:00:00'));
    }

    public function testToTimestamp(): void
    {
        $this->assertEquals(1666195200000, CMbDT::toTimestamp('2022-10-19 18:00:00'));
    }

    public function testFormatDuration(): void
    {
        $time        = CMbDT::getDateTimeFromFormat('U.u', microtime(true));
        $time_format = substr($time->format('H:i:s.u'), 0, 12);

        $this->assertInstanceOf(DateTime::class, $time);
        $this->assertStringMatchesFormat('%d:%d:%d.%d', $time_format);
    }

    /**
     * @dataProvider strfTimeConvertionProvider
     */
    public function testStrfTimeConvertion(int $timestamp, string $format, string $result): void
    {
        // Need to force the locale because we do not call CAppUI::loadCoreLocales for Unit tests.
        // Also this allow consistancie for this test between multiple instances.
        $this->assertEquals($result, CMbDT::strftime($format, $timestamp, 'fr-FR'));
    }

    /**
     * Test every single format from deprecated function strftime
     * @link https://www.php.net/manual/fr/function.strftime.php#refsect1-function.strftime-parameters
     */
    public function strfTimeConvertionProvider(): array
    {
        $date_1 = strtotime('2022-09-05 09:31:49');
        $date_2 = strtotime('2022-09-05 15:32:48');

        return [
            '%a' => [$date_1, '%a', 'lun.'],
            '%A' => [$date_1, '%A', 'lundi'],
            '%d' => [$date_1, '%d', '05'],
            '%e' => [$date_1, '%e', ' 5'],
            '%j' => [$date_1, '%j', '248'],
            '%u' => [$date_1, '%u', '1'],
            '%w' => [$date_1, '%w', '1'],
            '%U' => [$date_1, '%U', '36'],
            '%V' => [$date_1, '%V', '36'],
            '%W' => [$date_1, '%W', '36'],
            '%b' => [$date_1, '%b', 'sept.'],
            '%B' => [$date_1, '%B', 'septembre'],
            '%h' => [$date_1, '%h', 'sept.'],
            '%m' => [$date_1, '%m', '09'],
            '%C' => [$date_1, '%C', '20'],
            '%g' => [$date_1, '%g', '22'],
            '%G' => [$date_1, '%G', '2022'],
            '%y' => [$date_1, '%y', '22'],
            '%Y' => [$date_1, '%Y', '2022'],
            '%H' => [$date_1, '%H', '09'],
            '%k' => [$date_1, '%k', ' 9'],
            '%I AM' => [$date_1, '%I', '09'],
            '%I PM' => [$date_2, '%I', '03'],
            '%l AM' => [$date_1, '%l', ' 9'],
            '%l PM' => [$date_2, '%l', ' 3'],
            '%M' => [$date_1, '%M', '31'],
            '%p' => [$date_1, '%p', 'AM'],
            '%P' => [$date_1, '%P', 'am'],
            '%r' => [$date_1, '%r', '09:31:49 AM'],
            '%R' => [$date_1, '%R', '09:31'],
            '%S' => [$date_1, '%S', '49'],
            '%T' => [$date_1, '%T', '09:31:49'],
            '%X' => [$date_1, '%X', '09:31:49'],
            '%z' => [$date_1, '%z', '+0200'],
            '%Z' => [$date_1, '%Z', 'CEST'],
            '%c' => [$date_1, '%c', '5 septembre 2022 à 09:31'],
            '%D' => [$date_1, '%D', '09/05/2022'],
            '%F' => [$date_1, '%F', '2022-09-05'],
            '%s' => [$date_1, '%s', (string) $date_1],
            '%x' => [$date_1, '%x', '05/09/2022'],
            '%n' => [$date_1, '%n', "\n"],
            '%t' => [$date_1, '%t', "\t"],
            '%%' => [$date_1, '%%', '%'],
        ];
    }
}
