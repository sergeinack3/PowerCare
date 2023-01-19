<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\HttpClient;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Ox\Mediboard\System\CExchangeHTTP;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CResponse
 */
class Response {

  /** @var int */
  private $status_code;

  /** @var GuzzleResponse */
  private $guzzle_response;

  /** @var CExchangeHTTP */
  private $exchange_http;

  /**
   * CRestClient constructor.
   *
   * @param ResponseInterface $response
   * @param CExchangeHTTP     $exchange_http
   */
  public function __construct(ResponseInterface $response, CExchangeHTTP $exchange_http = null) {
    $this->guzzle_response = $response;
    $this->exchange_http   = $exchange_http;
    $this->status_code     = $response->getStatusCode();
  }

  /**
   * @return mixed
   */
  public function getStatusCode() {
    return $this->status_code;
  }

  /**
   * @return array|string[]
   */
  public function getHeaders() {
    return $this->guzzle_response->getHeaders();
  }

  /**
   * @param string $name
   *
   * @return array|mixed|string[]
   */
  public function getHeader($name) {
    return $this->guzzle_response->getHeader($name);
  }

  /**
   * @param string $encode_to
   * @return mixed
   */
  public function getBody($encode_to = null) {
    $body = $this->guzzle_response->getBody()->__toString();

    $content_type = $this->guzzle_response->getHeader('Content-Type');
    if (count($content_type) > 0 && strpos($content_type[0], 'application/json') === 0) {
      $body = json_decode($body, true);
      $e = json_last_error_msg();
      if($encode_to){
        array_walk_recursive($body, function (&$item) use ($encode_to) {
          $item = mb_convert_encoding($item, $encode_to, 'UTF-8');
        });
      }
    }

    return $body;
  }

  /**
   * @return ResponseInterface
   */
  public function getGuzzleResponse(): ResponseInterface {
    return $this->guzzle_response;
  }

  /**
   * @return CExchangeHTTP|null
   */
  public function getExchangeHttp(): ?CExchangeHTTP {
    return $this->exchange_http;
  }

}
