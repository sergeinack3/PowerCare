<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * To put colors in a CLI PHP script
 * Only on UNIX
 * Based from http://www.phpcs.com/codes/AJOUTER-COULEUR-VOS-BASH-PHP_45564.aspx
 * Some of styles don't work on all clients
 *
 * @param string $text     [optional] Text to color
 * @param string $txtColor [optional] Wanted color (black, red, green, cyan, magenta, etc.)
 * @param string $bgColor  [optional] Background color
 * @param string $styleTxt [optional] Font style (bold, underline, reverse, flashing)
 *
 * @return string
 */
function shColorText($text = '', $txtColor = '', $bgColor = '', $styleTxt = 'none') {
  $__ESC   = "\033";
  $__START = "[";
  $__END   = "m";

  $__CLEAR  = $__ESC . "[2J";
  $__NORMAL = $__ESC . "[0m";

  if ($text === 'CLEAR') {
    return $__NORMAL . $__CLEAR;
  }

  if (empty($text) || !$text) {
    return $__NORMAL;
  }

  // Text color
  $aTextColor['black']   = 30;
  $aTextColor['red']     = 31;
  $aTextColor['green']   = 32;
  $aTextColor['yellow']  = 33;
  $aTextColor['blue']    = 34;
  $aTextColor['magenta'] = 35;
  $aTextColor['cyan']    = 36;
  $aTextColor['white']   = 37;

  // Background color
  $aBgColor['black']   = 40;
  $aBgColor['red']     = 41;
  $aBgColor['green']   = 42;
  $aBgColor['yellow']  = 43;
  $aBgColor['blue']    = 44;
  $aBgColor['magenta'] = 45;
  $aBgColor['cyan']    = 46;
  $aBgColor['white']   = 47;

  // Style text
  $aStyle['none']      = 0; //normal
  $aStyle['bold']      = 1; //gras
  $aStyle['underline'] = 4; //souligné
  $aStyle['flashing']  = 5; //clignotant
  $aStyle['reverse']   = 7; //inversé

  $a = null;

  if ($styleTxt && isset($aStyle[$styleTxt])) {
    $a[] = $aStyle[$styleTxt];
  }

  if ($txtColor && isset($aTextColor[$txtColor])) {
    $a[] = $aTextColor[$txtColor];
  }

  if ($bgColor && isset($aBgColor[$bgColor])) {
    $a[] = $aBgColor[$bgColor];
  }

  if (is_null($a)) {
    return $text;
  }

  $c = $__ESC . $__START . implode(';', $a) . $__END;

  return $c . $text . $__NORMAL;
}

/**
 * Permet de mettre en forme la police d'un texte par des balises
 *
 * Ex : Ceci est un <c c=blue bg=white s=bold>TEST</c>
 *
 * Enable you to set font style with tags
 * Ex: This is a <c c=blue bg=white s=bold>TEST</c>
 *
 * @param string $str String to set font style
 *
 * @return string
 */
function parseShColorTag($str) {
  $tag      = "/(<c[^>]*>)([^<]*)<\/c>/";
  $innerTag = "/([\w]+)=([\w]+)/";
  preg_match_all($tag, $str, $r);

  if (!is_array($r[1])) {
    return $str;
  }

  foreach ($r[1] as $k => $v) {
    preg_match_all($innerTag, $v, $r2);

    if (!is_array($r2[1])) {
      return $str;
    }

    $c = $bg = $s = false;

    foreach ($r2[1] as $i => $value) {
      switch ($value) {
        case 'c':
          $c = $r2[2][$i];
          break;

        case 'bg':
          $bg = $r2[2][$i];
          break;

        case 's':
          $s = $r2[2][$i];
          break;
      }
    }

    $string = shColorText($r[2][$k], $c, $bg, $s);
    $str    = str_replace($r[0][$k], $string, $str);
  }

  return $str;
}
