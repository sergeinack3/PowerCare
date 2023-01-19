<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * CHL7v3EventSVSRetrieveValueSet
 * Retrieve Value Set
 */
class CHL7v3EventSVSRetrieveValueSet extends CHL7v3EventSVS implements CHL7EventSVSRetrieveValueSet {
  /** @var string */
  public $_event_request = "RetrieveValueSet";
  public $_event_name    = "RetrieveValueSet";

  /**
   * Build Retrieve Value Set event
   *
   * @param CMbObject $object compte rendu
   *
   * @see parent::build()
   *
   * @throws CMbException
   * @return void
   */
  function build($object) {
    parent::build($object);

    $data   = $object->_data;

    $dom = new CHL7v3MessageXML("utf-8", $this->version);

    $RetrieveValueSetRequest = $dom->addElement($dom, "ns1:RetrieveValueSetRequest", null, "urn:ihe:iti:svs:2008");

    $ValueSet = $dom->addElement($RetrieveValueSetRequest, "ns1:ValueSet", null, "urn:ihe:iti:svs:2008");
    $dom->addValueSet($ValueSet, "id"      , "id"      , $data);
    $dom->addValueSet($ValueSet, "version" , "version" , $data);
    $dom->addValueSet($ValueSet, "xml:lang", "lang"    , $data);

    $this->message = $dom->saveXML();

    $this->updateExchange(false);
  }
}