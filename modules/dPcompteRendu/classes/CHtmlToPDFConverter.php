<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;

/**
 * Factory pour la conversion html vers pdf
 */
abstract class CHtmlToPDFConverter implements IShortNameAutoloadable {
  /** @var CHtmlToPDFConverter */
  static $instance;
  static $_page_ordonnance;

  public $options;
  public $html;
  public $result;

  /**
   * Fonction d'initialisation
   *
   * @param string $class   frontend à utiliser
   * @param array  $options Options
   *
   * @throws CMbException
   * @return void
   */
  static function init($class, $options = array()) {
    self::$instance = new $class;

    //  Vérifier l'existance de la sous-classe
    if (!is_subclass_of(self::$instance, CHtmlToPDFConverter::class)) {
      throw new CMbException("$class not a subclass of CHtmlToPDFConverter");
    }

    self::$instance->options = $options;
  }

  /**
   * Conversion d'une source html en pdf
   *
   * @param string $html        source html
   * @param string $format      format de la page
   * @param string $orientation orientation de la page
   *
   * @throws CMbException
   * @return string|null
   */
  static function convert($html, $format, $orientation) {
    $instance = self::$instance;
    if (!$instance) {
      return null;
    }
    
    $instance->html = $html;
    $instance->prepare($format, $orientation);
    $instance->render();

    if (!$instance->result) {
      throw new CMbException("Error while generating the PDF");
    }

    return $instance->result;
  }
  
  /**
   * Préparation de la conversion
   * 
   * @param string $format      format de la page
   * @param string $orientation orientation de la page
   * 
   * @return void
   */
  function prepare($format, $orientation) {
  }
  
  /**
   * Création du pdf à partir de la source html
   * 
   * @return void 
   */
  function render() {
  }
}
