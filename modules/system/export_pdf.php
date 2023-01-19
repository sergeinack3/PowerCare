<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;

$data     = CValue::post("data");
$filename = CValue::post("filename", "data");

$data = stripslashes($data);

// @todo Inclure la CSS de MB
$data = "
<html>
  <head>
    <title>$filename</title>
    <style type=\"text/css\">
    
      ".file_get_contents("style/mediboard_ext/htmlarea.css")."
      
      table.tbl th,
      table.tbl td {
        padding: 0.5pt; 
      }
      
      .not-printable {
        display: none; 
      }
    </style>
  </head>
  <body>$data</body>
</html>";

$file = new CFile();
$file->file_name = $filename;

$cr = new CCompteRendu();
$cr->_page_format = "A4";
$cr->_orientation = "landscape";

$convert = new CHtmlToPDF();
@$convert->generatePDF($data, 1, $cr, $file);

