<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;
use DOMElement;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * CGroups import import class
 */
class CGroupsImport extends CMbXMLObjectImport {
  protected $name_suffix;

  protected $imported = array();

  protected $import_order = array(
    "//object[@class='CGroups']",
    "//object[@class='CService']",
    "//object[@class='CFunctions']",
    "//object[@class='CUniteFonctionnelle']",
    "//object[@class='CUser']",
    "//object[@class='CBlocOperatoire']",
    "//object[@class='CSalle']",
    "//object",
  );

  /**
   * @inheritdoc
   */
  protected function storeIdExt(CMbObject $object, $map_to) {
    if ($object instanceof CIdSante400) {
      return;
    }

    $tag = $this->getImportTag();
    // Rattachement d'un ID externe
    $idex = CIdSante400::getMatch($object->_class, $tag, $map_to, $object->_id);

    if (!$idex->_id) {
      $idex->store();
      CAppUI::stepAjax("Idex '%s' créé sur '%s'", UI_MSG_OK, $idex->id400, $object);
    }
  }

  /**
   * @inheritdoc
   */
  function importObject(DOMElement $element) {
    $id = $element->getAttribute("id");

    if (isset($this->imported[$id])) {
      return;
    }

    $_class = $element->getAttribute("class");
    $group  = CGroups::loadCurrent();

    if ($_class == "CGroups") {
      $this->storeIdExt($group, $id);

      $this->map[$id] = $group->_guid;

      $this->imported[$id] = true;
    }

    $this->name_suffix = " (import du " . CMbDT::dateTime() . ")";

    $map_to = isset($this->map[$id]) ? $this->map[$id] : null;

    if (!$map_to) {
      return;
    }

    switch ($_class) {
      case "CGroups":
        CAppUI::stepAjax("Etablissement de rattachement : '%s'", UI_MSG_OK, $group->text);

        $map_to = $group->_guid;
        break;

      case "CUser":
        if ($map_to === "") {
          break;
        }

        /** @var CMbObject $_object */
        $_object = CStoredObject::loadFromGuid($map_to);

        $this->storeIdExt($_object, $id);

        $map_to = $_object->_guid;

        break;

      case "CSalle":
      case "CFunctions":
      case "CService":
      case "CBlocOperatoire":
      case "CUniteFonctionnelle":
        if ($map_to === "__ignore__") {
          break;
        }

        if ($map_to === "__create__") {
          $_object = $this->createStructure($element, $id);
          if ($_object === null) {
            break;
          }
        }
        else {
          /** @var CMbObject $_object */
          $_object = CStoredObject::loadFromGuid($map_to);

          $this->storeIdExt($_object, $id);
        }

        $map_to = $_object->_guid;
        break;

      default:
        // Ignore object
        break;
    }

    $this->map[$id] = $map_to;

    $this->imported[$id] = true;
  }

  /**
   * @param DOMElement $element XML element
   * @param int        $id      Id
   *
   * @return CBlocOperatoire|CFunctions|CMbObject|CSalle|CService|null
   */
  function createStructure($element, $id) {
    /** @var CSalle|CFunctions|CService|CBlocOperatoire $_object */
    $_object = $this->getObjectFromElement($element);

    if ($msg = $_object->store()) {
      if ($_object instanceof CGroups || $_object instanceof CFunctions) {
        $_object->text .= $this->name_suffix;
      }
      elseif ($_object instanceof CSalle || $_object instanceof CService || $_object instanceof CBlocOperatoire) {
        $_object->nom .= $this->name_suffix;
      }
    }

    if ($msg = $_object->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
      return null;
    }

    $this->storeIdExt($_object, $id);
    CAppUI::stepAjax("%s '%s' créé", UI_MSG_OK, CAppUI::tr($_object->_class), $_object);

    return $_object;
  }
}