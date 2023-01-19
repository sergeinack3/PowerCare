<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ucum;

use DateInterval;
use Exception;
use Ox\Components\Cache\Decorators\KeySanitizerDecorator;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CAppUI;
use Ox\Core\CHTTPClient;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Ucum Client
 */
class Ucum
{
    public static CSourceHTTP $source;

    /** @var string */
    public const CODE_SYSTEM = 'https://unitsofmeasure.org';

    /**
     * @return string|object
     * @throws Exception
     */
    protected function callClient(string $url, bool $json = true)
    {
        $client = new CHTTPClient(static::getSource('Ucum')->host . $url);
        if ($json) {
            $headers = [
                "Accept: application/json",
            ];
            $client->setOption(CURLOPT_HTTPHEADER, $headers);
        }
        $client->setOption(CURLOPT_CONNECTTIMEOUT, 5);
        $client->setOption(CURLOPT_TIMEOUT, 10);
        $client->setOption(CURLOPT_RETURNTRANSFER, true);
        $client->setOption(CURLOPT_FOLLOWLOCATION, true);

        return $json ? json_decode($client->get()) : $client->get();
    }

    /**
     * Return Ucum HTTP Source
     *
     * @return CSourceHTTP
     */
    public static function getSource(string $name): CExchangeSource
    {
        return CExchangeSource::get($name, CSourceHTTP::TYPE, false, "UcumExchange");
    }

    /**
     * @return mixed|null
     *
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    public function callConversion(string $quantity, string $from, string $to, bool $cache)
    {
        if ($quantity == '') {
            $quantity = 1;
        }
        $from_key  = preg_replace('%[' . preg_quote(KeySanitizerDecorator::$reserved_characters) . ']%', '', $from);
        $to_key    = preg_replace('%[' . preg_quote(KeySanitizerDecorator::$reserved_characters) . ']%', '', $to);
        $key       = $from_key . $to_key . $quantity;
        $lay_cache = LayeredCache::getCache(LayeredCache::INNER_OUTER);

        if (!$cache || !$lay_cache->has($key)) {
            try {
                $url = CAppUI::gconf('ucum general path_conversion') . '/' . $quantity . '/from/'
                    . urlencode($from) . '/to/' . urlencode($to);
                $res = $this->callClient($url);
            } catch (Exception $e) {
                return false;
            }

            if (isset($res->UCUMWebServiceResponse->Response->ResultQuantity)) {
                $lay_cache->set($key, $res->UCUMWebServiceResponse->Response->ResultQuantity, new DateInterval('P1W'));
            } else {
                $lay_cache->set($key, CAppUI::tr('mod-ucum-wrong_unit'), new DateInterval('P1W'));
            }
        }

        return $lay_cache->get($key);
    }

    /**
     *
     * @return mixed|null
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public function callValidation(string $unit, bool $cache)
    {
        $unit_key  = preg_replace('%[' . preg_quote(KeySanitizerDecorator::$reserved_characters) . ']%', '', $unit);
        $key       = 'validation' . $unit_key;
        $lay_cache = LayeredCache::getCache(LayeredCache::INNER_OUTER);
        if (!$cache || !$lay_cache->has($key)) {
            try {
                $url = CAppUI::gconf('ucum general path_validation') . '/' . urlencode($unit);
                $res = $this->callClient($url, false);
            } catch (Exception $e) {
                return false;
            }
            $lay_cache->set($key, $res, new DateInterval('P1W'));
        }

        return $lay_cache->get($key);
    }

    /**
     * @param string|array $units
     * @param bool         $cache
     *
     * @return mixed|null
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public function callToBase($units, bool $cache)
    {
        if (is_array($units)) {
            $unit_key = '';
            foreach ($units as $unit) {
                $unit_key .= preg_replace('%[' . preg_quote(KeySanitizerDecorator::$reserved_characters) . ']%', '', $unit);
            }
            $units = implode('/', $units);
        } else {
            $unit_key = preg_replace('%[' . preg_quote(KeySanitizerDecorator::$reserved_characters) . ']%', '', $units);
        }
        $key       = 'tobase' . $unit_key;
        $lay_cache = LayeredCache::getCache(LayeredCache::INNER_OUTER);

        if (!$cache || !$lay_cache->has($key)) {
            try {
                $url = CAppUI::gconf('ucum general path_to_base') . '/' . $units;
                $res = $this->callClient($url);
            } catch (Exception $e) {
                return false;
            }

            if (isset($res->UCUMWebServiceResponse->Response->ResultBaseUnits)) {
                $lay_cache->set(
                    $key,
                    $res->UCUMWebServiceResponse->Response->ResultBaseUnits,
                    new DateInterval('P1W')
                );
            } else {
                $lay_cache->set($key, CAppUI::tr('mod-ucum-wrong_unit'), new DateInterval('P1W'));
            }
        }

        return $lay_cache->get($key);
    }
}
