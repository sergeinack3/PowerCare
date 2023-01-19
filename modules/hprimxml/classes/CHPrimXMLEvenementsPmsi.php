<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CMbObject;

/**
 * Class CHPrimXMLEvenementsPmsi
 * PMSI
 */

class CHPrimXMLEvenementsPmsi extends CHPrimXMLEvenementsServeurActivitePmsi {
  /**
   * Construct
   *
   * @return CHPrimXMLEvenementsPmsi
   */
  function __construct() {
    $this->sous_type = "evenementPMSI";
    $this->evenement = "evt_pmsi";

    parent::__construct("evenementPmsi", "msgEvenementsPmsi");
  }

  /**
   * @inheritdoc
   */
  function generateEnteteMessage($type = null, $version = true , $group_id = null) {
    parent::generateEnteteMessage("evenementsPMSI");
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $mbSejour, $referent = false) {
    $evenementsPMSI = $this->documentElement;

    $evenementPMSI = $this->addElement($evenementsPMSI, "evenementPMSI");

    // Ajout du patient
    $mbPatient = $mbSejour->_ref_patient;
    $patient = $this->addElement($evenementPMSI, "patient");
    $this->addPatient($patient, $mbPatient, false, true);
    
    // Ajout de la venue, c'est-à-dire le séjour
    $venue = $this->addElement($evenementPMSI, "venue");
    $this->addVenue($venue, $mbSejour, false, true);
    
    if ($mbSejour->type == "ssr") {
      // Ajout du contenu rhss
      $rhss = $this->addElement($evenementPMSI, "rhss");
      $this->addSsr($rhss, $mbSejour);
    }
    else {
      // Ajout de la saisie délocalisée
      $saisie = $this->addElement($evenementPMSI, "saisieDelocalisee");
      $this->addSaisieDelocalisee($saisie, $mbSejour);
    }

    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * Get content XML
   *
   * @return array
   */
  public function getContentsXML(): array
  {
      $data  = [];
      $xpath = new CHPrimXPath($this);

      $evenementPMSI = $xpath->queryUniqueNode("/hprim:evenementsPMSI/hprim:evenementPMSI");

      $data['patient']         = $xpath->queryUniqueNode("hprim:patient", $evenementPMSI);
      $data['idSourcePatient'] = $this->getIdSource($data['patient']);
      $data['idCiblePatient']  = $this->getIdCible($data['patient']);

      $data['venue']           = $xpath->queryUniqueNode("hprim:venue", $evenementPMSI);
      $data['idSourceVenue']   = $this->getIdSource($data['venue']);
    $data['idCibleVenue']    = $this->getIdCible($data['venue']);
    
    return $data; 
  }
}
