<?php
/**
 * Conversion d'un nombre quelconque en lettres.
 *
 * @author  Antoine MATTEI <a.mattei@free.fr>
 * @version 0.2
 * @link    http://forum.phpfrance.com/vos-contributions/conversion-nombres-lettres-t260825.html
 */

/**
 * Conversion d'un nombre quelconque en lettres.
 */

namespace Ox\Core;

class CNuts {
  const DEBUG = false;

  private $nb;
  private $decSep;
  private $unit;
  private $parts = array();

  private $separators = array(
    "fr-FR" => array(', ', '-', ' ', ' '),
    "en-EN" => array(', ', '-', ' ', ' '),
    "pt-PT" => array(' e ', ' e ', ' e ', ' e ')
  );

  private $units = array(
    "USD" => array(
      100,
      "fr-FR" => array('dollar', 'dollars', 'centime', 'centimes', '', ''),
      "pt-PT" => array('dollar', 'dollars', 'c�ntimo', 'c�ntimos', 'm', 'm'),
      "en-EN" => array('dollar', 'dollars', 'cent', 'cents', '')
    ),
    "EUR" => array(
      100,
      "fr-FR" => array('euro', 'euros', 'centime', 'centimes', '', ''),
      "pt-PT" => array('euro', 'euros', 'c�ntimo', 'c�ntimos', 'm', 'm'),
      "en-EN" => array('euro', 'euros', 'cent', 'cents', '', '')
    ),
    "t"   => array(
      1000,
      "fr-FR" => array('tonne', 'tonnes', 'kilo', 'kilos', '', ''),
      "pt-PT" => array('tonelada', 'toneladas', 'quilo', 'quilos', 'f', 'm'),
      "en-EN" => array('ton', 'tons', 'kilogram', 'kilograms', '', '')
    ),
    ""    => array(
      1000,
      "fr-FR" => array('', '', '', '', '', ''),
      "pt-PT" => array('', '', '', '', '', ''),
      "en-EN" => array('', '', '', '', '', '')
    )
  );

  private $numbers = array(
    "fr-FR" => array(
      0 => array('z�ro', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'),
      1 => array('dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingts', 'quatre-vingt-dix'),
      2 => array('cent', 'cents'),
      3 => array('mille', 'mille'), /* R�gle 3 */
      6 => array('million', 'millions'),
      9 => array('milliard', 'milliards')
    ),
    "pt-PT" => array(
      0 => array('zero', 'um', 'dois', 'tr�s', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'),
      1 => array('dez', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'),
      2 => array('cem', 'cem', 'f'),
      3 => array('mil', 'mil', 'f'),
      6 => array('milh�o', 'milh�es', 'm'),
      9 => array('mil milh�es', 'mil milh�es', 'm')
    ),
    "en-EN" => array(
      0 => array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'),
      1 => array('ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'),
      2 => array('hundred', 'hundred'),
      3 => array('thousand', 'thousand'),
      6 => array('million', 'millions'),
      9 => array('billion', 'billions')
    )
  );

  private $localExceptions = array(
    "fr-FR" => array(
      array("/dix-un/", "onze"),
      array("/dix-deux/", "douze"),
      array("/dix-trois/", "treize"),
      array("/dix-quatre/", "quatorze"),
      array("/dix-cinq/", "quinze"),
      array("/dix-six/", "seize"),
      array("/-un/", " et un"), /* R�gle 1 */
      array('/^et /', ''),
      array("/soixante-onze/", "soixante et onze"), /* R�gle 2 */
      array('/^-/', ''),
      array('/ z�ro$/', ""),
      array("/-z�ro/", ""),
      array("/cents /", "cent "), /* R�gle 4 */
      array('/cent et/', 'cent'),
      array('/cents et/', 'cents'),
      array('/-$/', ""),
      array("/vingts-/", "vingt-"), /* R�gle 4 */
      array("/un cent/", "cent"),
      array("/^un mille/", "mille"),
      array("/cent millions/", "cents millions"), /* R�gle 5 */
      array("/cent milliards/", "cents milliards") /* R�gle 5 */
    ),
    "pt-PT" => array(
      array('/^ e zero$/', 'zero'),
      array('/ zero$/', ''),
      array('/ e zero/', ''),
      array("/dez e um/", "onze"),
      array("/dez e dois/", "doze"),
      array("/dez e tr�s/", "treze"),
      array("/dez e quatro/", "catorze"),
      array("/dez e cinco/", "quinze"),
      array("/dez e seis/", "dezasseis"),
      array("/dez e sete/", "dezassete"),
      array("/dez e oito/", "dezoito"),
      array("/dez e nove/", "dezenove"),
      array("/um e cem/", "cem"),
      array("/dois e cem/", "e duzentos"),
      array('/e duzentos e /', 'duzentos e '),
      array('/tr�s e cem/', 'e trezentos'),
      array('/e trezentos e /', 'trezentos e '),
      array("/quatro e cem/", "e quatro centos"),
      array('/e quatro centos e /', 'quatro centos e '),
      array('/cinco e cem/', 'e quinhentos'),
      array('/e quinhentos e /', 'quinhentos e '),
      array("/seis e cem/", "e seiscentos"),
      array('/e seiscentos e /', 'seiscentos e '),
      array("/sete e cem/", "e setecentos"),
      array('/e setecentos e /', 'setecentos e '),
      array("/oito e cem/", "e oitocentos"),
      array('/e oitocentos e /', 'oitocentos e '),
      array("/nove e cem/", "e novecentos"),
      array('/e novecentos e /', 'novecentos e '),
      array('/cem e /', 'cento e '),
      array('/ e$/', ''),
      array('/^e$/', ''),
      array('/um mil$/', 'mil')
    ),
    "en-EN" => array(
      array("/ten-one/", "eleven"),
      array("/ten-two/", "twelve"),
      array("/ten-three/", "thirteen"),
      array("/ten-four/", "fourteen"),
      array("/ten-five/", "sixteen"),
      array("/ten-six/", "sixteen"),
      array("/ten-seven/", "seventeen"),
      array("/ten-height/", "eighteen"),
      array("/ten-nine/", "nineteen"),
      array('/^-/', ''),
      array("/-zero/", ""),
      array("/hundred /", "hundred and "),
      array('/hundred-/', 'hundred and '),
      array('/hundred and thousand/', 'hundred thousand'),
      array("/ and zero/", "")
    )
  );

  private $partExceptions = array(
    "fr-FR" => array(
      array("/ z�ro/", ""),
      array("/[[:blank:]].z�ro/", ""),
      array('/^-/', ''),
      array('/ -/', ' '),
      array('/million$/', "million de"),
      array('/millions$/', "millions de"),
      array('/milliard$/', "milliard de"),
      array('/milliards$/', "milliards de")
    ),
    "pt-PT" => array(
      array('/ zero/', ""),
      array('/^e /', ''),
      array('/milh�es$/', 'milh�es de'),
      array('/milh�o$/', 'milh�o de')
    ),
    "en-EN" => array(
      array("/ zero/", ""),
      array("/[[:blank:]].zero/", "")
    )
  );

  private $genderExceptions = array(
    "fr-FR" => array(),
    "pt-PT" => array(
      array('/um/', 'uma'),
      array('/dois/', 'duas'),
      array('/entos/', 'entas')
    ),
    "en-EN" => array()
  );

  private $globalExceptions = array(
    "fr-FR" => array(
      array("/de e/", "d'e")
    ),
    "pt-PT" => array(),
    "en-EN" => array()
  );

  /**
   * Formats accept�s : 1234 | 12,34 | 12.34 | 12 345.
   * Un seul caract�re non num�rique sera accept� et consid�r� comme le s�parateur d�cimal en entr�e.
   *
   * <code>
   *   $obj = new nuts("12345.67", "EUR");
   *   $text = $obj->convert("fr-FR");
   *   $nb = $obj->getFormated(" ", ",");
   * </code>
   *
   * @param float  $nb   Nombre � convertir.
   * @param string $unit Unit�
   */
  function __construct($nb, $unit) {
    // Nettoyages.
    $this->nb = str_replace(' ', '', $nb); // Suppession de tous les espaces.
    $this->nb = preg_replace("/[A-Za-z]/", "", $this->nb);
    $this->nb = preg_replace("/^0+/", "", $this->nb); // Suppression des 0 de t�te.

    if ($this->nb == '') {
      $this->nb = '0';
    }

    $this->unit = $unit;

    // S�parateur.
    $this->decSep = preg_replace("/[0-9]/", '', $this->nb); // On ne garde que ce qui n'est pas num�rique
    $this->decSep = substr($this->decSep, -1); // et on prend le dernier des caract�res restants.

    // Partie enti�re et partie d�cimale.
    if ($this->decSep == '') {
      // Pas de partie d�cimale.
      $this->parts[] = $this->nb;
    }
    else {
      // Ajout d'un 0 quand il manque devant le s�parateur d�cimal.
      // Noter le double \ pour �chapper le s�parateur . qui est un op�rateur dans les expressions r�guli�res.
      $this->nb = preg_replace("/^\\" . $this->decSep . "/", "0" . $this->decSep, $this->nb);

      $this->parts = explode($this->decSep, preg_replace("/^[0-9] " . $this->decSep . "/", '', $this->nb));

      // Nettoyage partie d�cimale.
      if ($this->parts[1] == '') {
        unset($this->parts[1]);
        $this->decSep = '';
      }
      else {
        // On coupe la partie d�cimale au nombre de caract�res en fonction du rapport entre unit� et sous-unit�.
        $this->parts[1] = substr($this->parts[1], 0, strlen($this->units[$this->unit][0]) - 1);

        // On bourre avec des 0 de fin.
        while (strlen($this->parts[1]) < strlen($this->units[$this->unit][0]) - 1) {
          $this->parts[1] .= '0';
        }
      }
    }

    if (CNuts::DEBUG) {
      echo "construct : [" . $this->nb . "]";
    }
  }

  /**
   * Module de traduction d'un groupe de 3 digits.
   *
   * @param string  $group    Groupe de 3 digits.
   * @param integer $unit     Indice de l'unit� concern�e par $group.
   * @param string  $language Langue demand�e.
   * @param integer $gender   Indice du genre dans le tableau (4 pour l'unit� de mesure, 5 pour la sous-unit�).
   *
   * @return string
   */
  private function getThree($group, $unit, $language, $gender) {
    $return = "";

    if ($group == '') {
      $group = 0;
    }

    // Centaines.
    if ($group >= 100) {
      $hundreds = floor($group / 100);;
      if ($hundreds == 1) {
        $return .= $this->numbers[$language][0][$hundreds] . $this->separators[$language][3] . $this->numbers[$language][2][0];
      }
      else {
        $return .= $this->numbers[$language][0][$hundreds] . $this->separators[$language][3] . $this->numbers[$language][2][1];
      }

      $tens = $group % 100; // On enl�ve les centaines.
    }
    else {
      $tens = $group;
    }

    // Dizaines et unit�s.
    if ($tens >= 10) {
      $return .= $this->separators[$language][2] .
        $this->numbers[$language][1][floor($tens / 10) - 1] .
        $this->separators[$language][1] .
        $this->numbers[$language][0][$tens % 10];
    }
    else {
      $return .= $this->separators[$language][1] . $this->numbers[$language][0][(int)$tens];
    }

    if ($unit < 3) {
      // [0..999].
    }
    else {
      // 0, 1 ou n ?
      if ($group == 0) {
      }
      elseif ($group == 1) {
        $return .= ' ' . $this->numbers[$language][$unit][0];
      }
      else {
        $return .= ' ' . $this->numbers[$language][$unit][1];
      }
    }

    if (CNuts::DEBUG) {
      echo "<br>local a [" . $return . "] ";
    }
    // Exceptions.
    for ($i = 0; $i < count($this->localExceptions[$language]); $i++) {
      $return = trim(preg_replace($this->localExceptions[$language][$i][0], $this->localExceptions[$language][$i][1], $return));
      //echo " $i [" . $return . "]";
    }

    if (CNuts::DEBUG) {
      echo "<br>local b $unit [" . $return . "] ";
    }
    // Exceptions de genre.
    if (
      ($this->units[$this->unit][$language][$gender] == 'f' && $unit < 3) ||
      ($this->units[$this->unit][$language][$gender] == 'f' && $this->numbers[$language][$unit][2] == 'f')
    ) {
      // L'unit� (tonelada) peut �tre de genre f�minin mais les milliers ou millards peuvent �tre invariables en genre.
      for ($i = 0; $i < count($this->genderExceptions[$language]); $i++) {
        $return = trim(preg_replace($this->genderExceptions[$language][$i][0], $this->genderExceptions[$language][$i][1], $return));
      }
    }

    if (CNuts::DEBUG) {
      echo "<br>local c [" . $return . "] ";
    }

    return $return;
  }

  /**
   * Formate la sortie num�rique avec s�parateurs d�cimal et des milliers souhait�s.
   *
   * @param string $tSep S�parateur des milliers, en sortie.
   * @param string $dSep S�parateur d�cimal, en sortie.
   *
   * @return string
   */
  function getFormated($tSep = '', $dSep = '.') {
    if ($tSep == $dSep) {
      // Les 2 s�parateurs ne peuvent �tre identiques > valeurs par d�faut.
      $tSep = '';
      $dSep = '.';
    }

    $return = $this->format($this->parts[0], $tSep);
    if ($this->decSep == '') {
      // Pas de partie d�cimale.
      $return .= ' ' . $this->unit;
    }
    else {
      $return .= $dSep . $this->parts[1] . ' ' . $this->unit;
    }

    return $return;
  }

  /**
   * Formatage par groupe de 3 digits avec s�parateur.
   *
   * @param string $nb  Nombre � formater.
   * @param string $sep S�parateur de milliers (espace, virgule).
   *
   * @todo Il faudra s'assurer que le s�parateur n'est pas le m�me que le s�parateur d�cimal qui a �t� identifi� automatiquement.
   *
   * @return string
   */
  private function format($nb, $sep) {
    $nb = strrev($nb);
    $n  = 0;
    for ($i = 2; $i < strlen($nb); $i++) {
      if ($i % 3 == 0) {
        $nb = substr($nb, 0, $i + $n) . $sep . substr($nb, $i + $n);
        $n++;
      }
    }

    return strrev(trim($nb));
  }

  /**
   * Effectue la conversion en texte de la partie enti�re ou d�cimale.
   *
   * @param string  $nb       Partie enti�re ou d�cimale.
   * @param string  $language Langue � utiliser pour la sortie.
   * @param integer $gender   Indice du genre dans le tableau (4 pour l'unit� de mesure, 5 pour la sous-unit�).
   *
   * @return string
   */
  private function part($nb, $language, $gender) {
    $return = "";

    // D�codage par blocs de 3.
    $groups = explode(' ', $this->format($nb, ' '));
    for ($i = 0; $i < count($groups); $i++) {
      $return .= $this->getThree($groups[$i], (count($groups) - 1 - $i) * 3, $language, $gender) . ' ';
    }
    $return = trim($return);

    if (CNuts::DEBUG) {
      echo "<br>part a : [" . $return . "]";
    }
    // Exceptions.
    for ($i = 0; $i < count($this->partExceptions[$language]); $i++) {
      $return = trim(preg_replace($this->partExceptions[$language][$i][0], $this->partExceptions[$language][$i][1], $return));
    }

    if (CNuts::DEBUG) {
      echo "<br>part b : [" . $return . "]";
    }

    return $return;
  }

  /**
   * Demande la conversion en texte de chaque partie et ajoute les unit�s.
   *
   * @param string $language Langue � utiliser pour la sortie.
   *
   * @return string
   */
  function convert($language) {
    // Partie enti�re.
    $return = $this->part($this->parts[0], $language, 4) . " ";
    $return .= ($this->parts[0] > 1) ? $this->units[$this->unit][$language][1] : $this->units[$this->unit][$language][0];

    // Exceptions.
    for ($i = 0; $i < count($this->globalExceptions[$language]); $i++) {
      $return = trim(preg_replace($this->globalExceptions[$language][$i][0], $this->globalExceptions[$language][$i][1], $return));
    }

    // Partie d�cimale.
    if (count($this->parts) == 2) {
      $return .= $this->separators[$language][0] . $this->part($this->parts[1], $language, 5) . " ";
      $return .= ($this->parts[1] > 1) ? $this->units[$this->unit][$language][3] : $this->units[$this->unit][$language][2];
    }

    if (CNuts::DEBUG) {
      echo "<br>";
    }

    return $return;
  }
}
