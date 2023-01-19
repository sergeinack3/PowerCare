<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CMbFieldSpecFact;
use Ox\Mediboard\System\CContentAny;

/**
 * Class CExchangeAny
 * Echange Tabular
 */

class CExchangeAny extends CExchangeDataFormat {
  static $messages = array(
    "None" => "CExchangeAny", 
  );
  
  static $evenements = array();
  
  // DB Table key
  public $echange_any_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->loggable = false;
    $spec->table = 'echange_any';
    $spec->key   = 'echange_any_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]               .= " back|echanges_generique";
    $props["sender_class"]            = "enum list|CSenderSFTP|CSenderFTP|CSenderSOAP|CSenderFileSystem|CSenderHTTP show|0";
    $props["sender_id"]            .= " back|expediteur_any";
    $props["message_content_id"]      = "ref class|CContentAny show|0 cascade back|messages_generique";
    $props["acquittement_content_id"] = "ref class|CContentAny show|0 cascade back|acquittements_generique";
    $props["receiver_id"]             = "ref class|CInteropReceiver autocomplete|nom back|echanges_any";
    $props["object_class"]            = "str class show|0";
    $props["object_id"]              .= " back|exchanges_any";

    $props["_message"]                = "str";
    $props["_acquittement"]           = "str";
    
    return $props;
  }

  /**
   * @inheritdoc
   */
  function loadContent() {
    $this->_ref_message_content = $this->loadFwdRef("message_content_id", true);
    $this->_message = $this->_ref_message_content->content;

    $this->_ref_acquittement_content = $this->loadFwdRef("acquittement_content_id", true);
    $this->_acquittement = $this->_ref_acquittement_content->content;
  }

  /**
   * @see parent::guessDataType()
   */
  function guessDataType(){
    $data_types = array(
       "<?xml" => "xml", 
       "MSH|"  => "er7",
    );
    
    foreach ($data_types as $check => $spec) {
      if (strpos($this->_message, $check) === 0) {
        $this->_props["_message"] = $spec;
        $this->_specs["_message"] = CMbFieldSpecFact::getSpec($this, "_message", $spec);
      }
      
      if (strpos($this->_acquittement, $check) === 0) {
        $this->_props["_acquittement"] = $spec;
        $this->_specs["_acquittement"] = CMbFieldSpecFact::getSpec($this, "_acquittement", $spec);
      }
    }
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    if ($this->_message !== null) {
      /** @var CContentAny $content */
      $content = $this->loadFwdRef("message_content_id", true);
      $content->content = $this->_message;
      if ($msg = $content->store()) {
        return $msg;
      }
      if (!$this->message_content_id) {
        $this->message_content_id = $content->_id;
      }
    }
    
    if ($this->_acquittement !== null) {
      /** @var CContentAny $content */
      $content = $this->loadFwdRef("acquittement_content_id", true);
      $content->content = $this->_acquittement;
      if ($msg = $content->store()) {
        return $msg;
      }
      if (!$this->acquittement_content_id) {
        $this->acquittement_content_id = $content->_id;
      }
    }
  }
}

