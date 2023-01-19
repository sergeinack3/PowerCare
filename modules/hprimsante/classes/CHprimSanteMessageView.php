<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMNode;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CSmartyDP;

/**
 * Description
 */
class CHprimSanteMessageView implements IShortNameAutoloadable {

  /** @var array The header */
  public $header;

  /** @var array  */
  public $segments;

  /** @var array The footer */
  public $footer;

  /** @var CHPrimSanteMessageXPath */
  protected $_xpath;

  /**
   * CHprimSanteMessageView constructor.
   *
   * @param CHPrimSanteMessageXML $dom The XML
   */
  public function __construct($dom) {
    $this->header = $this->parseHeader($dom->queryNode('H', null, $data, true));
    $this->footer = $this->parseFooter($dom->queryNode('L', null, $data, true));

    $this->segments = array();
    foreach ($dom->queryNodes('ORU.PATIENT_RESULT', null, $data, true) as $_node) {
      $this->segments[] = $this->parsePatient($_node);
    }
  }

  /**
   * Return the message data in array format
   *
   * @return array
   */
  public function toArray() {
    return array(
      'header'    => $this->header,
      'segments'  => $this->segments,
      'footer'    => $this->footer
    );
  }

  /**
   * Return the lab results in HTML format
   *
   * @return string
   */
  public function toHTML() {
    $smarty = new CSmartyDP('modules/hprimsante');
    $smarty->assign('message', $this->toArray());

    return $smarty->fetch('inc_display_lab_results.tpl');
  }

  /**
   * @param DOMNode $node The header node
   *
   * @return array
   */
  protected function parseHeader($node) {
    $xpath = new CHPrimSanteMessageXPath($node->ownerDocument);
    $header = array();

    $header['sender'] = array(
      'id'   => $xpath->queryTextNode('H.4/HD.1', $node),
      'name' => $xpath->queryTextNode('H.4/HD.2', $node)
    );

    $header['sender_address'] = array(
      'street'  => $xpath->queryTextNode('H.5/AD.1', $node),
      'comp'    => $xpath->queryTextNode('H.5/AD.2', $node),
      'city'    => $xpath->queryTextNode('H.5/AD.3', $node),
      'state'   => $xpath->queryTextNode('H.5/AD.4', $node),
      'postal'  => $xpath->queryTextNode('H.5/AD.5', $node),
      'country' => $xpath->queryTextNode('H.5/AD.6', $node),
    );

    $header['sender_phone'] = array();
    foreach ($xpath->query('H.7', $node) as $_node) {
      $header['sender_phone'][] = $xpath->queryTextNode('.', $_node);
    }

    $header['prescriptor'] = array(
      'id'   => $xpath->queryTextNode('H.9/HD.1', $node),
      'name' => $xpath->queryTextNode('H.9/HD.2', $node)
    );

    $header['comment'] = $xpath->queryTextNode('H.10', $node);

    $header['date'] = $this->parseDate($xpath->queryTextNode('H.11/TS.1', $node));

    return $header;
  }

  /**
   * @param DOMNode $node The footer node
   *
   * @return array
   */
  protected function parseFooter($node) {
    $xpath = new CHPrimSanteMessageXPath($node->ownerDocument);
    $footer = array(
      'patients_number' => $xpath->queryTextNode('L.3', $node),
      'segments_number' => $xpath->queryTextNode('L.4', $node)
    );

    return $footer;
  }

  /**
   * @param DOMNode $node The footer node
   *
   * @return array
   */
  protected function parsePatient($node) {
    $xpath = new CHPrimSanteMessageXPath($node->ownerDocument);
    $pnode = $xpath->queryUniqueNode('P', $node);

    $patient = array();
    $patient['last_name']   = $xpath->queryTextNode('P.5/PN.1', $pnode);
    $patient['first_name']  = $xpath->queryTextNode('P.5/PN.2', $pnode);
    $patient['second_name'] = $xpath->queryTextNode('P.5/PN.3', $pnode);
    $patient['surname']     = $xpath->queryTextNode('P.5/PN.4', $pnode);
    $patient['title']       = $xpath->queryTextNode('P.5/PN.5', $pnode);
    $patient['diploma']     = $xpath->queryTextNode('P.5/PN.6', $pnode);
    $patient['birth_name']  = $xpath->queryTextNode('P.6', $pnode);

    $patient['birth_date'] = $this->parseDate($xpath->queryTextNode('P.7', $pnode));

    $patient['sex'] = $xpath->queryTextNode('P.8', $pnode);

    $patient['address'] = array(
      'street'  => $xpath->queryTextNode('H.5/AD.1', $pnode),
      'comp'    => $xpath->queryTextNode('H.5/AD.2', $pnode),
      'city'    => $xpath->queryTextNode('H.5/AD.3', $pnode),
      'state'   => $xpath->queryTextNode('H.5/AD.4', $pnode),
      'postal'  => $xpath->queryTextNode('H.5/AD.5', $pnode),
      'country' => $xpath->queryTextNode('H.5/AD.6', $pnode),
    );

    $patient['analysis'] = array();
    foreach ($xpath->query('ORU.ORDER_OBSERVATION', $node) as $_node) {
      $patient['analysis'][] = $this->parseAnalysis($_node);
    }

    return $patient;
  }

  /**
   * @param DOMNode $node The analysis node
   *
   * @return array
   */
  protected function parseAnalysis($node) {
    $xpath = new CHPrimSanteMessageXPath($node->ownerDocument);
    $anode = $xpath->queryUniqueNode('OBR', $node);

    $analysis = array();

    $analysis['codes']  = array();
    foreach ($xpath->query('OBR.4/CE.1', $anode) as$_node) {
      $analysis['codes'][]  = $xpath->queryTextNode('.', $_node);
    }
    $analysis['names']  = array();
    foreach ($xpath->query('OBR.4/CE.2', $anode) as$_node) {
      $analysis['names'][]  = $xpath->queryTextNode('.', $_node);
    }
    $analysis['refs']  = array();
    foreach ($xpath->query('OBR.4/CE.3', $anode) as$_node) {
      $analysis['refs'][]  = $xpath->queryTextNode('.', $_node);
    }

    $analysis['date_consideration'] = $this->parseDate($xpath->queryTextNode('OBR.6/TS.1', $anode));
    $analysis['date_acts'] = $this->parseDate($xpath->queryTextNode('OBR.7/TS.1', $anode));

    $analysis['date_sample'] = $this->parseDate($xpath->queryTextNode('OBR.8/TS.1', $anode));

    $analysis['prescriptor_code'] = $xpath->queryTextNode('OBR.16/PRE.1/CNA.1', $anode);
    $analysis['prescriptor_name'] = $xpath->queryTextNode('OBR.16/PRE.2/CNA.1', $anode);

    $analysis['date_results'] = $this->parseDate($xpath->queryTextNode('OBR.22/TS.1', $anode));

    $analysis['date'] = '';
    if ($analysis['date_results'] != '') {
      $analysis['date'] = $analysis['date_results'];
    }
    elseif ($analysis['date_sample'] != '') {
      $analysis['date'] = $analysis['date_sample'];
    }
    elseif ($analysis['date_acts'] != '') {
      $analysis['date'] = $analysis['date_acts'];
    }
    elseif ($analysis['date_consideration'] != '') {
      $analysis['date'] = $analysis['date_consideration'];
    }

    $analysis['status'] = $this->parseAnalysisStatus($xpath->queryTextNode('OBR.25', $anode));

    $analysis['interpretor_code']       = $xpath->queryTextNode('OBR.32/CNA.1', $anode);
    $analysis['interpretor_last_name']  = $xpath->queryTextNode('OBR.32/CNA.2/PN.1', $anode);
    $analysis['interpretor_first_name'] = $xpath->queryTextNode('OBR.32/CNA.2/PN.2', $anode);

    $analysis['assistant_code']       = $xpath->queryTextNode('OBR.33/CNA.1', $anode);
    $analysis['assistant_last_name']  = $xpath->queryTextNode('OBR.33/CNA.2/PN.1', $anode);
    $analysis['assistant_first_name'] = $xpath->queryTextNode('OBR.33/CNA.2/PN.2', $anode);

    $analysis['technician_code']        = $xpath->queryTextNode('OBR.34/CNA.1', $anode);
    $analysis['technician_last_name']   = $xpath->queryTextNode('OBR.34/CNA.2/PN.1', $anode);
    $analysis['technician_first_name']  = $xpath->queryTextNode('OBR.34/CNA.2/PN.2', $anode);

    $analysis['operator_code']        = $xpath->queryTextNode('OBR.35/CNA.1', $anode);
    $analysis['operator_last_name']   = $xpath->queryTextNode('OBR.35/CNA.2/PN.1', $anode);
    $analysis['operator_first_name']  = $xpath->queryTextNode('OBR.35/CNA.2/PN.2', $anode);

    $analysis['observations'] = array();
    foreach ($xpath->query('ORU.OBSERVATION/OBX', $node) as $_node) {
      $analysis['observations'][] = $this->parseObservation($_node);
    }

    return $analysis;
  }

  /**
   * Parse the status of the analysis
   *
   * @param string $value The status code
   *
   * @return array
   */
  protected function parseAnalysisStatus($value) {
    $status = array('code' => $value);

    switch ($value) {
      case 'F':
        $status['desc']   = 'Résultat final';
        $status['color']  = 'green';
        break;
      case 'P':
        $status['desc']   = 'Résultats non validés biologiquement';
        $status['color']  = '#fb0';
        break;
      case 'M':
        $status['desc']   = 'Résultats partiels validés biologiquement';
        $status['color']  = '#fb0';
        break;
      case 'I':
        $status['desc']   = 'Echantillons reçus, analyses non effectuées';
        $status['color']  = '#fb0';
        break;
      case 'R':
        $status['desc']   = 'Résultats non validés techniquement';
        $status['color']  = '#fb0';
        break;
      case 'C':
        $status['desc']   = 'Correction de résultats déjà transmis';
        $status['color']  = 'green';
        break;
      case 'O':
        $status['desc']   = 'Echantillons non reçus';
        $status['color']  = 'firebrick';
        break;
      case 'D':
        $status['desc']   = 'Demande annulée par le demandeur';
        $status['color']  = 'firebrick';
        break;
      case 'X':
        $status['desc']   = 'Demande annulée par l\'exécutant';
        $status['color']  = 'firebrick';
        break;
      default:
        $status['desc']   = 'Status inconnue';
        $status['color']  = 'firebrick';
    }

    return $status;
  }

  /**
   * @param DOMNode $node The observation node
   *
   * @return array
   */
  protected function parseObservation($node) {
    $xpath = new CHPrimSanteMessageXPath($node->ownerDocument);

    $observation = array();

    $observation['type'] = $xpath->queryTextNode('OBX.2', $node);

    $observation['test_code']   = $xpath->queryTextNode('OBX.3/CE.1', $node);
    $observation['test_name']   = $xpath->queryTextNode('OBX.3/CE.2', $node);
    $observation['test_ref']    = $xpath->queryTextNode('OBX.3/CE.3', $node);
    $observation['test_code_2'] = $xpath->queryTextNode('OBX.3/CE.4', $node);
    $observation['test_name_2'] = $xpath->queryTextNode('OBX.3/CE.5', $node);
    $observation['test_ref_2']  = $xpath->queryTextNode('OBX.3/CE.6', $node);

    switch ($observation['type']) {
      case 'AD':
        $observation['result'] = $this->parseObservationResultAD($xpath->queryTextNode('OBX.5', $node));

        break;
      case 'CE':
        $observation['result'] = $this->parseObservationResultCE($xpath->queryTextNode('OBX.5', $node));
        break;
      case 'CNA':
        $observation['result'] = $this->parseObservationResultCNA($xpath->queryTextNode('OBX.5', $node));
        break;
      case 'DT':
        $observation['result'] = $this->parseDate($xpath->queryTextNode('OBX.5', $node));
        break;
      case 'PN':
        $observation['result']['last_name']   = $xpath->queryTextNode('OBX.5/PN.1', $node);
        $observation['result']['first_name']  = $xpath->queryTextNode('OBX.5/PN.2', $node);
        $observation['result']['second_name'] = $xpath->queryTextNode('OBX.5/PN.3', $node);
        $observation['result']['surname']     = $xpath->queryTextNode('OBX.5/PN.4', $node);
        $observation['result']['title']       = $xpath->queryTextNode('OBX.5/PN.5', $node);
        $observation['result']['diploma']     = $xpath->queryTextNode('OBX.5/PN.6', $node);
        break;
      case 'CK':
      case 'NM':
      case 'ST':
      case 'TN':
      case 'TX':
      default:
        $observation['result'] = $xpath->queryTextNode('OBX.5', $node);
    }

    $observation['unit'] = $xpath->queryTextNode('OBX.6/CE.1', $node);

    $observation['normal'] = $xpath->queryTextNode('OBX.7', $node);

    $observation['abnormal'] = array();
    foreach ($xpath->query('OBX.8', $node) as $_node) {
      $observation['abnormal'][] = $this->parseObservationAbnormality($xpath->queryTextNode('.', $_node));
    }

    $observation['status'] = $this->parseObservationStatus($xpath->queryTextNode('OBX.11', $node));

    $observation['validator_code'] = $xpath->queryTextNode('OBX.16/CE.1', $node);
    $observation['validator_name'] = $xpath->queryTextNode('OBX.16/CE.2', $node);

    return $observation;
  }

  /**
   * Parse the result field of AD (address) type
   *
   * @param string $value The content of the field result
   *
   * @return array
   */
  protected function parseObservationResultAD($value) {
    $address = array();

    $fields = explode('\\S\\', $value);
    if (array_key_exists(0, $fields)) {
      $address['street'] = $fields[0];
    }
    if (array_key_exists(1, $fields)) {
      $address['comp'] = $fields[1];
    }
    if (array_key_exists(2, $fields)) {
      $address['city'] = $fields[2];
    }
    if (array_key_exists(3, $fields)) {
      $address['state'] = $fields[3];
    }
    if (array_key_exists(4, $fields)) {
      $address['postal'] = $fields[4];
    }
    if (array_key_exists(5, $fields)) {
      $address['country'] = $fields[5];
    }

    return $address;
  }

  /**
   * Parse the result field of CE type
   *
   * @param string $value The content of the field result
   *
   * @return array
   */
  protected function parseObservationResultCE($value) {
    $cna = array();

    $fields = explode('\\S\\', $value);
    if (array_key_exists(0, $fields)) {
      $cna['code'] = $fields[0];
    }
    if (array_key_exists(1, $fields)) {
      $cna['name'] = $fields[1];
    }
    if (array_key_exists(2, $fields)) {
      $cna['ref'] = $fields[2];
    }
    if (array_key_exists(3, $fields)) {
      $cna['code_2'] = $fields[0];
    }
    if (array_key_exists(4, $fields)) {
      $cna['name_2'] = $fields[1];
    }
    if (array_key_exists(5, $fields)) {
      $cna['ref_2'] = $fields[2];
    }

    return $cna;
  }

  /**
   * Parse the result field of CNA type
   *
   * @param string $value The content of the field result
   *
   * @return array
   */
  protected function parseObservationResultCNA($value) {
    $cna = array();

    $fields = explode('\\S\\', $value);
    if (array_key_exists(0, $fields)) {
      $cna['code'] = $fields[0];
    }
    if (array_key_exists(1, $fields)) {
      $cna['name'] = $fields[1];
    }
    if (array_key_exists(2, $fields)) {
      $cna['ref'] = $fields[2];
    }

    return $cna;
  }

  /**
   * Parse the result field of CNA type
   *
   * @param string $value The content of the field result
   *
   * @return array
   */
  protected function parseObservationAbnormality($value) {
    $abnormality = array('code' => $value);

    switch ($value) {
      case 'L':
        $abnormality['desc'] = 'Inférieur à la normale basse';
        $abnormality['color'] = 'firebrick';
        break;
      case 'LL':
        $abnormality['desc'] = 'Inférieur à la limite panique basse';
        $abnormality['color'] = 'firebrick';
        break;
      case 'H':
        $abnormality['desc'] = 'Supérieur à la normale haute';
        $abnormality['color'] = 'firebrick';
        break;
      case 'HH':
        $abnormality['desc'] = 'Supérieur à la limite panique haute';
        $abnormality['color'] = 'firebrick';
        break;
      case '<':
        $abnormality['desc'] = 'Inférieur à la valeur minimale mesurable';
        $abnormality['color'] = 'firebrick';
        break;
      case '>':
        $abnormality['desc'] = 'Supérieur à la valeur maximale mesurable';
        $abnormality['color'] = 'firebrick';
        break;
      case 'A':
        $abnormality['desc'] = 'Anormal';
        $abnormality['color'] = '#fb0';
        break;
      case 'AA':
        $abnormality['desc'] = 'Très anormal';
        $abnormality['color'] = 'firebrick';
        break;
      case 'U':
        $abnormality['desc'] = 'Forte augmentation par rapport au résultat antérieur';
        $abnormality['color'] = '#fb0';
        break;
      case 'D':
        $abnormality['desc'] = 'Forte diminution par rapport au résultat antérieur';
        $abnormality['color'] = '#fb0';
        break;
      case 'B':
        $abnormality['desc'] = 'Amélioration par rapport au résultat antérieur';
        $abnormality['color'] = 'green';
        break;
      case 'W':
        $abnormality['desc'] = 'Dégradation par rapport au résultat antérieur';
        $abnormality['color'] = 'firebrick';
        break;
      case 'N':
        $abnormality['desc'] = 'Normal';
        $abnormality['color'] = 'green';
        break;
      default:
        $abnormality['desc'] = '';
    }

    return $abnormality;
  }

  /**
   * Parse the status, and sets it's description and display
   *
   * @param string $value The status
   *
   * @return array
   */
  protected function parseObservationStatus($value) {
    $status = array(
      'name' => $value
    );

    switch ($value) {
      case 'R':
        $status['desc'] = 'Résultat non validé techniquement';
        $status['color'] = 'firebrick';
        break;
      case 'P':
        $status['desc'] = 'Résultat non validé biologiquement';
        $status['color'] = 'firebrick';
        break;
      case 'F':
        $status['desc'] = 'Résultat validé biologiquement';
        $status['color'] = 'green';
        break;
      case 'C':
        $status['desc'] = 'Correction d\'un résultat validé déjà transmis';
        $status['color'] = '#fb0';
        break;
      case 'I':
        $status['desc'] = 'Echantillon reçu, analyse non faite';
        $status['color'] = 'firebrick';
        break;
      case 'D':
        $status['desc'] = 'Annulation d\'un résultat tranmis précédemment';
        $status['color'] = 'firebrick';
        break;
      case 'X':
        $status['desc'] = 'Annulation de la demande de résultat';
        $status['color'] = 'firebrick';
        break;
      case 'U':
        $status['desc'] = 'Validation d\'un résultat déjà transmit sans modification';
        $status['color'] = 'green';
        break;
      default:
        $status['desc'] = 'Aucun status renseigné';
        $status['color'] = 'firebrick';
    }

    return $status;
  }

  /**
   * Return the date in ISO format
   *
   * @param string $value The date to convert
   *
   * @return string
   */
  protected static function parseDate($value) {
    $date = '';

    if (strlen($value) == 8) {
      $date = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
    }
    elseif (strlen($value) == 12) {
      $date = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2)
        . ' ' . substr($value, 8, 2) . ':' . substr($value, 10, 2) . ':00';
    }

    return $date;
  }
}
