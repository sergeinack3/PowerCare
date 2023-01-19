<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FileUtil;

use ArgumentCountError;
use Exception;
use InvalidArgumentException;
use Ox\Core\FileUtil\CMbCalendar;
use Ox\Tests\OxUnitTestCase;

class CMbCalendarTest extends OxUnitTestCase
{
    private const UID_REGEX = "/UID:(?<uid>[0-9A-F]{8}\-[0-9A-F]{4}\-4[0-9A-F]{3}\-[89AB][0-9A-F]{3}\-[0-9A-F]{12})/mi";

    private const DTSTAMP_REGEX = "/DTSTAMP:(?<dtstamp>[0-9]{8}T[0-9]{6}Z)/mi";

    public function testConstructWithoutNameThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CMbCalendar("", "Lorem ipsum");
    }

    /**
     * @param string $expected
     * @param bool   $multi_events
     * @param mixed  ...$args
     *
     * @return void
     * @throws Exception
     * @dataProvider iCalFileProviderSuccess
     */
    public function testCreateICalFileSuccessful(string $expected, bool $multi_events, ...$args): void
    {
        $calendar = new CMbCalendar("Lorem", "Lorem ipsum");

        $calendar = $this->addEvent($calendar, $multi_events, $args);

        $result = $calendar->createCalendar();

        $this->replaceDynamicAttributes($result, $expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $expected
     * @param bool   $multi_events
     * @param string $expected_exception
     * @param mixed  ...$args
     *
     * @return void
     * @throws Exception
     * @dataProvider iCalFileProviderFailed
     */
    public function testInvalidEventsThrowException(
        string $expected,
        bool $multi_events,
        string $expected_exception,
        ...$args
    ): void {
        $calendar = new CMbCalendar("Lorem", "Lorem ipsum");
        $this->expectException($expected_exception);

        $calendar = $this->addEvent($calendar, $multi_events, $args);

        $calendar->createCalendar();
    }

    public function iCalFileProviderSuccess(): array
    {
        return [
            "cancelled event"                  => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:CANCEL
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            BEGIN:VEVENT
            UID:CConsultation-lorem
            DTSTAMP:20220913T141559Z
            COMMENT:Lorem ipsum
            DESCRIPTION:Lorem ipsum
            DTSTART:20220101T100000
            DTEND:20220101T120000
            LOCATION:Cabinet 1
            STATUS:CANCELLED
            SUMMARY:Consultation - lorem
            END:VEVENT
            END:VCALENDAR
            EOF
                ,
                false,
                "Cabinet 1",
                "Consultation - lorem",
                "CConsultation-lorem",
                "2022-01-01 10:00:00",
                "2022-01-01 12:00:00",
                "Lorem ipsum",
                "Lorem ipsum",
                true,
            ],
            "normal event"                     => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            BEGIN:VEVENT
            UID:CConsultation-lorem
            DTSTAMP:20220913T141559Z
            COMMENT:Lorem ipsum
            DESCRIPTION:Lorem ipsum
            DTSTART:20220303T160000
            DTEND:20220303T180000
            LOCATION:Cabinet 1
            SUMMARY:Consultation - lorem
            END:VEVENT
            END:VCALENDAR
            EOF
                ,
                false,
                "Cabinet 1",
                "Consultation - lorem",
                "CConsultation-lorem",
                "2022-03-03 16:00:00",
                "2022-03-03 18:00:00",
                "Lorem ipsum",
                "Lorem ipsum",
            ],
            "2 events"                         => [
                <<<EOF
                BEGIN:VCALENDAR
                VERSION:2.0
                PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
                CALSCALE:GREGORIAN
                METHOD:PUBLISH
                UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
                X-WR-CALNAME:Lorem
                X-WR-CALDESC:Lorem ipsum
                X-WR-TIMEZONE:Europe/Paris
                BEGIN:VEVENT
                UID:CConsultation-lorem1
                DTSTAMP:20220913T141559Z
                COMMENT:Lorem ipsum1
                DESCRIPTION:Lorem ipsum1
                DTSTART:20220505T140000
                DTEND:20220505T150000
                LOCATION:Cabinet 2
                SUMMARY:Consultation - lorem1
                END:VEVENT
                BEGIN:VEVENT
                UID:CConsultation-lorem2
                DTSTAMP:20220913T141559Z
                COMMENT:Lorem ipsum2
                DESCRIPTION:Lorem ipsum2
                DTSTART:20220505T170000
                DTEND:20220505T180000
                LOCATION:Cabinet 2
                SUMMARY:Consultation - lorem2
                END:VEVENT
                END:VCALENDAR
                EOF
                ,
                true,
                [
                    "Cabinet 2",
                    "Consultation - lorem1",
                    "CConsultation-lorem1",
                    "2022-05-05 14:00:00",
                    "2022-05-05 15:00:00",
                    "Lorem ipsum1",
                    "Lorem ipsum1",
                ],
                [
                    "Cabinet 2",
                    "Consultation - lorem2",
                    "CConsultation-lorem2",
                    "2022-05-05 17:00:00",
                    "2022-05-05 18:00:00",
                    "Lorem ipsum2",
                    "Lorem ipsum2",
                ],
            ],
            "normal event + 1 cancelled"       => [
                <<<EOF
                BEGIN:VCALENDAR
                VERSION:2.0
                PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
                CALSCALE:GREGORIAN
                METHOD:CANCEL
                UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
                X-WR-CALNAME:Lorem
                X-WR-CALDESC:Lorem ipsum
                X-WR-TIMEZONE:Europe/Paris
                BEGIN:VEVENT
                UID:CConsultation-lorem1
                DTSTAMP:20220913T141559Z
                COMMENT:Lorem ipsum1
                DESCRIPTION:Lorem ipsum1
                DTSTART:20220606T170000
                DTEND:20220606T180000
                LOCATION:Cabinet 1
                SUMMARY:Consultation - lorem1
                END:VEVENT
                BEGIN:VEVENT
                UID:CConsultation-lorem2
                DTSTAMP:20220913T141559Z
                COMMENT:Lorem ipsum2
                DESCRIPTION:Lorem ipsum2
                DTSTART:20220707T170000
                DTEND:20220707T180000
                LOCATION:Cabinet 2
                STATUS:CANCELLED
                SUMMARY:Consultation - lorem2
                END:VEVENT
                END:VCALENDAR
                EOF
                ,
                true,
                [
                    "Cabinet 1",
                    "Consultation - lorem1",
                    "CConsultation-lorem1",
                    "2022-06-06 17:00:00",
                    "2022-06-06 18:00:00",
                    "Lorem ipsum1",
                    "Lorem ipsum1",
                ],
                [
                    "Cabinet 2",
                    "Consultation - lorem2",
                    "CConsultation-lorem2",
                    "2022-07-07 17:00:00",
                    "2022-07-07 18:00:00",
                    "Lorem ipsum2",
                    "Lorem ipsum2",
                    true,
                ],
            ],
            "normal event with minimum params" => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            BEGIN:VEVENT
            UID:CConsultation-lorem
            DTSTAMP:20220913T141559Z
            DTSTART:20220505T100000
            DTEND:20220505T110000
            LOCATION:Salle1
            SUMMARY:Consultation - lorem
            END:VEVENT
            END:VCALENDAR
            EOF
                ,
                false,
                "Salle1",
                "Consultation - lorem",
                "CConsultation-lorem",
                "2022-05-05 10:00:00",
                "2022-05-05 11:00:00",
            ],
        ];
    }

    public function iCalFileProviderFailed(): array
    {
        return [
            "event with invalid date format"   => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            BEGIN:VEVENT
            UID:CConsultation-lorem
            DTSTAMP:20220913T141559Z
            COMMENT:Lorem ipsum
            DTSTART:loremipsum20
            DTEND:loremipsum30
            LOCATION:Salle1
            END:VEVENT
            END:VCALENDAR
            EOF
                ,
                false,
                InvalidArgumentException::class,
                "Salle1",
                "Consultation - lorem",
                "CConsultation-lorem",
                "loremipsum20",
                "loremipsum30",
            ],
            "no events"                        => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            END:VCALENDAR
            EOF
                ,
                false,
                ArgumentCountError::class,
            ],
            "event with start date > end date" => [
                <<<EOF
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//localhost//NONSGML kigkonsult.se iCalcreator 2.39.1//
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            UID:0ec29130-1c3f-4c2c-b367-6b34ed027bdf
            X-WR-CALNAME:Lorem
            X-WR-CALDESC:Lorem ipsum
            X-WR-TIMEZONE:Europe/Paris
            BEGIN:VEVENT
            UID:CConsultation-lorem
            DTSTAMP:20220913T141559Z
            COMMENT:Lorem ipsum
            DTSTART:20220505T120000
            DTEND:20220505T100000
            LOCATION:Salle1
            END:VEVENT
            END:VCALENDAR
            EOF
                ,
                false,
                InvalidArgumentException::class,
                "Salle1",
                "Consultation - lorem",
                "CConsultation-lorem",
                "2022-05-05 12:00:00",
                "2022-05-05 10:00:00",
            ],
        ];
    }

    /**
     * UID and DTSTAMP are params that we can't control.
     * We are only replacing them to valid tests
     *
     * @param string $original
     * @param string $destination
     *
     * @return void
     */
    private function replaceDynamicAttributes(string &$original, string &$destination): void
    {
        $uid     = null;
        $dtstamp = null;
        $matches = [];

        if (
            preg_match(
                self::UID_REGEX,
                $original,
                $matches
            )
        ) {
            $uid = $matches['uid'];
        }

        $matches = [];

        if (
            preg_match(
                self::DTSTAMP_REGEX,
                $original,
                $matches
            )
        ) {
            $dtstamp = $matches['dtstamp'];
        }

        $this->assertNotNull($uid);

        $destination = preg_replace(
            self::UID_REGEX,
            "UID:{$uid}",
            $destination
        );

        $destination = preg_replace(
            self::DTSTAMP_REGEX,
            "DTSTAMP:{$dtstamp}",
            $destination
        );

        $original = trim(str_replace("\r\n", "\n", $original));
    }

    /**
     * @param CMbCalendar $calendar
     * @param bool        $multi_events
     * @param mixed       $args
     *
     * @return CMbCalendar
     * @throws Exception
     */
    private function addEvent(CMbCalendar $calendar, bool $multi_events, $args): CMbCalendar
    {
        if ($args) {
            if ($multi_events) {
                foreach ($args as $event) {
                    $calendar->addEvent(
                        ...$event
                    );
                }
            } else {
                $calendar->addEvent(...$args);
            }
        } else {
            $calendar->addEvent(...$args);
        }

        return $calendar;
    }
}
