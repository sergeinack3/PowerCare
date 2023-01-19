<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\FieldSpecs\CPasswordSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\PasswordSpecs\PasswordSpecBuilder;
use Ox\Mediboard\System\Keys\Key;
use phpseclib\Crypt\AES as AESCompat;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\DES as DESCompat;
use phpseclib\Crypt\Rijndael as RijndaelCompat;
use phpseclib\Crypt\RSA;
use phpseclib\Crypt\TripleDES as TripleDESCompat;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\DES;
use phpseclib3\Crypt\Hash;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Crypt\TripleDES;
use phpseclib3\File\X509;
use phpseclib3\Math\BigInteger;

class CMbServer
{
    /**
     * Retrieve a server value from multiple sources
     *
     * @param string $key Value key
     *
     * @return string|null
     */
    public static function getServerVar(string $key): ?string
    {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (getenv($key)) {
            return getenv($key);
        }

        if (function_exists('apache_getenv') && apache_getenv($key, true)) {
            return apache_getenv($key, true);
        }

        return null;
    }

    /**
     * Get browser remote IPs using most of available methods
     *
     * @param bool $remove_scope_id Remove the Scope ID of the IP addresses
     *
     * @return array Array with proxy, client and remote keys as IP addresses
     */
    public static function getRemoteAddress(bool $remove_scope_id = true): array
    {
        $address = [
            "proxy"  => null,
            "client" => null,
            "remote" => null,
        ];

        $address["client"] = ($client = self::getServerVar(
            "HTTP_CLIENT_IP"
        )) ? $client : self::getServerVar("REMOTE_ADDR");
        $address["remote"] = $address["client"];

        $forwarded = [
            "HTTP_X_FORWARDED_FOR",
            "HTTP_FORWARDED_FOR",
            "HTTP_X_FORWARDED",
            "HTTP_FORWARDED",
            "HTTP_FORWARDED_FOR_IP",
            "X_FORWARDED_FOR",
            "FORWARDED_FOR",
            "X_FORWARDED",
            "FORWARDED",
            "FORWARDED_FOR_IP",
        ];

        foreach ($forwarded as $name) {
            if ($client = self::getServerVar($name)) {
                break;
            }
        }

        if ($client) {
            $address["proxy"]  = $address["client"];
            $address["client"] = $client;
        }

        // To handle weird IPs sent by iPhones, in the form "10.10.10.10, 10.10.10.10"
        $proxy  = (array_key_exists('proxy', $address) && is_string($address['proxy']))
            ? explode(',', $address['proxy']) : [];
        $client = (array_key_exists('client', $address) && is_string($address['client']))
            ? explode(',', $address['client']) : [];
        $remote = (array_key_exists('remote', $address) && is_string($address['remote']))
            ? explode(',', $address['remote']) : [];

        $address["proxy"]  = reset($proxy);
        $address["client"] = reset($client);
        $address["remote"] = reset($remote);

        if ($remove_scope_id) {
            foreach ($address as $_type => $_address) {
                if ($_address && ($pos = strpos($_address, "%"))) {
                    $address[$_type] = substr($_address, 0, $pos);
                }
            }
        }

        return $address;
    }

    /**
     * Check response time from a web server
     *
     * @param string $url  Server URL
     * @param string $port Server port
     *
     * @return int Response time in milliseconds
     */
    public static function getUrlResponseTime(string $url, string $port): int
    {
        $parse_url = parse_url($url);
        if (isset($parse_url["port"])) {
            $port = $parse_url["port"];
        }

        $url = $parse_url["host"] ?? $url;

        $start_time = microtime(true);
        $file      = @fsockopen($url, $port, $errno, $errstr, 5);
        $stop_time  = microtime(true);

        if (!$file) {
            $response_time = -1;  // Site is down
        } else {
            fclose($file);
            $response_time = ($stop_time - $start_time) * 1000;
            $response_time = floor($response_time);
        }

        return $response_time;
    }
}
