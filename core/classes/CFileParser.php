<?php

/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Throwable;
use Vaites\ApacheTika\Client;
use Vaites\ApacheTika\Metadata\Metadata;


/**
 *  File parser for indexing
 */
class CFileParser {
  public $client;
  public $options;
  public $current_file;


  /**
   * @param string $lang Lang fo tesseract OCR (fra/eng)
   *
   * @return void
   * @throws Exception
   */
  public function __construct($lang = 'fra') {
    $host    = trim(CAppUI::conf("dPfiles tika host"));
    $port    = trim(CAppUI::conf("dPfiles tika port"));
    $timeout = (int)trim(CAppUI::conf("dPfiles tika timeout"));

    $this->options = array(
      CURLOPT_TIMEOUT    => $timeout,
      CURLOPT_HTTPHEADER => array("X-Tika-OCRLanguage: $lang")
    );

    $this->client = Client::make($host, $port, $this->options);
  }


  /**
   * @param String $file path/to/filename.ext
   *
   * @return Metadata|bool
   * @throws Exception
   */
  public function getMetadata($file) {
    if (!file_exists($file)) {
      return false;
    }

    return $this->client->getMetadata($file);
  }

  /**
   * @param String $file path/to/filename.ext
   *
   * @return string|bool
   * @throws Exception
   */
  public function getContent($file) {
    if (!file_exists($file)) {
      return false;
    }

    // Init
    $this->current_file = $file;
    $_pathinfo          = pathinfo($file);
    [$_type, $_ext] = explode('/', mime_content_type($file));

    // Tika
    if ($content = $this->tikaContent(false)) {
      return $content;
    }

    // 2° chance image (title) todo native tesseract ?
    if ($_type === 'image') {
      return $_pathinfo['filename'];
    }

    // 2° chance pdf (tika with PDFextractInlineImages)
    $active_ocr_pdf = (bool)CAppUI::conf("dPfiles tika active_ocr_pdf");
    if (strtolower($_ext) === 'pdf' && $active_ocr_pdf) {
      return $this->tikaContent(true);
    }

    // 2° chance (regex) todo optimiser pour osoft, surgica ...
    return $this->fileContent();
  }

  /**
   * REGEX
   *
   * @return string
   */
  private function fileContent() {
    $content = file_get_contents($this->current_file);
    $content = preg_replace('/[[:^print:]]/', '', $content);
    $content = strip_tags($content);

    return $content;
  }


  /**
   * @param bool $force_ocr_pdf Force OCR on inline images (tesseract)
   *
   * @return bool|string $content
   * @throws Exception
   */
  private function tikaContent($force_ocr_pdf = false) {
    $_options = $this->options;
    if ($force_ocr_pdf) {
      $_options[CURLOPT_HTTPHEADER][] = "X-Tika-PDFextractInlineImages: true";
    }
    $this->client->setOptions($_options);

    try {
      $content = $this->client->getText($this->current_file);
    }
    catch (Throwable $e) {
      CApp::log($e->getMessage());
      $content = false;
    }

    return $content;
  }


}
