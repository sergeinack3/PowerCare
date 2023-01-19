<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Converter;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionManager;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 *
 * Todo: Refactor to use CWkhtmlToPDF::makePDF
 */
class ExObjectPDFConverter {
  /** @var string */
  private const BIN_DIRECTORY = 'vendor' . DIRECTORY_SEPARATOR . 'bin';

  /** @var string */
  private const BIN = 'wkhtmltopdf-amd64-0-11-0-RC1';

  /** @var CExObject */
  private $ex_object;

  /** @var CMbObject */
  private $object;

  /**
   * ExObjectPDFConverter constructor.
   *
   * @param CExObject $ex_object
   * @param CMbObject $object
   *
   * @throws CMbException
   */
  public function __construct(CExObject $ex_object, CMbObject $object) {
    if (!$ex_object->_id || !$ex_object->_ex_class_id || !$object->_id || !$object->_guid) {
      throw new CMbException('ExObjectPDFConverter-error-Valid CExObject and CMbObject must be provided');
    }

    if ($this->isWindows()) {
      throw new CMbException('ExObjectPDFConverter-error-Windows platform not supported');
    }

    $this->ex_object = $ex_object;
    $this->object    = $object;
  }

  /**
   * @return bool
   */
  protected function isWindows(): bool {
    return (strpos(PHP_OS, 'WIN') !== false);
  }

  /**
   * @return string
   * @throws CMbException
   */
  public function convert(): string {
    $html_source = $this->getHTMLSource();

    if (!$html_source) {
      throw new CMbException('ExObjectPDFConverter-error-Unable to get HTML source of the CExObject');
    }

    $command = $this->getBinaryCommand();

    $options = ' -q --print-media-type';

    $source_file = $this->createTemporarySourceFile($html_source);

    try {
      $result_file = $this->createTemporaryResultFile();
    }
    catch (CMbException $e) {
      unlink($source_file);

      throw $e;
    }

    $options .= ' ' . escapeshellarg($source_file);
    $options .= ' ' . escapeshellarg($result_file);

    $options .= ' 2> /dev/null';

    exec($command . $options, $output, $return_code);

    unlink($source_file);

    if ($return_code !== 0) {
      unlink($result_file);

      throw new CMbException('ExObjectPDFConverter-error-An error occurred during PDF conversion');
    }

    $pdf_content = file_get_contents($result_file);

    unlink($result_file);

    if ($pdf_content === false) {
      throw new CMbException('ExObjectPDFConverter-error-Unable to get PDF file content');
    }

    return $pdf_content;
  }

  /**
   * @return string
   * @throws Exception
   */
  protected function getHTMLSource(): string {
    $http_client = $this->makeHTTPClient();

    return $http_client->get(true);
  }

  /**
   * @return CHTTPClient
   * @throws Exception
   */
  private function makeHTTPClient(): CHTTPClient {
    $http_client = new CHTTPClient($this->makeURL());

    $session_name = CSessionManager::forgeSessionName();
    $cookie       = CValue::cookie($session_name);

    $http_client->setCookie("{$session_name}={$cookie}");

    return $http_client;
  }

  /**
   * @return string
   */
  private function makeURL(): string {
    $url = rtrim(CAppUI::conf('base_url'), '/');

    $params = [
      'm'            => 'forms',
      'a'            => 'view_ex_object_form',
      'ex_object_id' => $this->ex_object->_id,
      'ex_class_id'  => $this->ex_object->_ex_class_id,
      'object_guid'  => $this->object->_guid,
      'readonly'     => '1',
      'print'        => '1',
      'only_filled'  => '0',
      'dialog'       => '1',
      '_aio'         => '1',
    ];

    return "{$url}/index.php?" . http_build_query($params, null, '&');
  }

  /**
   * @return string
   */
  private function getBinaryCommand(): string {
    $root_dir = rtrim(CAppUI::conf('root_dir'), '/');

    return $root_dir . DIRECTORY_SEPARATOR . self::BIN_DIRECTORY . DIRECTORY_SEPARATOR . self::BIN;
  }

  /**
   * @param string $html_source
   *
   * @return string
   * @throws CMbException
   */
  protected function createTemporarySourceFile(string $html_source): string {
    $temp = tempnam('./tmp', 'wkhtmltopdf');

    if ($temp === false) {
      throw new CMbException('ExObjectPDFConverter-error-Cannot create temporary source file');
    }

    $temp_html = "{$temp}.html";

    if (file_exists($temp_html) || !rename($temp, $temp_html)) {
      throw new CMbException('ExObjectPDFConverter-error-Cannot create temporary source file, file already exists');
    }

    if (file_put_contents($temp_html, $html_source) === false) {
      unlink($temp_html);

      throw new CMbException('ExObjectPDFConverter-error-Cannot write temporary source file');
    }

    return $temp_html;
  }

  /**
   * @return string
   * @throws CMbException
   */
  protected function createTemporaryResultFile(): string {
    $result = tempnam('./tmp', 'result');

    if ($result === false) {
      throw new CMbException('ExObjectPDFConverter-error-Cannot create temporary result file');
    }

    return $result;
  }
}
