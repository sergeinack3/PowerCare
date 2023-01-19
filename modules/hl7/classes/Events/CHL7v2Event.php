<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXPath;
use Ox\Mediboard\System\CContentTabular;

/**
 * Class CHL7v2Event 
 * Event HL7
 */
class CHL7v2Event extends CHL7Event {
  /**
   * Get segment terminator
   *
   * @param string $st The key of the value to get
   *
   * @return mixed
   */
  private function getSegmentTerminator($st) {
    $terminators = array(
      "CR"   => "\r",
      "LF"   => "\n",
      "CRLF" => "\r\n",
    );
    
    return CValue::read($terminators, $st, CHL7v2Message::DEFAULT_SEGMENT_TERMINATOR);
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    // Traitement sur le mbObject
    $this->object   = $object;
    $this->last_log = $object->loadLastLog();

    // Récupération de la version HL7 en fonction du receiver et de la transaction
    $this->version  = CAppUI::conf("hl7 default_version");
    if ($version = CMbArray::get($this->_receiver->_configs, $this->transaction."_HL7_version")) {
      $this->version  = $version;
    }
    
    // Génération de l'échange
    $this->generateExchange();
 
    $terminator = $this->getSegmentTerminator($this->_receiver->_configs["ER7_segment_terminator"]);
    
    // Création du message HL7
    $message = new CHL7v2Message($this->version);
    $message->segmentTerminator = $terminator;
    $message->name              = $this->msg_codes;
    $message->actor             = $this->_receiver;
   
    $this->message = $message;
  }

  /**
   * @inheritdoc
   */
  function handle($msg_hl7) {
    $this->message = new CHL7v2Message();

    if ($this->_sender && $this->_sender->_id) {
      $this->message->actor = $this->_sender;
    }

    $ignored_fields = array();
    if ($this->_data_format) {
      $configs_format = $this->_data_format->_configs_format;

      if ($configs_format->ignore_fields) {
        $ignored_fields = preg_split("/\s*,\s*/", $configs_format->ignore_fields);
      }

      $strict = $configs_format->strict_segment_terminator;
      $this->message->strict_segment_terminator = $strict;
      
      if ($strict) {
        $terminator = $this->getSegmentTerminator($configs_format->segment_terminator);
        $this->message->segmentTerminator = $terminator;
      }
    }

    $this->message->ignored_fields = $ignored_fields;

    // Apply rule
    $msg_hl7 = $this->applySequences($msg_hl7, $this->message->actor);

    $this->message->parse($msg_hl7, true, $this->message->actor);

    $dom = $this->message->toXML(CClassMap::getSN($this), false, CApp::$encoding);

    $xpath = new CHL7v2MessageXPath($dom);

    foreach ($ignored_fields as $_ignore_field) {
      $query = "//$_ignore_field";

      $nodes = $xpath->query($query);
      foreach ($nodes as $_node) {
        $_node->parentNode->removeChild($_node);
      }
    }

    return $dom;
  }

  /**
   * Get the message as a string
   *
   * @return string
   */
  function flatten() {
    $this->msg_hl7 = $this->message->flatten();
    $this->message->validate();
    
    $this->updateExchange();
    
    return $this->msg_hl7;
  }

  /**
   * Generate exchange HL7v2
   *
   * @return CExchangeHL7v2
   */
  function generateExchange() {
    $exchange_hl7v2                  = $this->_exchange_hl7v2 ? $this->_exchange_hl7v2 : new CExchangeHL7v2();
    $exchange_hl7v2->date_production = CMbDT::dateTime();
    $exchange_hl7v2->receiver_id     = $this->_receiver->_id;
    $exchange_hl7v2->group_id        = $this->_receiver->group_id;
    $exchange_hl7v2->sender_id       = $this->_sender ? $this->_sender->_id : null;
    $exchange_hl7v2->sender_class    = $this->_sender ? $this->_sender->_id : null;
    $exchange_hl7v2->version         = $this->version;
    $exchange_hl7v2->type            = $this->profil;
    $exchange_hl7v2->sous_type       = $this->transaction;
    $exchange_hl7v2->code            = $this->code;
    $exchange_hl7v2->object_id       = $this->object->_id;
    $exchange_hl7v2->object_class    = $this->object->_class;
    $exchange_hl7v2->store();

    return $this->_exchange_hl7v2 = $exchange_hl7v2;
  }

  /**
   * Update exchange HL7v2 with
   *
   * @return CExchangeHL7v2
   */
  function updateExchange() {
    $exchange_hl7v2 = $this->_exchange_hl7v2;
    $receiver       = $this->_receiver;

    $exchange_hl7v2->_message = $this->msg_hl7;

    // Si le message HL7 contient une wildcard pour l'IPP et le NDA on flag alors l'échange
    $pattern = "===NDA_MISSING===";
    if (!CValue::read($receiver->_configs, "send_not_master_NDA") && strpos($this->msg_hl7, $pattern) !== false) {
      $exchange_hl7v2->master_idex_missing = true;
    }
    $pattern = "===IPP_MISSING===";
    if (!CValue::read($receiver->_configs, "send_not_master_IPP") && strpos($this->msg_hl7, $pattern) !== false) {
      $exchange_hl7v2->master_idex_missing = true;
    }

    $exchange_hl7v2->message_valide = $this->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
    $exchange_hl7v2->store();
    
    return $exchange_hl7v2;
  }

    /**
     * Apply rules
     *
     * @param string        $msg
     * @param CInteropActor $actor
     *
     * @return string
     * @throws \Exception
     */
    public function applySequences(string $msg, CInteropActor $actor): string
    {
        $link_actor_sequences = $actor->loadRefsEAITransformation();
        if (!$link_actor_sequences) {
            return $msg;
        }

        $content = $msg;
        foreach ($link_actor_sequences as $_link_actor_sequence) {
            $sequence = $_link_actor_sequence->loadRefSequence();
            // est-ce que la séquence est applicable au message ?
            if (!$sequence->checkAvailability($this)) {
                continue;
            }

            $content = $sequence->applyRules($content, $actor);
        }

        if (!$actor->_content_altered) {
            return $msg;
        }

        $content_altered = new CContentTabular();
        $content_altered->content = $content;
        $content_altered->store();

        $this->altered_content_message_id = $content_altered->_id;

        return $content;
    }

}
