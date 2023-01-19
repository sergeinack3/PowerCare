<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;


CCanDo::checkAdmin();

$string  = CView::post("string", "str", true);
$mode    = CView::post("mode", "str", true);
$compare = CView::post("compare", "str", true);

CView::checkin();

if ($mode == "hex") {
  $string = hex2bin(preg_replace('/[^0-9a-f]/i', '', $string));
}

$charsets = mb_list_encodings();

function bin2hex_format($bin) {
  return strtoupper(implode(" ", str_split(bin2hex($bin), 2)));
}

$results = array();

if ($compare) {
  foreach ($charsets as $_charset) {
    $_out_bin    = array();
    $_char_count = mb_strlen($string, $_charset);

    $_input_chars  = array();
    $_output_chars = array();
    for ($i = 0; $i < $_char_count; $i++) {
      $_char           = mb_substr($string, $i, 1, $_charset);
      $_char_out       = @mb_convert_encoding($_char, "windows-1252", $_charset);
      $_input_chars[]  = array($_char, bin2hex_format($_char));
      $_output_chars[] = array($_char_out, bin2hex_format($_char_out));
    }

    $results[$_charset] = array(
      "input"  => $_input_chars,
      "output" => $_output_chars,
      "status" => @mb_check_encoding($string, $_charset),
    );
  }
}
else {
  foreach ($charsets as $_charset) {
    $_out = mb_convert_encoding($string, "windows-1252", $_charset);

    $results[$_charset] = array(
      "result" => $_out,
      "status" => @mb_check_encoding($string, $_charset),
      "hex"    => strtoupper(implode(" ", str_split(bin2hex($_out), 2))),
    );
  }
}

$smarty = new CSmartyDP();
$smarty->assign("results", $results);
$smarty->assign("compare", $compare);
$smarty->display("inc_charset_finder.tpl");