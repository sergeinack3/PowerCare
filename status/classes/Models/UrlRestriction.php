<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

/**
 * URL restriction
 */
class UrlRestriction extends Prerequisite
{
    public $url         = "";
    public $description = "";

    /**
     * Get the last HTTP code from a requested URL (follow redirection)
     *
     * @param string $url URL to request
     *
     * @return string
     */
    private function getHTTPResponseCode($url)
    {
        ini_set("default_socket_timeout", 2);
        @file_get_contents($url);
        $headers = $http_response_header;

        $response = false;
        if (is_array($headers)) {
            foreach ($headers as $header) {
                if (substr($header, 0, 4) == "HTTP") {
                    $response = explode(" ", $header);
                    $response = $response[1];
                }
            }
        }

        return $response;
    }

    /**
     * @see parent::check()
     */
    function check($strict = true)
    {
        $code = substr($this->getHTTPResponseCode($this->url), 0, 3);

        return $code == 403;
    }

    /**
     * @see parent::getAll()
     */
    function getAll()
    {
        $http = "http://";
        if (array_key_exists("HTTPS", $_SERVER)) {
            $http = "https://";
        }

        $address = $_SERVER['SERVER_ADDR'];

        // IPv6 address
        if (strpos($address, ":") !== false) {
            $address = "[$address]";
        }

        $url = $http . dirname(dirname($address . $_SERVER['REQUEST_URI']));

        $restrictions = [];

        $restriction              = new self;
        $restriction->url         = "$url/files";
        $restriction->description = "Répertoire des fichiers utilisateur";
        $restrictions[]           = $restriction;

        $restriction              = new self;
        $restriction->url         = "$url/tmp";
        $restriction->description = "Répertoire des fichiers temporaires";
        $restrictions[]           = $restriction;

        $restriction              = new self;
        $restriction->url         = "$url/tmp/mb-log.html";
        $restriction->description = "Journal d'erreurs système";
        $restrictions[]           = $restriction;

        return $restrictions;
    }
}
