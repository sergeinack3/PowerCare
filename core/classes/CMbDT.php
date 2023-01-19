<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use InvalidArgumentException;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Date and time manipulation class
 */
class CMbDT
{
    // ISO date formats
    const ISO_DATE     = "%Y-%m-%d";
    const ISO_TIME     = "%H:%M:%S";
    const ISO_DATETIME = "%Y-%m-%d %H:%M:%S";

    // GMT date formats
    const GMT_DATETIME = "D, d M Y H:i:s";

    const TIMESTAMPS = "TIMESTAMPS";

    // XML date formats
    const XML_DATE     = "%Y-%m-%d";
    const XML_TIME     = "%H:%M:%S";
    const XML_DATETIME = "%Y-%m-%dT%H:%M:%S";

    // Round time
    const ROUND_YEAR  = "M:d:h:m:s";
    const ROUND_MONTH = "d:h:m:s";
    const ROUND_DAY   = "h:m:s";
    const ROUND_HOUR  = "m:s";

    // Multipliers months to X
    const MULTIPLIER_MONTHS_TO_WEEKS = 4.34524;
    const MULTIPLIER_MONTHS_TO_DAY   = 30.4167;

    public const SECS_PER_YEAR   = 31536000; // 60 * 60 * 24 * 365
    public const SECS_PER_MONTH  = 2592000; // 60 * 60 * 24 * 30
    public const SECS_PER_WEEK   = 604800;   // 60 * 60 * 24 * 30
    public const SECS_PER_DAY    = 86400;     // 60 * 60 * 24
    public const SECS_PER_HOUR   = 3600;     // 60 * 60
    public const SECS_PER_MINUTE = 60;     // 60
    public const SECS_PER_SECOND = 1;      // 1

    public const SECS_PER = [
        "year"   => self::SECS_PER_YEAR,
        "month"  => self::SECS_PER_MONTH,
        "week"   => self::SECS_PER_WEEK,
        "day"    => self::SECS_PER_DAY,
        "hour"   => self::SECS_PER_HOUR,
        "minute" => self::SECS_PER_MINUTE,
        "second" => self::SECS_PER_SECOND,
    ];

    /** @var string|null */
    private static $system_date;

    /**
     * Set the system date
     *
     * /!\ SHOULD NOT BE USED OUT OF FRAMEWORK INITIALISATION
     *
     * @param string|null $date
     *
     * @return void
     */
    public static function setSystemDate(?string $date = null): void
    {
        self::$system_date = $date;
    }


    /**
     * Fix the timestamp value for strftime function
     * @param $timestamp mixed
     *
     * @return DateTime
     */
    private static function correctTimestampValue($timestamp): DateTime
    {
        if (null === $timestamp) {
            $timestamp = new DateTime();
        } elseif (is_numeric($timestamp)) {
            $timestamp = date_create('@' . $timestamp);

            if ($timestamp) {
                $timestamp->setTimezone(new DateTimezone(date_default_timezone_get()));
            }
        } elseif (is_string($timestamp)) {
            $timestamp = date_create($timestamp);
        } elseif ($timestamp === false) {
            $timestamp = (new DateTime())->setTimestamp(0);
        }

        if (!($timestamp instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                '$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.'
            );
        }

        return $timestamp;
    }

    /**
     * Locale-formatted strftime using IntlDateFormatter (PHP 8.1 compatible)
     * This provides a cross-platform alternative to strftime() for when it will be removed from PHP.
     * Note that output can be slightly different between libc sprintf and this function as it is using ICU.
     *
     * Usage:
     * use function CMbDT::strftime;
     * echo CMbDT::strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
     *
     * Original use:
     * \setlocale('fr_FR.UTF-8', LC_TIME);
     * echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
     *
     * @param string                            $format    Date format
     * @param null|int|string|DateTimeInterface $timestamp Timestamp
     * @param string|null                       $locale    Locale to use. If null it will depend on CApp::$lang.
     *
     * @throws InvalidArgumentException
     *
     * @author BohwaZ <https://bohwaz.net/> https://gist.github.com/bohwaz/42fc223031e2b2dd2585aab159a20f30
     */
    public static function strftime(string $format, $timestamp = null, ?string $locale = null): string
    {
        if (!$locale) {
            $locale = CAppUI::$locale_info['names'][0] ?? null;
        }

        $timestamp = self::correctTimestampValue($timestamp);

        $locale = substr((string) $locale, 0, 5);

        $intl_formats = [
            '%a' => 'EEE', // An abbreviated textual representation of the day Sun through Sat
            '%A' => 'EEEE', // A full textual representation of the day Sunday through Saturday
            '%b' => 'MMM', // Abbreviated month name, based on the locale Jan through Dec
            '%B' => 'MMMM', // Full month name, based on the locale January through December
            '%h' => 'MMM', // Abbreviated month name, based on the locale (an alias of %b) Jan through Dec
        ];

        $intl_formatter = function (DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
            $tz = $timestamp->getTimezone();
            $date_type = IntlDateFormatter::FULL;
            $time_type = IntlDateFormatter::FULL;
            $pattern = '';

            // %c = Preferred date and time stamp based on locale
            // %c = Preferred date and time stamp based on locale
            // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
            if ($format == '%c') {
                $date_type = IntlDateFormatter::LONG;
                $time_type = IntlDateFormatter::SHORT;
            } elseif ($format == '%x') {
                // %x = Preferred date representation based on locale, without the time
                // Example: 02/05/09 for February 5, 2009
                $date_type = IntlDateFormatter::SHORT;
                $time_type = IntlDateFormatter::NONE;
            } elseif ($format == '%X') {
                // Localized time format
                $date_type = IntlDateFormatter::NONE;
                $time_type = IntlDateFormatter::MEDIUM;
            } else {
                $pattern = $intl_formats[$format];
            }

            return (new IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
        };

        // Same order as https://www.php.net/manual/en/function.strftime.php
        $translation_table = [
            // Day
            '%a' => $intl_formatter,
            '%A' => $intl_formatter,
            '%d' => 'd',
            '%e' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('j'));
            },
            '%j' => function ($timestamp) {
                // Day number in year, 001 to 366
                return sprintf('%03d', $timestamp->format('z') + 1);
            },
            '%u' => 'N',
            '%w' => 'w',

            // Week
            '%U' => function ($timestamp) {
                // Number of weeks between date and first Sunday of year
                $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));

                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },
            '%V' => 'W',
            '%W' => function ($timestamp) {
                // Number of weeks between date and first Monday of year
                $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));

                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },

            // Month
            '%b' => $intl_formatter,
            '%B' => $intl_formatter,
            '%h' => $intl_formatter,
            '%m' => 'm',

            // Year
            '%C' => function ($timestamp) {
                // Century (-1): 19 for 20th century
                return floor($timestamp->format('Y') / 100);
            },
            '%g' => function ($timestamp) {
                return substr($timestamp->format('o'), -2);
            },
            '%G' => 'o',
            '%y' => 'y',
            '%Y' => 'Y',

            // Time
            '%H' => 'H',
            '%k' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('G'));
            },
            '%I' => 'h',
            '%l' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('g'));
            },
            '%M' => 'i',
            '%p' => 'A', // AM PM (this is reversed on purpose!)
            '%P' => 'a', // am pm
            '%r' => 'h:i:s A', // %I:%M:%S %p
            '%R' => 'H:i', // %H:%M
            '%S' => 's',
            '%T' => 'H:i:s', // %H:%M:%S
            '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

            // Timezone
            '%z' => 'O',
            '%Z' => 'T',

            // Time and Date Stamps
            '%c' => $intl_formatter,
            '%D' => 'm/d/Y',
            '%F' => 'Y-m-d',
            '%s' => 'U',
            '%x' => $intl_formatter,
        ];

        $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
            if ($match[1] == '%n') {
                return "\n";
            } elseif ($match[1] == '%t') {
                return "\t";
            }

            if (!isset($translation_table[$match[1]])) {
                throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
            }

            $replace = $translation_table[$match[1]];

            if (is_string($replace)) {
                return $timestamp->format($replace);
            } else {
                $result = $replace($timestamp, $match[1]);
                // In case result is returned by an intl function we have to decode it to ISO-8859-1
                return $result ? mb_convert_encoding($result, 'ISO-8859-1', 'UTF-8') : $result;
            }
        }, $format);

        return str_replace('%%', '%', $out);
    }

    /**
     * Transforms absolute or relative time into a given format
     *
     * @param string $relative A relative time
     * @param string $ref      An absolute time to transform
     * @param string $format   The format in which the date will be returned
     *
     * @return float|int|string The transformed date
     */
    static function transform($relative, $ref, $format)
    {
        if ($relative === "last sunday") {
            $relative .= " 12:00:00";
        }

        $timestamp = $ref ? strtotime($ref) : (self::$system_date ? strtotime(
            self::$system_date . " " . date("H:i:s")
        ) : time());
        if ($relative) {
            $timestamp = strtotime($relative, $timestamp);
        }

        if ($format == self::TIMESTAMPS) {
            return $timestamp * 1000; //ms
        }

        return self::strftime($format, $timestamp);
    }

    /**
     * Arrondi un dateTime selon un format donné.
     *
     * @param string $dateTime     DateTime
     * @param string $round_format format dateTime
     *
     * @return datetime|string
     */
    static function roundTime($dateTime, $round_format)
    {
        if ($dateTime == null) {
            $dateTime = self::dateTime();
        }
        $format         = explode(":", $round_format);
        $fragments_date = explode("-", CMbArray::get(explode(" ", $dateTime), "0", ""));
        $fragments_time = explode(":", CMbArray::get(explode(" ", $dateTime), "1", ""));
        $relat          = "";
        foreach ($format as $_f) {
            switch ($_f) {
                case "s":
                    if ($seconds = CValue::read($fragments_time, 2, 0)) {
                        $relat .= "-$seconds SECONDS ";
                    }
                    break;

                case "m":
                    if ($minutes = CValue::read($fragments_time, 1, 0)) {
                        $relat .= "-$minutes MINUTES ";
                    }
                    break;

                case "h":
                    if ($hours = CValue::read($fragments_time, 0, 0)) {
                        $relat .= "-$hours HOURS ";
                    }
                    break;

                case 'd':
                    $days = CValue::read($fragments_date, 2, 0);
                    $days = intval($days) > 1 ? intval($days) - 1 : 0; // day 0 of date not exist
                    if ($days) {
                        $relat .= "-$days DAYS ";
                    }
                    break;

                case 'M':
                    $month = CValue::read($fragments_date, 1, 0);
                    $month = intval($month) > 1 ? intval($month) - 1 : 0; // month 0 of date not exist
                    if ($month) {
                        $relat .= "-$month MONTHS ";
                    }
                default:
            }
        }

        return CMbDT::dateTime($relat, $dateTime);
    }

    /**
     * Know what day we are
     *
     * @param null $ref          reference
     * @param bool $isTimestamps if ref is timestamps
     *
     * @return string
     */
    static function daysIs($ref = null, $isTimestamps = false)
    {
        $ref = ($ref && !$isTimestamps) ? strtotime($ref) : ($isTimestamps ? $ref / 1000 : $ref);

        return date("l", $ref);
    }


    /**
     * Shortcut to transform when no relative operand is given
     *
     * @param string $ref    An absolute time to transform
     * @param string $format The format in which the date will be returned
     *
     * @return string The transformed date
     */
    static function format($ref, $format)
    {
        return self::transform(null, $ref, $format);
    }

    /**
     * Transforms absolute or relative date into an ISO_DATETIME
     *
     * @param string $relative [optional] Modifies the date (eg '+1 DAY')
     * @param string $ref      [optional] The reference date time fo transform
     *
     * @return string The date
     **/
    static function date($relative = null, $ref = null)
    {
        return self::transform($relative, $ref, self::ISO_DATE);
    }

    /**
     * Transforms absolute or relative time into an ISO_DATETIME
     *
     * @param string $relative Modifies the time (eg '+1 DAY')
     * @param string $ref      The reference time time fo transform
     *
     * @return string The time
     **/
    static function time($relative = null, $ref = null)
    {
        return self::transform($relative, $ref, self::ISO_TIME);
    }

    /**
     * Transforms absolute or relative datetime into an ISO_DATETIME
     *
     * @param string $relative Modifies the datetime (eg '+1 DAY')
     * @param string $ref      The reference datetime fo transform
     *
     * @return string The datetime
     **/
    static function dateTime($relative = null, $ref = null)
    {
        return self::transform($relative, $ref, self::ISO_DATETIME);
    }

    /**
     * Transforms absolute or relative time into XML DATETIME format
     *
     * @param string $relative Modifies the time (eg '+1 DAY')
     * @param string $ref      The reference date time fo transforms
     *
     * @return string The transformed time
     **/
    static function dateTimeXML($relative = null, $ref = null)
    {
        return self::transform($relative, $ref, self::XML_DATETIME);
    }

    /**
     * Converts an XML datetime into an ISO_DATETIME
     *
     * @param string $datetime XML datetime with format YYYY-MM-DDTHH:mm:ss.uuuuuu
     *
     * @return string The ISO_DATETIME, null if failed
     **/
    static function dateTimeFromXMLDateTime($datetime)
    {
        $regexp = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).(\d{6})?/";
        if (!preg_match($regexp, $datetime, $matches)) {
            return null;
        }

        return sprintf(
            "%d-%d-%d %d:%d:%d",
            $matches[1],
            $matches[2],
            $matches[3],
            $matches[4],
            $matches[5],
            $matches[6]
        );
    }

    /**
     * Convert timestamp in datetime
     *
     * @param String $relative  Modifies the time (eg '+1 DAY')
     * @param String $timestamp time in timestamp second
     *
     * @return string The Datetime
     */
    static function dateTimeFromTimestamp($relative, $timestamp)
    {
        return CMbDT::dateTime($relative, date('Y-m-d H:i:s', $timestamp));
    }

    /**
     * Transforms relative time into GMT/UTC date/time
     *
     * @param string $ref The reference date time fo transforms
     *
     * @return string The transformed time in GMT
     **/
    static function dateTimeGMT($ref = null)
    {
        return gmdate(self::GMT_DATETIME, ($ref ? $ref : time())) . " GMT";
    }

    /**
     * Add a relative time to a reference time
     *
     * @param string $relative The relative time to add
     * @param string $ref      The reference time
     *
     * @return string The resulting time
     **/
    static function addTime($relative = null, $ref = null)
    {
        $fragments = explode(":", $relative);
        $hours     = CValue::read($fragments, 0, 0);
        $minutes   = CValue::read($fragments, 1, 0);
        $seconds   = CValue::read($fragments, 2, 0);

        return self::time("+$hours HOURS $minutes MINUTES $seconds SECONDS", $ref);
    }

    /**
     * Add a relative time to a reference datetime
     *
     * @param string $relative The relative time to add
     * @param string $ref      The reference datetime
     *
     * @return string The resulting time
     **/
    static function addDateTime($relative, $ref = null)
    {
        $fragments = explode(":", $relative ?? "0:0:0");
        $hours     = CValue::read($fragments, 0, 0);
        $minutes   = CValue::read($fragments, 1, 0);
        $seconds   = CValue::read($fragments, 2, 0);

        return self::dateTime("+$hours HOURS $minutes MINUTES $seconds SECONDS", $ref);
    }

    /**
     * Substract a relative time to a reference time
     *
     * @param string $relative The relative time to substract
     * @param string $ref      The reference time
     *
     * @return string The resulting time
     **/
    static function subTime($relative = null, $ref = null)
    {
        $fragments = explode(":", $relative);
        $hours     = CValue::read($fragments, 0, 0);
        $minutes   = CValue::read($fragments, 1, 0);
        $seconds   = CValue::read($fragments, 2, 0);

        return self::time("-$hours HOURS -$minutes MINUTES -$seconds SECONDS", $ref);
    }

    /**
     * Sub a relative time to a reference datetime
     *
     * @param string $relative The relative time to add
     * @param string $ref      The reference datetime
     *
     * @return string The resulting time
     **/
    static function subDateTime($relative, $ref = null)
    {
        $fragments = explode(":", $relative);
        $hours     = CValue::read($fragments, 0, 0);
        $minutes   = CValue::read($fragments, 1, 0);
        $seconds   = CValue::read($fragments, 2, 0);

        return self::dateTime("-$hours HOURS -$minutes MINUTES -$seconds SECONDS", $ref);
    }

    /**
     * Count days between two datetimes
     *
     * @param string $from        From datetime
     * @param string $to          To datetime
     * @param bool   $worked_days Only count worked days
     *
     * @return int Days count
     **/
    static function daysRelative($from, $to, $worked_days = false)
    {
        if (!$from || !$to) {
            return null;
        }
        $f = intval(strtotime($from) / self::SECS_PER_DAY);
        $t = intval(strtotime($to) / self::SECS_PER_DAY);

        $result = intval($t - $f);

        if ($worked_days) {
            $range = self::getDays(CMbDT::date($from), CMbDT::date($to));
            foreach ($range as $day) {
                if ($result >= 1 && !self::isWorkingDay($day)) {
                    $result--;
                }
            }
        }

        return $result;
    }

    /**
     * Count hours between two datetimes
     *
     * @param string $from From datetime
     * @param string $to   To datetime
     *
     * @return int Days count
     **/
    static function hoursRelative($from, $to)
    {
        if (!$from || !$to) {
            return null;
        }

        $from = intval(strtotime($from) / 3600);
        $to   = intval(strtotime($to) / 3600);

        return intval($to - $from);
    }

    /**
     * Count minutes between two datetimes
     *
     * @param string $from From datetime
     * @param string $to   To datetime
     *
     * @return int Days count
     **/
    static function minutesRelative($from, $to)
    {
        if (!$from || !$to) {
            return null;
        }

        $from = intval(strtotime($from) / 60);
        $to   = intval(strtotime($to) / 60);

        return intval($to - $from);
    }

    /**
     * Compute time duration between two datetimes
     *
     * @param string $from   From date
     * @param string $to     To date
     * @param string $format Format for time (sprintf syntax)
     *
     * @return string hh:mm:ss diff duration
     **/
    static function timeRelative($from, $to, $format = "%02d:%02d:%02d")
    {
        $diff  = strtotime($to) - strtotime($from);
        $hours = intval($diff / 3600);
        $mins  = intval(($diff % 3600) / 60);
        $secs  = intval($diff % 60);

        return sprintf($format, $hours, $mins, $secs);
    }

    /**
     * Counts the number of intervals between reference and relative
     *
     * @param string $from     From time
     * @param string $to       To time
     * @param string $interval Interval time
     *
     * @return int Number of intervals
     **/
    static function timeCountIntervals($from, $to, $interval)
    {
        $zero     = strtotime("00:00:00");
        $from     = strtotime($from) - $zero;
        $to       = strtotime($to) - $zero;
        $interval = strtotime($interval) - $zero;

        return intval(($to - $from) / $interval);
    }

    /**
     * Retrieve nearest time (Dirac-like) with intervals
     *
     * @param string     $reference     Reference time
     * @param int|string $mins_interval Minutes count
     *
     * @return string Nearest time
     **/
    static function timeGetNearestMinsWithInterval($reference, $mins_interval)
    {
        $min_reference = self::transform(null, $reference, "%M");
        $div           = intval($min_reference / $mins_interval);
        $borne_inf     = $mins_interval * $div;
        $borne_sup     = $mins_interval * ($div + 1);
        $mins_replace  = ($min_reference - $borne_inf) < ($borne_sup - $min_reference) ?
            $borne_inf :
            $borne_sup;

        $reference = ($mins_replace == 60) ?
            sprintf('%02d:00:00', self::transform(null, $reference, "%H") + 1) :
            sprintf('%02d:%02d:00', self::transform(null, $reference, "%H"), $mins_replace);

        return $reference;
    }

    /**
     * Return the Easter Date following a date
     *
     * @param string $date Reference date
     *
     * @return string the Easter date (Y-m-d)
     */
    static function getEasterDate($date = null)
    {
        $date = ($date) ?: CMbDT::date();
        $year = CMbDT::format($date, "%Y");

        $year = intval($year);

        $n = $year - 1900;
        $a = $n % 19;
        $b = intval((7 * $a + 1) / 19);
        $c = ((11 * $a) - $b + 4) % 29;
        $d = intval($n / 4);
        $e = ($n - $c + $d + 31) % 7;
        $P = 25 - $c - $e;

        if ($P > 0) {
            $P = "+" . $P;
        }

        return CMbDT::date("$P DAYS", "$year-03-31");
    }

    /**
     * Calculate the number of work days in the given month date
     *
     * @param string $date The relative date of the months to get work days
     *
     * @return integer Number of work days
     **/
    static function workDaysInMonth($date = null)
    {
        $result = 0;
        if (!$date) {
            $date = self::date();
        }

        $debut  = $date;
        $rectif = self::transform("+0 DAY", $debut, "%d") - 1;
        $debut  = self::date("-$rectif DAYS", $debut);
        $fin    = $date;
        $rectif = self::transform("+0 DAY", $fin, "%d") - 1;
        $fin    = self::date("-$rectif DAYS", $fin);
        $fin    = self::date("+ 1 MONTH", $fin);
        $fin    = self::date("-1 DAY", $fin);

        $freeDays = self::getHolidays($date);

        for ($i = $debut; $i <= $fin; $i = self::date("+1 DAY", $i)) {
            $day = self::transform("+0 DAY", $i, "%u");
            if ($day == 6 && !in_array($i, $freeDays)) {
                $result += 0.5;
            } elseif ($day != 7 and !in_array($i, $freeDays)) {
                $result += 1;
            }
        }

        return $result;
    }

    /**
     * Calculate the number of work days in the given time period
     *
     * @param string $from The beginning of the period
     * @param string $to   The end of the period
     *
     * @return integer Number of work days
     **/
    static function workDays($from = null, $to = null)
    {
        $result = 0;
        if (!$from) {
            $from = self::date();
        }
        if (!$to) {
            $to = self::date();
        }

        $freeDays = self::getHolidays($from);

        for ($i = $from; $i <= $to; $i = self::date("+1 DAY", $i)) {
            $day = self::transform("+0 DAY", $i, "%u");
            if ($day == 6 && !array_key_exists($i, $freeDays)) {
                $result += 0.5;
            } elseif ($day != 7 and !array_key_exists($i, $freeDays)) {
                $result += 1;
            }
        }

        return $result;
    }

    /**
     * Tell whether date is lunar
     *
     * @param string $date Date to check
     *
     * @return boolean
     **/
    static function isLunarDate($date)
    {
        if (strpos($date ?? '', '-') === false) {
            return false;
        }

        $fragments = explode("-", $date);

        return ($fragments[2] > 31) || ($fragments[1] > 12);
    }

    /**
     * Convert a date from ISO to locale format
     *
     * @param string $date Date in ISO format
     *
     * @return string Date in locale format
     */
    static function dateToLocale($date)
    {
        return preg_replace("/(\d{4})-(\d{2})-(\d{2})/", '$3/$2/$1', $date);
    }

    /**
     * Convert a time from ISO to locale format
     *
     * @param string $time
     *
     * @return string
     */
    public static function timeToLocale(string $time): string
    {
        return preg_replace('/(\d{2}):(\d{2}):(\d{2})/', '$1h$2', $time);
    }

    /**
     * Convert a date from locale to ISO format
     *
     * @param string $date Date in locale format
     *
     * @return string Date in ISO format
     */
    static function dateFromLocale($date)
    {
        return preg_replace("/(\d{2})\/(\d{2})\/(\d{2,4})/", '$3-$2-$1', $date ?? '');
    }

    /**
     * Convert a datetime from LDAP to ISO format
     *
     * @param string $filetime nano seconds (yes, nano seconds) since jan 1st 1601
     *
     * @return string DateTime in ISO format
     * @see http://www.php.net/manual/de/function.ldap-get-entries.php#33180
     *
     */
    static function dateTimeFromLDAP($filetime)
    {
        return date("d-m-Y H:i:s", static::timestampFromLDAP($filetime));
    }

    /**
     * Convert a LDAP timestamp to UNIX timestamp
     *
     * @param integer $timestamp LDAP timestamp
     *
     * @return bool|int|string
     */
    static function timestampFromLDAP($timestamp)
    {
        // Divide by 10 000 000 to get seconds
        $win_secs = bcdiv($timestamp, '10000000');

        // 1.1.1601 -> 1.1.1970 difference in seconds
        return bcsub($win_secs, '11644473600');
    }

    /**
     * Convert a datetime from ActiveDirectory to ISO format
     *
     * @param string $dateAD Datetime from AD since jan 1st 1601
     *
     * @return string DateTime in ISO format
     */
    static function dateTimeFromAD($dateAD)
    {
        return preg_replace("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})\.0Z/", '$1-$2-$3 $4:$5:$6', $dateAD);
    }

    /**
     * Return an array containing the days between two dates
     *
     * @param string $from The begin date
     * @param string $to   The end date
     *
     * @return array
     */
    static function getDays($from, $to)
    {
        $count_between = self::daysRelative($from . '00:00:00', $to . '00:00:00');
        $days          = [$from];

        for ($i = 1; $i < $count_between; $i++) {
            $days[] = self::date("+ $i DAYS", $from);
        }

        if (!in_array($to, $days)) {
            $days[] = $to;
        }

        return $days;
    }

    /**
     * Transforms a lunar date to gregorian, like described by GIE Sesam-Vitale
     *
     * @param string $date Lunar date
     *
     * @return string Gregorian date
     */
    public static function lunarToGregorian($date)
    {
        [$year, $month, $day] = explode("-", $date);
        if ($month > 12) {
            $month = 12;
        }

        $last_day = static::daysInMonthGregorian((int)$month, (int)$year);
        if ($day > $last_day) {
            $day = $last_day;
        }

        return "$year-$month-$day";
    }

    /**
     * Return the number of days in the gregorian calendar for the month.
     * Month must be an in between 1 and 12 (jan -> dec).
     */
    public static function daysInMonthGregorian(int $month, int $year): int
    {
        // For february check bisextile year
        if ($month === 2) {
            // Check if year is bisextile. The year must either :
            // - be divisible by 4 but NOT by 100
            // - be divisible by 400
            if ((($year % 4 === 0) && ($year % 100 !== 0)) || ($year % 400 === 0)) {
                return 29;
            }

            return 28;
        }

        return (($month - 1) % 7 % 2) ? 30 : 31;
    }

    /**
     * @param int    $duration
     * @param string $from_unit
     * @param string $to_unit
     *
     * @return int|string
     */
    static function getHumanReadableDuration($duration, $from_unit = 'min', $to_unit = 'h')
    {
        switch ($to_unit) {
            case 'h':
                return self::convertToHours($duration, $from_unit);

            case 'd':
                return self::convertToDays($duration, $from_unit);

            default:
                return $duration;
        }
    }

    /**
     * @param int    $duration
     * @param string $from_unit
     *
     * @return int|string
     */
    static function convertToHours($duration, $from_unit = 'min')
    {
        switch ($from_unit) {
            case 'min':
                if ($duration < 60) {
                    return "{$duration} min";
                }

                $hours = str_pad(intval($duration / 60), 2, '0', STR_PAD_LEFT);
                $min   = str_pad($duration % 60, 2, '0', STR_PAD_LEFT);

                return "{$hours} h {$min}";
            case 'h':
                $duration *= 60;

                $hours = str_pad(intval($duration / 60), 2, '0', STR_PAD_LEFT);
                $min   = str_pad($duration % 60, 2, '0', STR_PAD_LEFT);

                return "{$hours} h {$min}";

            default:
                return $duration;
        }
    }

    static function convertToDays($duration, $from_unit = 'min')
    {
        switch ($from_unit) {
            case 'min':
                if ($duration < 1440) {
                    return self::convertToHours($duration, $from_unit);
                }

                $days = intval($duration / 1440);
                $min  = self::convertToHours($duration % 1440, $from_unit);

                return "{$days} j {$min}";

            case 'h':
                if ($duration < 24) {
                    return "{$duration} h";
                }

                $days  = intval($duration / 60);
                $hours = $duration % 60;

                return "{$days} j {$hours} h";

            default:
                return $duration;
        }
    }

    /**
     * Compute real relative achieved gregorian durations in years and months
     *
     * @param string $from      Starting time
     * @param string $to        Ending time, now if null
     * @param int    $min_count The minimum count to reach the upper unit, 2 if undefined
     *
     * @return array[int] Number of years and months
     *
     */
    static function achievedDurations($from, $to = null, $min_count = 2)
    {
        $achieved = [
            "year"  => "??",
            "month" => "??",
            "week"  => "??",
            "day"   => "??",
        ];

        if ($from == "0000-00-00" || !$from) {
            return $achieved;
        }

        if (!$to) {
            $to = CMbDT::date();
        }

        [$yf, $mf, $df] = explode("-", $from);
        [$yt, $mt, $dt] = explode("-", $to);

        $yf = intval($yf);
        $mf = intval($mf);
        $df = intval($df);

        $yt = intval($yt);
        $mt = intval($mt);
        $dt = intval($dt);

        $achieved["day"] = self::daysRelative($from, $to);

        $achieved["week"] = intval($achieved["day"] / 7);

        $achieved["month"] = 12 * ($yt - $yf) + ($mt - $mf);
        if ($mt == $mf && $dt < $df) {
            $achieved["month"]--;
        }

        $achieved["year"] = intval($achieved["month"] / 12);

        foreach ($achieved as $_unit => $_count) {
            if (abs($_count) >= $min_count) {
                $achieved["locale"] = $_count . " " . CAppUI::tr($_unit . (abs($_count) > 1 ? "s" : ""));

                return $achieved;
            }
        }

        if ($achieved["day"] < 0) {
            $achieved["locale"] = $achieved["day"] . " " . CAppUI::tr("day" . ($achieved["day"] < -1 ? "s" : ""));

            return $achieved;
        }

        $achieved["locale"] = CAppUI::tr("Day-one");

        return $achieved;
    }

    /**
     * @param string|null $from      - the first date
     * @param string|null $to        - the second date
     * @param int         $min_count - the value must be = or > than an int to be displayed (e.g. 2 years but not 1
     *                               year if int = 2)
     *
     * @return array
     * @throws Exception
     *
     * now returns achievedDurations() to handle lunarDate and to return the correct date
     */
    static function achievedDurationsDT($from, $to = null, $min_count = 2)
    {
        if (CMbDT::isLunarDate($from)) {
            $from = CMbDT::lunarToGregorian($from);
        }

        if ($to && CMbDT::isLunarDate($to)) {
            $to = CMbDT::lunarToGregorian($to);
        }

        $from = new DateTime($from);
        $to   = ($to) ? new DateTime($to) : new DateTime();

        if ($from > $to) {
            throw new Exception("The first date must be smaller than the second");
        }

        $diff   = $from->diff($to);
        $months = $diff->y * 12 + $diff->m;
        $weeks  = floor($diff->days / 7);

        $achieved = [
            "year"   => $diff->y,
            "month"  => $months,
            "week"   => (int)$weeks,
            "day"    => $diff->days,
            "locale" => CAppUI::tr("Day-one"),
        ];

        foreach ($achieved as $_unit => $_count) {
            if ($_count >= $min_count) {
                $achieved["locale"] = $_count . " " . CAppUI::tr($_unit . (($_count > 1) ? "s" : ""));

                return $achieved;
            }
        }

        return $achieved;
    }


    /**
     * Compute duration between two date time
     * Seems to return 10 more seconds
     *
     * @param string $from      From time (datetime)
     * @param string $to        To time, now if null (datetime)
     * @param int    $min_count return only positive units
     *
     * @return array array("unit" => string, "count" => int)
     */
    static function duration($from, $to = null, $min_count = 0)
    {
        $duration = [];
        if (!$from) {
            return null;
        }

        if (!$to) {
            $to = CMbDT::dateTime();
        }

        $diff = strtotime($to) - strtotime($from);
        // Find the best unit
        foreach (self::SECS_PER as $unit => $secs) {
            if (abs($diff / $secs) > $min_count) {
                $duration[$unit] = intval($diff / $secs);
                $diff            = (int) ($diff / $secs) + ($diff % $secs);
            }
        }

        return $duration;
    }

    static function durationSecond($from, $to = null)
    {
        if (!$from) {
            return null;
        }

        if (!$to) {
            $to = CMbDT::dateTime();
        }

        return strtotime($to) - strtotime($from);
    }

    /**
     * Transform date which are like this format : 24/08/1995
     *
     * @param string $from date
     *
     * @return null|string
     */
    static function transformDateFormSlash($from)
    {
        if (strlen($from ?? '') != 10) {
            return null;
        }

        $day   = substr($from, 0, 2);
        $month = substr($from, 3, 2);
        $year  = substr($from, 6, 4);

        return "$year-$month-$day";
    }

    /**
     * Compute duration between two date time
     *
     * @param string $from From time (datetime)
     * @param string $to   To time, now if null (datetime)
     *
     * @return string The time
     */
    static function durationTime($from, $to = null)
    {
        $duration = self::duration($from, $to);
        if (!$duration) {
            return null;
        }
        $second = $duration["second"] % 60;
        $minute = ($duration["minute"] + floor($duration["second"] / 60));
        $hour   = $duration["day"] * 24 + $duration["hour"] + floor($minute / 60);

        return str_pad($hour, 2, 0, STR_PAD_LEFT) . ":" . str_pad(($minute % 60), 2, 0, STR_PAD_LEFT) . ":" . str_pad(
                $second,
                2,
                0,
                STR_PAD_LEFT
            );
    }

    /**
     * Compute user friendly approximative relative duration between two datetimes
     *
     * @param string $from      From ISO datetime
     * @param string $to        To ISO datetime, now if null
     * @param int    $min_count The minimum count to reach the upper unit, 2 if undefined
     *
     * @return array array("count" => int, "unit" => string, "locale", string)
     */
    static function relativeDuration($from, $to = null, $min_count = 2)
    {
        if (!$from) {
            return null;
        }

        if (!$to) {
            $to = CMbDT::dateTime();
        }

        if (CMbDT::isLunarDate($from)) {
            $from = CMbDT::lunarToGregorian($from);
        }
        if ($to && CMbDT::isLunarDate($to)) {
            $to = CMbDT::lunarToGregorian($from);
        }

        return self::friendlyDuration(strtotime($to) - strtotime($from), $min_count);
    }

    /**
     * Compute a user friendly approximative duration from a seconds amount
     *
     * @param int $seconds   The amount of seconds
     * @param int $min_count The minimum count to reach the upper unit, 2 if undefined
     *
     * @return array array("count" => int, "unit" => string, "locale", string)
     */
    static function friendlyDuration($seconds, $min_count = 2)
    {
        $unit  = null;
        $count = null;

        // Find the best unit
        foreach (self::SECS_PER as $unit => $secs) {
            $count = abs(intval($seconds / $secs));
            if ($count >= $min_count) {
                break;
            }
        }

        return [
            "unit"   => $unit,
            "count"  => $count,
            "locale" => $count . " " . CAppUI::tr($unit . ($count > 1 ? "s" : "")),
        ];
    }

    /**
     * Know if date is bisextiles
     *
     * @param string $date Datetime
     *
     * @return bool
     */
    static function isYearBisextilles($date)
    {
        $year = self::yearNumber($date);

        return ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0));
    }

    /**
     * Tell whether a given date is valid
     *
     * @param string $date The date to string format (Y-m-d)
     *
     * @return bool
     */
    public static function isDateValid(string $date): bool
    {
        return self::isDateValidWithFormat($date, 'Y-m-d');
    }

    /**
     * Tell whether a given datetime is valid
     *
     * @param string $datetime The datime to string format (Y-m-d H:i:s)
     *
     * @return bool
     */
    public static function isDatetimeValid(string $datetime): bool
    {
        return self::isDateValidWithFormat($datetime, 'Y-m-d H:i:s');
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    private static function isDateValidWithFormat(string $date, string $format): bool
    {
        $datetime = DateTimeImmutable::createFromFormat($format, $date);

        if ($datetime === false) {
            return false;
        }

        // In order to trigger getLastErrors
        $datetime->format($format);
        $last_errors = $datetime::getLastErrors();

        if ($last_errors['warning_count'] || $last_errors['error_count']) {
            return false;
        }

        return true;
    }

    /**
     * Check if given datetime is invalid according to leap years
     *
     * @param string $datetime The datetime to string format (Y-m-d H:i:s)
     *
     * @return bool
     */
    public static function isValidLeapDatetime(string $datetime): bool
    {
        if (!self::isYearBisextilles($datetime)) {
            [$date, $time] = explode(' ', $datetime);
            [$year, $month, $day] = explode('-', $date);

            if ($month === '02' && $day === '29') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the day number for a given datetime
     *
     * @param string $date Datetime
     *
     * @return int The month number
     */
    static function dayNumber($date)
    {
        return intval(CMbDT::format($date, "%d"));
    }

    /**
     * Get the month number for a given datetime
     *
     * @param string $date Datetime
     *
     * @return int The month number
     */
    static function monthNumber($date)
    {
        return intval(CMbDT::format($date, "%m"));
    }

    /**
     * Get the week number for a given datetime
     *
     * @param string $date Datetime
     *
     * @return int The week number
     */
    static function weekNumber($date)
    {
        return intval(date("W", strtotime($date)));
    }

    /**
     * Get the year for a given datetime
     *
     * @param string $date Datetime
     *
     * @return int The year
     */
    static function yearNumber($date)
    {
        return intval(date("Y", strtotime($date)));
    }

    /**
     * Get the date from the week number and the year
     *
     * @param string $week Week number
     * @param string $year Year
     *
     * @return array
     */
    static function dateFromWeekNumber($week, $year)
    {
        $dto          = new DateTime();
        $ret['start'] = $dto->setISODate($year, $week)->format('Y-m-d');
        $ret['end']   = $dto->modify('+6 days')->format('Y-m-d');

        return $ret;
    }

    /**
     * Get the week count between two dates
     *
     * @param string $from Start date
     * @param string $to   End date
     *
     * @return int The week count
     */
    static function weekCount($from, $to)
    {
        $first  = DateTime::createFromFormat('Y-m-d', $from);
        $second = DateTime::createFromFormat('Y-m-d', $to);

        return floor($first->diff($second)->days / 7);
    }

    /**
     * Get the week number in the month
     *
     * @param string $date Date
     *
     * @return int The week number
     */
    static function weekNumberInMonth($date)
    {
        $month       = self::monthNumber($date);
        $week_number = 0;

        do {
            $date   = CMbDT::date("-1 WEEK", $date);
            $_month = self::monthNumber($date);
            $week_number++;
        } while ($_month == $month);

        return $week_number;
    }

    /**
     * Return date ranges for working days between two dates
     *
     * @param string $from Start date
     * @param string $to   End date
     *
     * @return array
     */
    static function getWorkingDays($from, $to)
    {
        $result = [];
        if (CMbDT::format($from, '%w') != 1) {
            $from = CMbDT::date('monday this week', $from);
        }
        if (CMbDT::format($to, '%w') != 5) {
            $to = CMbDT::date('friday this week', $to);
        }

        $week_count = self::weekCount($from, $to);
        for ($i = 0; $i < $week_count; $i++) {
            $result[] = ['start' => $from, 'end' => CMbDT::date('+4 days', $from)];
            $from     = CMbDT::date('+7 days', $from);
        }

        return $result;
    }

    /**
     * Get the next working day from the given date
     *
     * @param string $date The date
     *
     * @return string
     */
    public static function getNextWorkingDay($date = null)
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        while (!self::isWorkingDay($date)) {
            $date = CMbDT::date('+1 DAY', $date);
        }

        return $date;
    }

    /**
     * Get complete days between two dates
     *
     * @param string $from             Start date
     * @param string $to               End date
     * @param bool   $include_weekends Include weekend days
     * @param bool   $include_holidays Include non opened days
     *
     * @return array
     */
    static function getDaysBetween($from, $to, $include_weekends = true, $include_holidays = true)
    {
        if ($to < $from || $from == $to) {
            return [];
        }

        $from_date = CMbDT::date(null, $from);
        $to_date   = CMbDT::date(null, $to);

        if ($from_date == $to_date || CMbDT::date('+1 day', $from_date) == $to_date) {
            return [];
        }

        $_date    = $from_date;
        $_to_date = CMbDT::date('-1 day', $to_date);

        $dates = [];

        do {
            $_date = CMbDT::date('+1 day', $_date);

            $_add = true;

            if (!$include_weekends && static::isWeekend($_date)) {
                $_add = false;
            }

            if (!$include_holidays && static::isHoliday($_date)) {
                $_add = false;
            }

            if ($_add) {
                $dates[] = $_date;
            }
        } while ($_date < $_to_date);

        return $dates;
    }

    /**
     * Get complete non opened days between two dates
     *
     * @param string $from Start date
     * @param string $to   End date
     *
     * @return array
     */
    static function getOpenDaysBetween($from, $to)
    {
        return static::getDaysBetween($from, $to, false, false);
    }

    /**
     * Give a Dirac hash of given datetime
     *
     * @param string $period   One of minute, hour, day, week, month or year
     * @param string $datetime Datetime
     *
     * @return string|null Hash
     */
    static function dirac($period, $datetime)
    {
        switch ($period) {
            case "min":
                return CMbDT::format($datetime, "%Y-%m-%d %H:%M:00");

            case "hour":
                return CMbDT::format($datetime, "%Y-%m-%d %H:00:00");

            case "day":
                return CMbDT::format($datetime, "%Y-%m-%d 00:00:00");

            case "week":
                return CMbDT::transform("last sunday +1 day", $datetime, "%Y-%m-%d 00:00:00");

            case "month":
                return CMbDT::format($datetime, "%Y-%m-01 00:00:00");

            case "year":
                return CMbDT::format($datetime, "%Y-01-01 00:00:00");

            default:
                trigger_error("Can't make a Dirac hash for unknown '$period' period", E_USER_WARNING);
        }

        return null;
    }

    /**
     * Give a position to a datetime relative to a reference
     *
     * @param string $datetime  Datetime
     * @param string $reference Reference
     * @param string $period    One of 1hour, 6hours, 1day
     *
     * @return float|null
     */
    static function position($datetime, $reference, $period)
    {
        $diff = strtotime($datetime) - strtotime($reference);

        switch ($period) {
            case "1hour":
                return $diff / CMbDT::SECS_PER_HOUR;

            case "6hours":
                return $diff / (CMbDT::SECS_PER_HOUR * 6);

            case "2hours":
                return $diff / (CMbDT::SECS_PER_HOUR * 2);

            case "3hours":
                return $diff / (CMbDT::SECS_PER_HOUR * 3);

            case "1day":
                return $diff / CMbDT::SECS_PER_DAY;

            default:
                trigger_error("Can't proceed for unknown '$period' period", E_USER_WARNING);
        }

        return null;
    }

    /**
     * Turn a datetime to its UTC timestamp equivalent
     *
     * @param string $datetime Datetime
     *
     * @return int
     */
    static function toUTCTimestamp($datetime)
    {
        static $cache = [];
        static $default_timezone;

        if (isset($cache[$datetime])) {
            return $cache[$datetime];
        }

        if (!$default_timezone) {
            $default_timezone = date_default_timezone_get();
        }

        // Temporary change timezone to UTC
        date_default_timezone_set("UTC");
        $utc = strtotime($datetime) * 1000; // in ms;
        date_default_timezone_set($default_timezone);

        return $cache[$datetime] = $utc;
    }

    /**
     * Turn a datetime to its UTC timestamp equivalent
     *
     * @param string $datetime Datetime
     *
     * @return int
     */
    static function toTimestamp($datetime)
    {
        static $cache = [];

        if (isset($cache[$datetime])) {
            return $cache[$datetime];
        }

        $utc = strtotime($datetime) * 1000; // in ms;

        return $cache[$datetime] = $utc;
    }

    /**
     * Return an array of dates non worked
     *
     * @param string  $date          date to check (used to analyse the year)
     * @param bool    $includeRegion add region holidays (cantons, regions)
     * @param CGroups $group         group used for the check, null = current
     * @param string  $code_pays     country code
     *
     * @return array
     */
    static function getHolidays($date = null, $includeRegion = true, $group = null, $code_pays = null)
    {
        static $cache = [
            '1' => [],
            '2' => [],
        ];

        $calendar = [];
        $date     = ($date) ?: CMbDT::date();

        $year      = CMbDT::format($date, "%Y");
        $next_year = (int)$year + 1;

        $group = ($group && $group instanceof CGroups) ? $group : CGroups::loadCurrent();

        $code_pays = ($code_pays) ?: CAppUI::conf("ref_pays");

        $cache_key = implode('-', [$date, ($includeRegion) ? '1' : '0', $group->_guid, $code_pays]);

        if (isset($cache[$cache_key]) && $cache[$cache_key]) {
            return $cache[$cache_key];
        }

        switch ($code_pays) {
            // France
            case '1':
                $paques = CMbDT::getEasterDate($date);

                // Static
                $calendar["$year-01-01"]      = CAppUI::tr("common-new Year s Day");   // Jour de l'an
                $calendar["$year-05-01"]      = CAppUI::tr("common-Labor day");        // Fête du travail
                $calendar["$year-05-08"]      = CAppUI::tr("common-Victory of 1945");  // Victoire de 1945
                $calendar["$year-07-14"]      = CAppUI::tr("common-National holiday"); // Fête nationale
                $calendar["$year-08-15"]      = CAppUI::tr("common-Assumption");       // Assomption
                $calendar["$year-11-01"]      = CAppUI::tr("common-Toussaint");        // Toussaint
                $calendar["$year-11-11"]      = CAppUI::tr("common-Armistice 1918");   // Armistice 1918
                $calendar["$year-12-25"]      = CAppUI::tr("common-Christmas");        // Noël
                $calendar["$next_year-01-01"] = CAppUI::tr("common-new Year s Day");   // Jour de l'an

                // Dynamic
                $calendar[CMbDT::date("+1 DAY", $paques)]   = CAppUI::tr(
                    "common-Easter Monday"
                );        // Lundi de Pâques
                $calendar[CMbDT::date("+39 DAYS", $paques)] = CAppUI::tr(
                    "common-Ascension Thursday"
                );   // Jeudi de l'Ascension
                $calendar[CMbDT::date("+50 DAYS", $paques)] = CAppUI::tr(
                    "common-Monday of Pentecost"
                );  // Lundi de Pentecôte
                break;

            default:
                return $cache[$cache_key] = $calendar;
        }

        if ($includeRegion) {
            $holidaysSub = self::getCpHolidays($date, $group, $code_pays); //récupération des régions
            $calendar    = array_merge($calendar, $holidaysSub);
        }

        ksort($calendar);

        return $cache[$cache_key] = $calendar;
    }

    /**
     * Get the holidays by region
     *
     * @param string       $date  date to check
     * @param null|CGroups $group group, null = current
     * @param string       $pays  country code
     * @param string       $cp    postal code
     *
     * @return array
     */
    static function getCpHolidays($date, $group = null, $pays = null, $cp = null)
    {
        $subdivisionHoliday = [];
        $pays               = ($pays) ?: CAppUI::conf("ref_pays");

        //no group, load current
        if (!$group) {
            $group = CGroups::loadCurrent();
        }

        $cp = ($cp) ?: $group->cp;

        //no CP, abord
        if (!$group->cp) {
            return $subdivisionHoliday;
        }

        $year   = CMbDT::format($date, "%Y");
        $paques = CMbDT::getEasterDate($date);

        switch ($pays) {
            // France
            case '1':
                return $subdivisionHoliday;
                break;
            default:
        }

        ksort($subdivisionHoliday);

        return $subdivisionHoliday;
    }

    /**
     * Check if given date is a weekend day
     *
     * @param string $date Date
     *
     * @return bool
     */
    static function isWeekend($date = null)
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        return (!in_array(CMbDT::format($date, '%w'), range(1, 5)));
    }

    /**
     * Check if the given date is an holyday
     *
     * @param string  $date          date to check (used to analyse the year)
     * @param bool    $includeRegion add region holidays (cantons, regions)
     * @param CGroups $group         group used for the check, null = current
     *
     * @return bool
     */
    static function isHoliday($date, $includeRegion = true, $group = null)
    {
        $holidays = self::getHolidays($date, $includeRegion, $group);

        return array_key_exists($date, $holidays);
    }

    /**
     * Check if the given date if worked (not a weekend day or a holy day)
     *
     * @param string $date The date
     *
     * @return bool
     */
    public static function isWorkingDay($date)
    {
        return !self::isHoliday($date) && !self::isWeekend($date);
    }

    /**
     * Return value formatted
     *
     * @param string $value the value
     *
     * @return string
     */
    static function formatDuration($value)
    {
        if (!$value) {
            return null;
        }
        $pattern = "/^(\d+):(\d+)/";
        if (!preg_match($pattern, $value, $matches)) {
            return null;
        }

        return sprintf("%dh%02d", $matches[1], $matches[2]);
    }

    /**
     * Convert a datetime to its UTC value (ISO8601)
     *
     * @param string $date Date
     *
     * @return string
     */
    static function dateTimeToUTC($date)
    {
        try {
            $datetime = new DateTime($date ?? '');

            return $datetime->format(DateTime::ATOM);
        } catch (Exception $e) {
            return $date;
        }
    }

    /**
     * @param string|int $start_date Start date for random or start timestamp
     * @param string|int $end_date   End date for random or end timestamp
     * @param string     $mask       Mask to use for the result
     *
     * @return false|string
     */
    static function getRandomDate($start_date, $end_date, $mask = 'Y-m-d H:i:s')
    {
        // Convert to timetamps
        $min = (is_int($start_date)) ? $start_date : strtotime($start_date);
        $max = (is_int($end_date)) ? $end_date : strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Change the timestamp using the server timezone
        //$time_zone = date("Z");
        //$val       -= $time_zone;

        // Convert back to desired date format
        return date($mask, $val);
    }

    /**
     * @param string $format
     * @param string $time
     * @param string $timezone
     *
     * @return DateTime|bool
     */
    static function getDateTimeFromFormat($format, $time, $timezone = 'Europe/Paris')
    {
        $date = DateTime::createFromFormat($format, $time);
        if ($date && $timezone) {
            $date->setTimezone(new DateTimeZone('Europe/Paris'));
        }

        return $date;
    }

    static $days_name = [
        1  => [
            'Jour de l\'an',
            'Basile',
            'Geneviève',
            'Odilon',
            'Edouard',
            'Epiphanie',
            'Raymond',
            'Lucien',
            'Alix',
            'Guillaume',
            'Paulin',
            'Tatiana',
            'Yvette',
            'Nina',
            'Rémi',
            'Marcel',
            'Roseline',
            'Prisca',
            'Marius',
            'Sébastien',
            'Agnès',
            'Vincent',
            'Barnard',
            'Fr. de Sales',
            'Conv. S. Paul',
            'Paule',
            'Angèle',
            'Th. d\'Aquin, Maureen',
            'Gildas',
            'Martine',
            'Marcelle',
        ],
        2  => [
            'Ella',
            'Chandeleur',
            'Blaise',
            'Véronique',
            'Agathe',
            'Gaston',
            'Eugénie',
            'Jacqueline',
            'Apolline',
            'Arnaud',
            'N-D Lourdes',
            'Félix',
            'Béatrice',
            'Valentin',
            'Claude',
            'Julienne',
            'Alexis',
            'Bernadette',
            'Gabin',
            'Aimée',
            'P. Damien',
            'Isabelle',
            'Lazare',
            'Modeste',
            'Roméo',
            'Nestor',
            'Honorine',
            'Romain',
            'August',
        ],
        3  => [
            "Aubin",
            "Charles le B.",
            "Guénolé",
            "Casimir",
            "Olive",
            "Colette",
            "Félicité",
            "Jean de Dieu",
            "Françoise",
            "Vivien",
            "Rosine",
            "Justine",
            "Rodrigue",
            "Mathilde",
            "Louise",
            "Bénédicte",
            "Patrice",
            "Cyrille",
            "Joseph",
            "Alessandra",
            "Clémence",
            "Léa",
            "Victorien",
            "Catherine De Suède",
            "Humbert",
            "Larissa",
            "Habib",
            "Gontran",
            "Gwladys",
            "Amédée",
            "Benjamin",
        ],
        4  => [
            "Lundi de Pâques",
            "Sandrine",
            "Richard",
            "Isidore",
            "Irène",
            "Marcellin",
            "Jean-Baptiste de la Salle",
            "Julie",
            "Gautier",
            "Fulbert",
            "Stanislas",
            "Jules",
            "Ida",
            "Maxime",
            "Paterne16",
            "Benoît-Joseph",
            "Anicet",
            "Parfait",
            "Emma",
            "Odette",
            "Anselme",
            "Alexandre",
            "Georges",
            "Fidèle",
            "Marc",
            "Alida",
            "Zita",
            "Jour du Souv.",
            "Cath. de Si",
            "Robert",
        ],
        5  => [
            "Fête du Travail",
            "Boris",
            "Phil., Jacq.",
            "Sylvain",
            "Judith",
            "Prudence19",
            "Gisèle",
            "Victoire 1945",
            "Ascension",
            "Solange",
            "Estelle",
            "Achille",
            "Rolande",
            "Matthias",
            "Denise",
            "Honoré",
            "Pascal",
            "Éric",
            "Yves",
            "Bernardin",
            "Constantin",
            "Emile",
            "Didier",
            "Donatien",
            "Sophie",
            "Fête des Mères",
            "Augustin",
            "Germain",
            "Aymar",
            "Ferdinand",
            "Visitation",
        ],
        6  => [
            "Justin",
            "Blandine",
            "Kévin",
            "Clotilde",
            "Igor",
            "Norbert",
            "Gilbert",
            "Médard",
            "Diane",
            "Landry",
            "Barnabé",
            "Guy",
            "AntoindP",
            "Elisée",
            "Germaine",
            "Aurélien",
            "Hervé",
            "Léonce",
            "Romuald",
            "Fête des Pères",
            "Rodolphe",
            "Alban",
            "Audrey",
            "Jean-Baptiste",
            "Prosper",
            "Anthelme",
            "Fernand",
            "Irénée",
            "Pierre, Paul",
            "Martial",
        ],
        7  => [
            "Thierry",
            "Martinien",
            "Thomas",
            "Florent",
            "Antoine",
            "Mariette",
            "Raoul",
            "Thibault",
            "Amandine",
            "Ulrich",
            "Benoît",
            "Olivier",
            "Henri, Joël",
            "Fête Nationale",
            "Donald",
            "N-Mt-Carmel",
            "Charlotte",
            "Frédéric",
            "Arsène",
            "Marina",
            "Victor",
            "Marie-Mad",
            "Brigitte",
            "Christine",
            "Jacques",
            "Anne,Joach",
            "Nathalie",
            "Samson",
            "Marthe",
            "Juliette",
            "IgnacdL",
        ],
        8  => [
            "Alphonse",
            "Julien-Eym",
            "Lydie",
            "Jean-Marie, Vianney",
            "Abel",
            "Transfiguration",
            "Gaétan",
            "Dominique",
            "Amour",
            "Laurent",
            "Claire",
            "Clarisse",
            "Hippolyte",
            "Evrard",
            "Assomption",
            "Armel",
            "Hyacinthe",
            "Hélène",
            "Jean-Eudes",
            "Bernard",
            "Christophe",
            "Fabrice",
            "RosdL",
            "Barthélemy",
            "Louis",
            "Natacha",
            "Monique",
            "Augustin",
            "Sabine",
            "Fiacre",
            "Aristide",
        ],
        9  => [
            "Gilles",
            "Ingrid",
            "Grégoire",
            "Rosalie",
            "Raïssa",
            "Bertrand",
            "Reine",
            "Nativité N.-D",
            "Alain",
            "Inès",
            "Adelphe",
            "Apollinaire",
            "Aimé",
            "LCroix",
            "Roland",
            "Edith",
            "Renaud",
            "Nadège",
            "Émilie",
            "Davy",
            "Matthieu",
            "Maurice",
            "Constant",
            "Thècle",
            "Hermann",
            "Côme, Damien",
            "Vinc. dP",
            "Venceslas",
            "Michel",
            "Jérôme",
        ],
        10 => [
            "Thér.de l'E",
            "Léger",
            "Gérard",
            "Fr. d'Assise",
            "Fleur",
            "Bruno",
            "Serge",
            "Pélagie",
            "Denis",
            "Ghislain",
            "Firmin",
            "Wilfried",
            "Géraud",
            "Juste",
            "Thér. d'Avila",
            "Edwige",
            "Baudoin",
            "Luc",
            "René",
            "Adeline",
            "Céline",
            "Elodie",
            "JeadC.",
            "Florentin",
            "Crépin",
            "Dimitri",
            "Emeline",
            "Simon, Jude",
            "Narcisse",
            "Bienvenue",
            "Quentin",
        ],
        11 => [
            "Toussaint",
            "Défunt",
            "Hubert",
            "Charles",
            "Sylvie",
            "Bertille",
            "Carine",
            "Geoffroy",
            "Théodore",
            "Léon",
            "Armistice 1918",
            "Christian",
            "Brice",
            "Sidoine",
            "Albert",
            "Marguerite",
            "Elisabeth",
            "Aude",
            "Tanguy",
            "Edmond",
            "Prés. Marie",
            "Cécile",
            "Christ Roi",
            "Flora",
            "Cath. L.",
            "Delphine",
            "Séverin",
            "Jacq. de la M.",
            "Saturnin",
            "Avent",
        ],
        12 => [
            "Florence",
            "Viviane",
            "François-Xavier",
            "Barbara",
            "Gérald",
            "Nicolas",
            "Ambroise",
            "Imm. Conception",
            "Guadalupe",
            "Romaric",
            "Daniel",
            "Chantal",
            "Lucie",
            "Odile",
            "Ninon",
            "Alice",
            "Gaël",
            "Gatien",
            "Urbain",
            "Théophile",
            "PierrCan.",
            "Fr.-Xavière",
            "Armand",
            "Adèle",
            "Noël",
            "Etienne",
            "Jean",
            "Innocents",
            "David",
            "Roger",
            "Sylvestre",
        ],
    ];
}
