<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\CMbXMLDocument;

/**
 * Classe xml pour les jeux de valeurs
 */
class CXDSXmlJvDocument extends CMbXMLDocument {

  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct("UTF-8");
    $this->formatOutput = true;
    $this->addElement($this, "jeuxValeurs");
  }

  /**
   * Ajoute une ligne dans le xml
   *
   * @param String $oid  OID
   * @param String $id   Identifiant
   * @param String $name Nom
   *
   * @return void
   */
  function appendLine($oid, $id, $name) {
    $element = $this->addElement($this->documentElement, "line");
    $this->addAttribute($element, "id"  , $id);
    $this->addAttribute($element, "oid" , $oid);
    $this->addAttribute($element, "name", $name);
  }
}