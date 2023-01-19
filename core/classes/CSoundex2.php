<?php
/**
 * @package    Mediboard
 * @subpackage Classes
 * @author     Johan Barbier <barbier_johan@hotmail.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Core;

/**
 * Soundex2 French version based on the algorithm described here : http://sqlpro.developpez.com/cours/soundex/ by Frédéric BROUARD
 */
class CSoundex2 {
  /**
   * public sString
   * main string we work on
   */
  public $sString = '';

  /**
   * vowels replacement array
   */
  private $aReplaceVoy1 = array(
    'E' => 'A',
    'I' => 'A',
    'O' => 'A',
    'U' => 'A'
  );

  /**
   * consonnants replacement array
   */
  private $aReplaceGrp1 = array(
    'GUI' => 'KI',
    'GUE' => 'KE',
    'GA'  => 'KA',
    'GO'  => 'KO',
    'GU'  => 'K',
    'CA'  => 'KA',
    'CO'  => 'KO',
    'CU'  => 'KU',
    'Q'   => 'K',
    'CC'  => 'K',
    'CK'  => 'K'
  );

  /**
   * other replacement array
   */
  private $aReplaceGrp2 = array(
    'ASA' => 'AZA',
    'KN'  => 'NN',
    'PF'  => 'FF',
    'PH'  => 'FF',
    'SCH' => 'SSS'
  );

  /**
   * endings replacement array
   */
  private $aEnd = array(
    'A',
    'T',
    'D',
    'S'
  );

  /**
   * public function build
   * core function of the class, go through the whole process
   *
   * @param string $sString The string we want to check
   *
   * @return string The CSoundex2 value of the string
   */
  public function build($sString) {
    /**
     * let's check it's a real string...
     */
    if (is_string($sString) && $sString != '') {
      $this->sString = $sString;
    }
    else {
      return '';
    }

    /**
     * remove starting and ending spaces
     */
    $this->sString = trim($this->sString);
    /**
     * remove special french characters
     */
    $this->trimAccent();
    /**
     * string to upper case
     */
    $this->sString = strtoupper($this->sString);
    /**
     * let's remove every space in the string
     */
    $this->sString = str_replace(' ', '', $this->sString);
    /**
     * let's remove every '-' in the string
     */
    $this->sString = str_replace('-', '', $this->sString);

    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * let's process through the first replacement array
     */
    $this->arrReplace($this->aReplaceGrp1);

    /**
     * let's process through th vowels replacement
     */
    $sChar         = substr($this->sString, 0, 1);
    $this->sString = substr($this->sString, 1, strlen($this->sString) - 1);
    $this->arrReplace($this->aReplaceVoy1);
    $this->sString = $sChar . $this->sString;
    /**
     * let's process through the second replacement array
     */
    $this->arrReplace($this->aReplaceGrp2, true);
    /**
     * let's remove every 'H' but those prededed by a 'C' or an 'S'
     */
    $this->sString = preg_replace('/(?<![CS])H/', '', $this->sString);
    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * let's remove every 'Y' but those preceded by an 'A'
     */
    $this->sString = preg_replace('/(?<!A)Y/', '', $this->sString);
    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * remove endings in aEnd
     */
    $length = strlen($this->sString) - 1;
    if (in_array($this->sString[$length], $this->aEnd)) {
      $this->sString = substr($this->sString, 0, $length);
    }
    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * let's remove every 'A', but the one at the beginning of the string, if any.
     */
    $sChar = '';
    if ($this->sString[0] === 'A') {
      $sChar = 'A';
    }
    $this->sString = str_replace('A', '', $this->sString);
    $this->sString = $sChar . $this->sString;
    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * let's have only 1 occurence of each letter
     */
    $this->sString = preg_replace('/(.)\1/', '$1', $this->sString);
    if (!strlen($this->sString)) {
      return $sString[0];
    }
    /**
     * let's have the final code : a 4 letters string
     */
    $this->getFinal();

    return $this->sString;
  }

  /**
   * private function getFinal
   * gets the first 4 letters, pads the string with white space if the string length < 4
   *
   * @return void
   */
  private function getFinal() {
    if (strlen($this->sString) >= 4) {
      $this->sString = substr($this->sString, 0, 4);
    }
  }

  /**
   * private function trimAccent
   * remove every special French letters
   *
   * @return void
   */
  private function trimAccent() {
    $this->sString = CMbString::htmlEntities(strtolower($this->sString));
    $this->sString = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml|grave);/", "$1", $this->sString);
    $this->sString = preg_replace("/([^a-z0-9]+)/", "-", html_entity_decode($this->sString, ENT_COMPAT));
    $this->sString = trim($this->sString, "-");
  }

  /**
   * private function arrReplace
   * replacement method, given an array
   *
   * @param array $tab  The replacement array to be used
   * @param bool  $pref If false, just replace keys by values; if true, do the same but only with prefix
   *
   * @return void
   */
  private function arrReplace($tab, $pref = false) {
    $fromRep = array_keys($tab);
    $toRep   = array_values($tab);

    if (false === $pref) {
      $this->sString = str_replace($fromRep, $toRep, $this->sString);
    }
    else {
      foreach ($fromRep as $clef => $val) {
        $length = strlen($val);
        if (substr($this->sString, 0, $length) === $val) {
          $this->sString = substr_replace($this->sString, $toRep[$clef], 0, $length);
        }
      }
    }
  }
}
