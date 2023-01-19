<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Exception;
use Ox\Core\CFileParser;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;

/**
 * Decode string into data
 */
class CIdInterpreter implements IShortNameAutoloadable {
  public $char_trans = array(
    "0" => "O",
    "1" => "I",
    "2" => "Z",
    "4" => "A",
    "5" => "S",
    "6" => "E",
    "7" => "T",
    "8" => "B"
  );

  public $file_type;

  /**
   * Define if the class can be used
   *
   * @return bool
   */
  static function canBeUsed() {
    return (CAppUI::conf("dPfiles tika host") !== "");
  }

  /**
   * Decode a picture then return an array
   *
   * @param String $file Path to the picture
   *
   * @return array|bool
   * @throws Exception
   */
  public function decodeFile($file) {
    if (!$this->canBeUsed() || !$file || !file_get_contents($file)) {
      return false;
    }

    try {
      $file_parser = new CFileParser();
    }
    catch (Exception $e) {
      return array(
        "error"    => "connection_error",
        "continue" => 1
      );
    }

    return $this->decodeText($file_parser->getContent($file));
  }


  /**
   * Decodes the last name of the person
   *
   * @param string $text - the text to detect
   *
   * @return string|null
   */
  public function decodeLastName($text) {
    $prefix_idcard = "IDFRA";

    $line = $this->postTraitment($text[sizeof($text) - 2], 'str');
    $line = preg_replace('/[^A-Za-z0-9<]/', '', $line); // Removes special chars a part from stripes
    // Try with IDFRA{name}, DFRA{name}, FRA{name} ...
    for ($i = strlen($prefix_idcard); $i > 1; $i--) {
      $substr_prefix = substr($prefix_idcard, -$i);

      // Remove stripes (<) and return
      if (strpos($line, $substr_prefix) !== false) {
        $substr_without_prefix = substr($line, $i);
        $pos_first_stripe      = strpos($substr_without_prefix, "<");
        $last_name             = substr($substr_without_prefix, 0, $pos_first_stripe);

        return $last_name;
      }
    }

    return null;
  }

  /**
   * Decodes the first name of the person
   *
   * @param string $text - the text to detect
   *
   * @return string|null
   */
  public function decodeFirstName($text) {
    $line = $text[sizeof($text) - 1];
    preg_match("/[0-9]+([A-Za-z]+)<+/", $line, $matches);
    if (sizeof($matches) > 0) {
      return self::postTraitment($matches[1], "str"); // Capture
    }

    return null;
  }

    /**
     * Decodes the birthday of the person
     *
     * @param string $text - the text to detect
     *
     * @return string|null
     */
    public function decodeBirth($text)
    {
        $line = $text[sizeof($text) - 1];

        preg_match("/<+([A-Za-z0-9]{7})[MmFm]/", $line, $matches);

        // When there are several first names
        if (!sizeof($matches)) {
            preg_match("/[<A-Za-z]+([0-9]{6})/", $line, $matches);
        }

        if (sizeof($matches) > 0) {
            $matches[1] = str_replace("D", "0", $matches[1]);

            return self::postTraitment($matches[1], "int"); // Capture
        }

        return null;
    }

  /**
   * Decodes the gender of the person
   *
   * @param string $text - the text to detect
   *
   * @return string|null
   */
  public function decodeGender($text) {
    $line = $text[sizeof($text) - 1];
    preg_match("/<+[A-Za-z0-9]+[MmF]/", $line, $matches);
    if (sizeof($matches) > 0 && isset($matches[0])) {
      return substr($matches[0], -1, 1); // Capture
    }

    return null;
  }

    /**
     * Decodes the end of validity
     *
     * @param string $text - the text to detect
     *
     * @return string|null
     */
    public function decodeEndValidity($text) {
        $line = $text[sizeof($text) - 1];

        preg_match("/(^[0-9]{4})/", $line, $matches);
        if (sizeof($matches) > 0) {
            $year         = substr($matches[1], 0, 2);
            $month        = substr($matches[1], 2, 4);
            $created_date = CMbDT::date("20{$year}-{$month}-01");
            $end_validity = CMbDT::date("+ 10 YEARS", $created_date);

            // Validity of 15 years for identity cards
            if (($created_date > "2004-01-01") && ($created_date < "2021-08-02")) {
                $end_validity = CMbDT::date("+ 15 YEARS", $created_date);
            }

            return $end_validity;
        }

        return null;
    }

  /**
   * Decode a text then return an array
   *
   * @param String $text Text to convert
   *
   * @return array|bool
   */
    public function decodeText($text)
    {
        $text = str_replace(" ", "", $text);
        $text = explode("\n", $text);
        $text = array_map_recursive("trim", $text);
        CMbArray::removeValue("", $text);
        $text = array_values($text); // Reset keys

        $last_name    = self::decodeLastName($text);
        $first_name   = self::decodeFirstName($text);
        $birth        = self::decodeBirth($text);
        $sexe         = self::decodeGender($text);

        $data = [
            "nom_jeune_fille"   => $last_name,
            "prenom"            => $first_name,
            "naissance"         => $birth,
            "sexe"              => $sexe,
        ];

        return $data;
    }

    /**
   * Add a traitment to enhance the data quality
   *
   * @param String $string        String to transform
   * @param String $expected_type Type of string used (str, int or null)
   *
   * @return mixed|string
   */
  public function postTraitment($string, $expected_type = null) {
    if ($expected_type === "str") {
      $string = str_replace(
        array_keys($this->char_trans),
        array_values($this->char_trans),
        $string
      );
    }
    elseif ($expected_type === "int") {
      $string = str_replace(
        array_values($this->char_trans),
        array_keys($this->char_trans),
        $string
      );
    }

    return strtoupper($string);
  }
}
