<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * classe Association représentant l'association UpdateAvailabilityStatus
 */
class CXDSUpdateAvailabilityStatus extends CXDSAssociationOld {

  /** @var CXDSSlot */
  public $newStatus;
  /** @var CXDSSlot */
  public $originalStatus;

  /**
   * @see parent::__construct()
   */
  function __construct($id, $sourceObject, $targetObject) {
    parent::__construct($id, $sourceObject, $targetObject);
    $this->associationType = "urn:ihe:iti:2010:AssociationType:UpdateAvailabilityStatus";
  }

  /**
   * Setter NewStatus
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setNewStatus($value) {
    $this->newStatus = new CXDSSlot("NewStatus", $value);
  }

  /**
   * Setter originalStatus
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setOriginalStatus($value) {
    $this->originalStatus = new CXDSSlot("OriginalStatus", $value);
  }

  /**
   * @see parent::toXML()
   */
  function toXML() {
    $xml = parent::toXML();

    if ($this->newStatus) {
      $xml->importDOMDocument($xml->documentElement, $this->newStatus->toXML());
    }

    if ($this->originalStatus) {
      $xml->importDOMDocument($xml->documentElement, $this->originalStatus->toXML());
    }

    return $xml;
  }
}
