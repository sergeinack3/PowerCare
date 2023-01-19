<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

/**
 * Class CHPrimXMLAcquittements
 */
class CHPrimXMLAcquittements extends CHPrimXMLDocument {
  public $_codes_erreurs = array();

  /**
   * Get acknowledgment event
   *
   * @param CHPrimXMLEvenements $dom_evt Event
   *
   * @return CHPrimXMLAcquittementsFraisDivers|CHPrimXMLAcquittementsPatients|CHPrimXMLAcquittementsPmsi|
   * CHPrimXMLAcquittementsServeurActes|CHPrimXMLAcquittementsServeurIntervention|null
   */
  static function getAcquittementEvenementXML(CHPrimXMLEvenements $dom_evt) {
    // Message événement patient
    if ($dom_evt instanceof CHPrimXMLEvenementsPatients) {
      return new CHPrimXMLAcquittementsPatients();
    }

    if ($dom_evt instanceof CHPrimXMLEvenementsFraisDivers) {
      return new CHPrimXMLAcquittementsFraisDivers();
    }

    // Message serveur activité PMSI
    if ($dom_evt instanceof CHPrimXMLEvenementsServeurActivitePmsi) {
      return CHPrimXMLAcquittementsServeurActivitePmsi::getEvtAcquittement($dom_evt);
    }

    return null;
  }

  /**
   * Generate acknowledgement
   *
   * @param string $statut       Status code
   * @param array  $codes        Codes
   * @param string $commentaires Comments
   * @param string $mbObject     Object
   * @param array  $data         Objects
   *
   * @return string
   */
  function generateAcquittements($statut, $codes, $commentaires = null, $mbObject = null, $data = array()) {
  }
}
