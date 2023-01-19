<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use CURLFile;
use Exception;

/**
 * Create a client for manipulate the request HTTP to a site
 */
class CHTTPClient
{
    private $handle;
    public  $url;
    public  $timeout = 5;
    public  $option  = [];
    public  $header  = [];
    public  $last_information;
    public  $request_type;

    /**
     * Construct the HTTP client
     *
     * @param String $url Site URL
     *
     * @throws Exception
     */
    function __construct($url)
    {
        $this->url = $url;
        $init      = curl_init($url);
        if ($init === false) {
            throw new Exception("Initialisation impossible");
        }
        $this->handle = $init;

        $this->setOption(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Execute a request GET
     *
     * @param bool $close Close the connection
     *
     * @return String
     * @throws Exception
     */
    function get($close = true)
    {
        $this->request_type = "GET";
        $this->setOption(CURLOPT_HTTPGET, true);

        return $this->executeRequest($close);
    }

    /**
     * Execute a request POST
     *
     * @param string $content uri of post data (http_build_query)
     * @param bool   $close   Close the connection
     *
     * @return String
     * @throws Exception
     */
    function post($content, $close = true)
    {
        $this->request_type = "POST";
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $content);

        return $this->executeRequest($close);
    }

    /**
     * Execute a request PUT
     *
     * @param string $content uri of post data (http_build_query)
     * @param bool   $close   Close the connection
     *
     * @return String
     * @throws Exception
     */
    function put($content, $close = true)
    {
        $this->request_type = "PUT";
        $this->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
        $this->setOption(CURLOPT_POSTFIELDS, $content);

        return $this->executeRequest($close);
    }

    /**
     * Execute a request PUT for a file
     *
     * @param String $file  The path of the file
     * @param String $mode  The mode parameter specifies the type of access you require to the stream.
     * @param bool   $close If true, the connection is closed
     *
     * @return string the response's content
     * @throws Exception
     */
    function putFile($file, $mode = "r", $close = true)
    {
        $content = "";

        if (!is_readable($file)) {
            return $content;
        }

        $fp = fopen($file, $mode);

        $this->setOption(CURLOPT_INFILE, $fp);
        $this->setOption(CURLOPT_INFILESIZE, filesize($file));
        $this->setOption(CURLOPT_BINARYTRANSFER, 1);
        $this->setOption(CURLOPT_PUT, 1);

        $content = $this->put(null, $close);

        fclose($fp);

        return $content;
    }

    /**
     * Send a file with a POST request
     * /!\ PHP 5.5+ only
     *
     * @param string $file_path The path of the file
     * @param string $mimetype  The file mimetype
     * @param string $postname  The file POST name
     * @param bool   $close     If true, the connection is closed
     *
     * @return bool|String
     * @throws Exception
     */
    function postFile($file_path, $mimetype = 'application/json', $postname = 'file', $close = true)
    {
        if (!is_readable($file_path)) {
            return false;
        }

        //return $this->post(array('file' => new CURLFile(realpath($file_path), 'application/json', 'file')), $close);
        return $this->post(['file' => new CURLFile(realpath($file_path), $mimetype, $postname)], $close);
    }

    /**
     * Execute a request DELETE
     *
     * @param bool $close Close the connection
     *
     * @return String
     * @throws Exception
     */
    function delete($close = true)
    {
        $this->request_type = "DELETE";
        $this->setOption(CURLOPT_CUSTOMREQUEST, "DELETE");

        return $this->executeRequest($close);
    }

    /**
     * Execute a request HEAD
     *
     * @param bool $close Close the connection
     *
     * @return String
     * @throws Exception
     */
    function head($close = true)
    {
        $this->request_type = "HEAD";
        $this->setOption(CURLOPT_NOBODY, true);

        return $this->executeRequest($close);
    }

    /**
     * Assign a user agent
     *
     * @param String $user_agent user agent
     *
     * @return void
     */
    function setUserAgent($user_agent)
    {
        $this->setOption(CURLOPT_USERAGENT, $user_agent);
    }

    /**
     * Assign a HTTP authentification
     *
     * @param String $username Username for the site
     * @param String $password Password for the site
     *
     * @return void
     */
    function setHTTPAuthentification($username, $password)
    {
        $this->setOption(CURLOPT_USERPWD, "$username:$password");
    }

    /**
     * Assign a SSL authentification
     *
     * @param String $local_cert Certificate path
     * @param String $passphrase Certificate passphrase
     *
     * @return void
     */
    function setSSLAuthentification($local_cert, $passphrase = null)
    {
        $this->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOption(CURLOPT_SSLCERT, $local_cert);
        if ($passphrase) {
            $this->setOption(CURLOPT_SSLCERTPASSWD, $passphrase);
        }
    }

    /**
     * Verify the peer certificate
     *
     * @param String $ca_cert Certificate authority path
     *
     * @return void
     */
    function setSSLPeer($ca_cert)
    {
        $this->setOption(CURLOPT_SSL_VERIFYPEER, 1);
        $this->setOption(CURLOPT_CAINFO, $ca_cert);
    }

    /**
     * Assign cookie to the request
     *
     * @param String $cookie cookie seperate with a ; and space like "fruit=pomme; couleur=rouge"
     *
     * @return void
     */
    function setCookie($cookie)
    {
        $this->setOption(CURLOPT_COOKIE, $cookie);
    }

    /**
     * Assign the option to use
     *
     * @param String $name  Option name
     * @param String $value Option value
     *
     * @return void
     */
    function setOption($name, $value)
    {
        $this->option[$name] = $value;
    }

    /**
     * Create the CURL option
     *
     * @return void
     * @throws Exception
     */
    private function createOption()
    {
        if (count($this->header) !== 0) {
            $this->option[CURLOPT_HTTPHEADER] = $this->header;
        }

        $this->option[CURLOPT_CONNECTTIMEOUT] = $this->option[CURLOPT_CONNECTTIMEOUT] ?? $this->timeout;
        $result = curl_setopt_array($this->handle, $this->option);
        if (!$result) {
            throw new Exception("Impossible d'ajouter une option");
        }
    }

    /**
     * Action before HTTP request
     *
     * @return void
     */
    function onBeforeRequest()
    {
    }

    /**
     * Action before HTTP request
     *
     * @param string $result Result
     *
     * @return void
     */
    function onAfterRequest($result)
    {
    }

    /**
     * Execute the request to the site
     *
     * @param bool $close Close the
     *
     * @return String
     * @throws Exception
     */
    function executeRequest($close = true)
    {
        $handle = $this->handle;
        $this->createOption();

        $this->onBeforeRequest();

        $result                 = curl_exec($handle);
        $this->last_information = $this->getInfo();
        if (curl_errno($handle)) {
            $this->onAfterRequest($result);

            throw new Exception(curl_error($handle));
        }

        if ($close) {
            $this->closeConnection();
        }

        $this->onAfterRequest($result);

        return $result;
    }

    /**
     * Close the connection
     *
     * @return void
     */
    function closeConnection()
    {
        curl_close($this->handle);
    }

    /**
     * Get informations regarding the last request/response
     *
     * @param int|null $opt The information to return (see curl_getinfo for the differents values)
     *
     * @return mixed If an opt is provided, will return the wanted info only, otherwise, an array will be returned
     */
    function getInfo($opt = null)
    {
        if ($opt) {
            return curl_getinfo($this->handle, $opt);
        }

        return curl_getinfo($this->handle);
    }

    /**
     * Check the URL disponibility
     *
     * @param String   $url         URL site
     * @param String[] $option      Option array
     * @param Boolean  $return_body Return the content of the page
     * @param array    $headers     A set of custom headers to pass
     *
     * @return bool|int
     */
    static function checkUrl($url, $option = null, $return_body = false, $headers = [])
    {
        try {
            $http_client = new CHTTPClient($url);

            if ($option) {
                if (CMbArray::get($option, "ca_cert")) {
                    $http_client->setSSLPeer($option["ca_cert"]);
                }
                if (CMbArray::get($option, "username") || CMbArray::get($option, "password")) {
                    $http_client->setHTTPAuthentification($option["username"], $option["password"]);
                }
                if (CMbArray::get($option, "local_cert")) {
                    $http_client->setSSLAuthentification($option["local_cert"], $option["passphrase"]);
                }
                if (!CMbArray::get($option, 'verify_peer', true)) {
                    $http_client->setOption(CURLOPT_SSL_VERIFYPEER, false);
                    $http_client->setOption(CURLOPT_SSL_VERIFYHOST, false);
                }
            }

            foreach ($headers as $_header) {
                $http_client->header[] = $_header;
            }

            $http_client->setOption(CURLOPT_HEADER, true);
            $result = $http_client->get();
        } catch (Exception $e) {
            return false;
        }

        if ($return_body) {
            // Gestion du retour 404 Not Found
            $return = null;
            preg_match("|404 Not Found|", $result) ? $return = false : $return = $result;

            return $return;
        }

        return preg_match("|200|", $result);
    }

    /**
     * Basic HTTP header parser
     * FIXME: doesn't work for repeating headers
     *
     * @param string $headers Headers to parse
     *
     * @return array
     */
    function parseHeaders($headers)
    {
        $headers       = explode("\r\n", $headers);
        $headers_array = [];

        foreach ($headers as $_header) {
            if (strpos($_header, "HTTP/") === 0) {
                [
                    $headers_array["HTTP_Version"],
                    $headers_array["HTTP_Code"],
                    $headers_array["HTTP_Message"],
                ] = explode(" ", $_header, 3);

                continue;
            }

            [$_key, $_value] = explode(":", $_header, 2);
            $headers_array[$_key] = $_value;
        }

        return $headers_array;
    }
}
