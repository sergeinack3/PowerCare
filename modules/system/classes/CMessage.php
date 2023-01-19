<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Classe CMessage.
 *
 * @abstract Gère les messages de l'administrateur
 */
class CMessage extends CMbObject {
  // DB Table key
  public $message_id;
  
  // DB fields
  public $module_id;
  public $group_id;
  
  public $deb;
  public $fin;
  public $titre;
  public $corps;
  public $urgence;
  
  // Form fields
  public $_status;
  
  // Behaviour fields
  public $_email_send;
  public $_email_from;
  public $_email_to;
  public $_email_details;
  
  public $_update_moment;
  public $_update_initiator;
  public $_update_benefits;
  
  
  // Object references
  public $_ref_module_object;
  public $_ref_group;

  public $_ref_acquittals;
  
  static $status = array (
    "all"     => "Tous les messages",
    "past"    => "Déjà publiés",
    "present" => "En cours de publication",
    "future"  => "Publications à venir",
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'message';
    $spec->key   = 'message_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["deb"]       = "dateTime notNull";
    $props["fin"]       = "dateTime notNull";
    $props["titre"]     = "str notNull maxLength|40";
    $props["corps"]     = "text notNull markdown";
    $props["urgence"]   = "enum notNull list|normal|urgent default|normal";
    $props["module_id"] = "ref class|CModule back|messages";
    $props["group_id"]  = "ref class|CGroups back|messages";

    $props["_status"]         = "enum list|past|present|future";
    $props["_email_from"]     = "str";
    $props["_email_to"]       = "str";
    $props["_email_details"]  = "text";
    $props["_update_moment"]    = "dateTime";
    $props["_update_initiator"] = "str";
    $props["_update_benefits"]  = "text";
    return $props;
  }

  /**
   * Loads messages from a publication date perspective
   *
   * @param string $status   Wanted status, null for all
   * @param string $mod_name Module name restriction, null for all
   * @param int    $group_id Group ID
   *
   * @return self[] Published messages
   */
  function loadPublications($status = null, $user_id = null, $mod_name = null, $group_id = null) {
    $now = CMbDT::dateTime();
    $where = array();

    switch ($status) {
      case "past":
        $where["fin"] = "< '$now'";
        break;
      case "present":
        $where["deb"] = "< '$now'";
        $where["fin"] = "> '$now'";
        break;
      case "future":
        $where["deb"] = "> '$now'";
        break;
    }

    if ($group_id) {
      $where[] = "group_id = '$group_id' OR group_id IS NULL";
    }

    /** @var self[] $messages */
    $messages = $this->loadList($where, "deb DESC");

    // Module name restriction
    if ($mod_name) {
      foreach ($messages as $message_id => $_message) {
        if ($_message->module_id) {
          if ($_message->loadRefModuleObject()->mod_name != $mod_name) {
            unset($messages[$message_id]);
          }
        }
      }
    }

    if ($user_id) {
      CMbObject::massLoadBackRefs($messages, "acquittals");
      foreach ($messages as $key => $_message) {
        $_message->loadRefAcquittals();

        if (in_array($user_id, CMbArray::pluck($_message->_ref_acquittals, "user_id"))) {
          unset($messages[$key]);
        }
      }
    }

    return $messages;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $msg = parent::store();
    $this->sendEmail();
    return $msg;
  }

  /**
   * Sends the email
   *
   * @return void
   */
  function sendEmail() {
    if (!$this->_email_send) {
      return;
    }
    
    try {
      // Source init
      /** @var CSourceSMTP $source */
      $source = CExchangeSource::get("system-message", CSourceSMTP::TYPE);
      $source->init();
      $source->addTo($this->_email_to);
      $source->addBcc($this->_email_from);
      $source->addRe($this->_email_from);
      
      // Email subject
      $page_title = CAppUI::conf("page_title");
      $source->setSubject("$page_title - $this->titre");
      
      // Email body
      $info = CAppUI::tr("CMessage-info-published");
      $body = "<strong>$page_title</strong> $info<br />";
      $body.= $this->getFieldContent("titre");
      $body.= $this->getFieldContent("deb");
      $body.= $this->getFieldContent("fin");
      $body.= $this->getFieldContent("module_id");
      $body.= $this->getFieldContent("group_id");
      $body.= $this->getFieldContent("corps");
      $body.= $this->getFieldContent("_email_details");
      $source->setBody($body);
      // Do send
      $source->send();
    }
    catch (CMbException $e) {
      $e->stepAjax();
      return;
    }
    
    CAppUI::setMsg("CMessage-email_sent");
  }

  /**
   * Builds user readable data
   *
   * @param string $field Field name
   *
   * @return null|string
   */
  function getFieldContent($field) {
    if (!$this->$field) {
      return null;
    }
    
    // Build content
    $label = $this->getLabelElement($field);
    $value = $this->getHtmlValue($field);
    $content = "<br/ >$label : <strong>$value</strong>\n"; 
        
    return $content;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->titre;
  }

  /**
   * Load module references by the message
   *
   * @return CModule
   */
  function loadRefModuleObject() {
    $module = $this->loadFwdRef("module_id", true);
    $this->_view = ($module->_id ? "[$module] - " : "") . $this->titre;
    return $this->_ref_module_object = $module;
  }

  /**
   * Load group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  function loadRefAcquittals() {
    return $this->_ref_acquittals = $this->loadBackRefs("acquittals");
  }
}
