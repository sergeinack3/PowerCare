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

/**
 * CHL7v3EventSVSRetrieveValueSet
 * Retrieve Multiple Value Sets
 */
class CHL7v3EventSVSRetrieveMultipleValueSets extends CHL7v3EventSVS implements CHL7EventSVSRetrieveMultipleValueSets {
  /** @var string */
  public $_event_name = "RetrieveMultipleValueSets";

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

    /*$xml = new CXDSXmlDocument();

    $message = $xml->createDocumentRepositoryElement($xml, "ProvideAndRegisterDocumentSetRequest");

    $factory = CCDAFactory::factory($object);
    $factory->old_version = $this->old_version;
    $factory->old_id      = $this->old_id;
    $factory->receiver    = $this->_receiver;
    $cda = $factory->generateCDA();
    try {
      CCdaTools::validateCDA($cda);
    }
    catch (CMbException $e) {
      throw $e;
    }

    $xds           = CXDSFactory::factory($factory);
    $xds->type     = $this->type;
    $xds->doc_uuid = $this->uuid;
    switch ($this->hide) {
      case "0":
        $xds->hide_ps = true;
        break;
      case "1":
        $xds->hide_patient = true;
        break;
      default:
        $xds->hide_patient = false;
    }
    $xds->extractData();
    $xds->xcn_mediuser         = $this->xcn_mediuser      ? $this->xcn_mediuser      : $xds->xcn_mediuser;
    $xds->xon_etablissement    = $this->xon_etablissement ? $this->xon_etablissement : $xds->xon_etablissement;
    $xds->specialty            = $this->specialty         ? $this->specialty         : $xds->specialty;
    $xds->practice_setting     = $this->pratice_setting   ? $this->pratice_setting   : $xds->practice_setting;
    $xds->health_care_facility = $this->healtcare         ? $this->healtcare         : $xds->health_care_facility;

    $header_xds = $xds->generateXDS41();
    $xml->importDOMDocument($message, $header_xds);

    //ajout d'un document
    $document = $xml->createDocumentRepositoryElement($message, "Document");
    $xml->addAttribute($document, "id", $xds->uuid["extrinsic"]);
    $document->nodeValue = base64_encode($cda);

    //ajout de la signature
    CEAIHandler::notify("AfterBuild", $this, $xml, $factory, $xds);

    $this->message = $xml->saveXML($message);
    $this->updateExchange(false);*/
  }
}