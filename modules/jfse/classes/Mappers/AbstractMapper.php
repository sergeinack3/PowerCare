<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use DateTimeImmutable;
use Exception;

/**
 * Class AbstractMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
abstract class AbstractMapper
{
    /**
     * Add an optional value to an array
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $array
     *
     * @return void
     */
    protected static function addOptionalValue(string $key, $value, array &$array): void
    {
        // Accept false values
        if ($value !== null && $value !== '' && (!is_array($value) || (count($value)))) {
            $array[$key] = $value;
        }
    }

    /**
     * @param array  $array
     * @param string $key
     *
     * @return DateTime|null
     * @throws Exception
     */
    protected static function toDateTimeOrNull(array $array, string $key): ?DateTime
    {
        $datetime = null;
        if (isset($array[$key]) && $array[$key] !== '') {
            try {
                $datetime = new DateTime($array[$key]);
            } catch (Exception $e) {
            }
        }

        return $datetime;
    }

    /**
     * @param array  $array
     * @param string $key
     *
     * @return DateTimeImmutable|null
     */
    protected static function toDateTimeImmutableOrNull(array $array, string $key): ?DateTimeImmutable
    {
        $datetime = null;
        if (isset($array[$key]) && $array[$key] !== '') {
            try {
                $datetime = new DateTimeImmutable($array[$key]);
            } catch (Exception $e) {
            }
        }

        return $datetime;
    }
}
