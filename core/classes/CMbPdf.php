<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use CMb128BObject;
use Ox\Core\Autoload\IShortNameAutoloadable;
use TCPDF;

define("K_TCPDF_EXTERNAL_CONFIG", "config_externe");

define("K_PATH_MAIN", __DIR__ . "/../../vendor/openxtrem/tcpdf/");
define("K_PATH_URL", "");

define("FPDF_FONTPATH", K_PATH_MAIN . "fonts/");
define("K_PATH_CACHE", __DIR__ . "/../../tmp/");
define("K_PATH_URL_CACHE", K_PATH_URL . "cache/");
define("K_PATH_IMAGES", K_PATH_MAIN . "../../images/pictures/");
define("K_BLANK_IMAGE", K_PATH_IMAGES . "_blank.png");

define("HEAD_MAGNIFICATION", 1.1);
define("K_CELL_HEIGHT_RATIO", 1.25);
define("K_TITLE_MAGNIFICATION", 1.3);
define("K_SMALL_RATIO", 2 / 3);



/**
 * Classe de gestion des pdf heritant de TCPDF
 */
class CMbPdf extends TCPDF implements IShortNameAutoloadable {
  /**
   * @inheritDoc
   */
  public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding="UTF-8") {
    parent::__construct($orientation, $unit, $format, $unicode, $encoding);

    CMb128BObject::init();
  }

  /**
   * Checks if it is UTF-8
   *
   * @param string $str String to check
   *
   * @return bool
   */
  private static function _isUtf8($str) {
    return utf8_encode(utf8_decode($str)) === $str;
  }

  /**
   * Convert to UTF-8
   *
   * @param string $str String to convert
   *
   * @return bool
   */
  private static function _toUtf8($str) {
    return self::_isUtf8($str) ? $str : utf8_encode($str);
  }

  /**
   * @inheritdoc
   */
  public function Text($x, $y, $txt, $stroke = 0, $clip = false) {
    parent::Text($x, $y, self::_toUtf8($txt), $stroke, $clip);
  }

  /**
   * @inheritdoc
   */
  public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $stretch = 0) {
    parent::Cell($w, $h, self::_toUtf8($txt), $border, $ln, $align, $fill, $link, $stretch);
  }

  /**
   * @inheritdoc
   */
  public function Write($h, $txt, $link = '', $fill = 0, $align = '', $ln = false, $stretch = 0) {
    parent::Write($h, self::_toUtf8($txt), $link, $fill, $align, $ln, $stretch);
  }

  /**
   * Write a barcode grid
   *
   * @param float   $x       X position
   * @param float   $y       Y position
   * @param float   $width   Width
   * @param float   $height  Height
   * @param int     $col_num Column count
   * @param int     $row_num Row count
   * @param array[] $data    Data to write
   */
  public function WriteBarcodeGrid($x, $y, $width, $height, $col_num, $row_num, $data) {
    $cell_width  = $width / $col_num;
    $cell_height = $height / $row_num;
    $margin_top  = $y;
    $i           = 0;

    $delta_x = 0;
    $delta_y = 0;
    $this->SetFontSize(7);
    $this->SetDrawColor(20, 20, 20);

    $barcode_height      = 8;
    $barcode_width_ratio = 0.8;

    foreach ($data as $cell) {
      if ($i % $col_num === 0 && $i !== 0) {
        $y       += $cell_height;
        $delta_x = 0;
      }

      if ($i > 1 && $i % ($col_num * $row_num) === 0) {
        $this->AddPage();
        $y = $margin_top;
      }

      $line_height = ($cell_height - $barcode_height) / count($cell);

      $this->Rect($x + $delta_x, $y + $delta_y, $cell_width, $cell_height);
      foreach ($cell as $line) {
        // if it's a barcode
        if (is_array($line)) {
          // draw barcode
          $this->writeBarcode(
            $x + $delta_x + ($cell_width * (1 - $barcode_width_ratio) / 2),
            $y + $delta_y, $cell_width * $barcode_width_ratio,
            $barcode_height, $line['type'], false, false, 2, $line['barcode']
          );

          $delta_y += $barcode_height;

          $this->writeHTMLCell(
            0,
            $line_height,
            $x + $delta_x + ($cell_width * (1 - $barcode_width_ratio) / 2),
            $y + $delta_y, str_replace("x", ' ', $line['barcode']),
            0,
            0,
            0
          );

          $delta_y += $line_height;
        }
        else {
          // print line
          $this->writeHTMLCell(
            0,
            $line_height, $x + $delta_x + ($cell_width * (1 - $barcode_width_ratio) / 2),
            $y + $delta_y,
            utf8_encode($line),
            0,
            0,
            0
          );

          $delta_y += $line_height;
        }
      }
      $delta_y = 0;
      $delta_x += $cell_width;
      $i++;
    }
  }

  /**
   * @inheritdoc
   */
  function OutPut($name = '', $dest = '') {
    if ($dest === "I") {
      // Needs this header for internet explorer
      header("Accept-Ranges: bytes");
    }

    return parent::Output($name, $dest);
  }

  /**
   * @inheritdoc
   */
  protected function _putcatalog() {
    $this->_out('/Type /Catalog');
    $this->_out('/ViewerPreferences <</PrintScaling /None>>');
    $this->_out('/Pages 1 0 R');

    if ($this->ZoomMode === 'fullpage') {
      $this->_out('/OpenAction [3 0 R /Fit]');
    }
    elseif ($this->ZoomMode === 'fullwidth') {
      $this->_out('/OpenAction [3 0 R /FitH null]');
    }
    elseif ($this->ZoomMode === 'real') {
      $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
    }
    elseif (!is_string($this->ZoomMode)) {
      $this->_out('/OpenAction [3 0 R /XYZ null null ' . ($this->ZoomMode / 100) . ']');
    }
    if ($this->LayoutMode === 'single') {
      $this->_out('/PageLayout /SinglePage');
    }
    elseif ($this->LayoutMode === 'continuous') {
      $this->_out('/PageLayout /OneColumn');
    }
    elseif ($this->LayoutMode === 'two') {
      $this->_out('/PageLayout /TwoColumnLeft');
    }
  }
}