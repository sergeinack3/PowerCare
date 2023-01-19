<?php
/**
 * @package Mediboard\\${Module}
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbPdf;
use Ox\Tests\OxUnitTestCase;

class CMbPdfTest extends OxUnitTestCase {

  public function testOutPut() {
    $file_name = 'CMbPdfTest_testOutPut.pdf';
    $file      = __DIR__ . '/../../../tmp/' . $file_name;
    if (file_exists($file)) {
      unlink($file);
    }

    // create new PDF document
    $pdf = new CMbPdf('P', 'mm');

    // set document information
    $pdf->SetAuthor('OX');
    $pdf->SetTitle('CMbPdfTest');
    $pdf->SetSubject('testOutPut');

    // remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // set margins
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

    // add a page
    $pdf->AddPage();

    // list fonts
    $fonts = array(
      'dejavusans',
      //'ocrb2',
      //'ocrbmedium',
      'ocrb', // regular
      'vera',
      //'verabd',
      'verab',
      'verai',
      'c39hrp24dhtt'
    );

    // write
    foreach ($fonts as $font) {
      $pdf->SetFont('dejavusans');
      $pdf->SetFontSize("15");
      $pdf->Write(15, "[$font]\n");

      $pdf->SetFont($font);
      $pdf->SetFontSize("25");
      $pdf->Write(15, "Lorem ipsum dolor sit amet !\n");
    }

    $pdf->Output($file, 'F');

    $this->assertFileExists($file);
  }
}
