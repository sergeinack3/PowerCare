<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Admin\CUser;
use phpmailerException;

/**
 * Description
 */
class CSMTPBuffer extends CMbObject {
  const MAX_ATTEMPTS = 3;

  /** @var integer Primary key */
  public $smtp_buffer_id;

  /** @var string Creation date */
  public $creation_date;

  /** @var integer CUser ID */
  public $user_id;

  /** @var integer CSourceSMTP ID */
  public $source_id;

  /** @var integer Number of attempts */
  public $attempts;

  /** @var string Serialized array input */
  public $input;

  /** @var array Unserialized input */
  public $_input;

  /** @var CUser */
  public $_ref_user;

  /** @var CSourceSMTP */
  public $_ref_source;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = 'smtp_buffer';
    $spec->key      = 'smtp_buffer_id';
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props['creation_date'] = 'dateTime notNull index|0';
    $props['user_id']       = 'ref class|CUser notNull back|smtp_buffers';
    $props['source_id']     = 'ref class|CSourceSMTP notNull back|buffers';
    $props['attempts']      = 'num notNull default|0';
    $props['input']         = 'text notNull';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->creation_date = ($this->creation_date) ?: CMbDT::dateTime();
      $this->user_id       = ($this->user_id) ?: CUser::get()->_id;
    }

    $this->input = serialize($this->_input);

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_input = unserialize($this->input);
  }

  /**
   * Loads CUser's reference
   *
   * @return CUser|null
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef('user_id', true);
  }

  /**
   * Loads CSourceSMTP's reference
   *
   * @return CSourceSMTP|null
   */
  function loadRefSource() {
    return $this->_ref_source = $this->loadFwdRef('source_id', true);
  }

  /**
   * Init input data
   *
   * @return void
   */
  function init() {
    $this->_input = array(
      'from'            => null,
      'to'              => array(),
      'cc'              => array(),
      'bcc'             => array(),
      're'              => array(),
      'subject'         => null,
      'body'            => null,
      'attachments'     => array(),
      'embedded_images' => array(),
    );
  }

  /**
   * Sends mail
   *
   * @return bool
   * @throws CMbException
   * @throws phpmailerException
   */
  function send() {
    if (!$this->checkAttempts()) {
      return false;
    }

    try {
      $source = $this->loadRefSource();
      $source->init();
      $source->_skip_buffer = true;

      $this->setInputParameters();
      $source->send();
    }
    catch (phpmailerException $e) {
      $this->incrementAttempts();
      throw $e;
    }
    catch (CMbException $e) {
      $this->incrementAttempts();
      throw $e;
    }

    $this->delete();

    return true;
  }

  /**
   * Init content settings
   *
   * @throws CMbException
   *
   * @return void
   */
  function setInputParameters() {
    $source = $this->_ref_source;

    if (!isset($this->_input['from']) || !$this->_input['from']) {
      throw new CMbException('No FROM parameter');
    }

    $source->setFrom($this->_input['from']);

    if (isset($this->_input['to']) && is_array($this->_input['to'])) {
      foreach ($this->_input['to'] as $_to) {
        $source->addTo($_to);
      }
    }

    if (isset($this->_input['cc']) && is_array($this->_input['cc'])) {
      foreach ($this->_input['cc'] as $_cc) {
        $source->addCc($_cc);
      }
    }

    if (isset($this->_input['bcc']) && is_array($this->_input['bcc'])) {
      foreach ($this->_input['bcc'] as $_bcc) {
        $source->addBcc($_bcc);
      }
    }

    if ((!isset($this->_input['to']) || !$this->_input['to'])
        && (!isset($this->_input['cc']) || !$this->_input['cc'])
        && (!isset($this->_input['bcc']) || !$this->_input['bcc'])
    ) {
      throw new CMbException('No receiver');
    }

    if (isset($this->_input['re']) && is_array($this->_input['re'])) {
      foreach ($this->_input['re'] as $_re) {
        $source->addRe($_re);
      }
    }

    if (isset($this->_input['subject']) && $this->_input['subject']) {
      $source->setSubject($this->_input['subject']);
    }

    if (isset($this->_input['body']) && !$this->_input['body']) {
      throw new CMbException('No BODY parameter');
    }

    //$source->setBody(CMbString::purifyHTML($this->_input['body']));
    $source->setBody($this->_input['body']);

    if (isset($this->_input['attachments']) && is_array($this->_input['attachments'])) {
      foreach ($this->_input['attachments'] as $_attachment) {
        $source->addAttachment($_attachment['path'], $_attachment['name']);
      }
    }

    if (isset($this->_input['embedded_images']) && is_array($this->_input['embedded_images'])) {
      foreach ($this->_input['embedded_images'] as $_embedded_image) {
        $source->addEmbeddedImage($_embedded_image['path'], $_embedded_image['cid']);
      }
    }
  }

  /**
   * Checks if max number of attempts is reached
   *
   * @return bool
   */
  function checkAttempts() {
    return ($this->attempts < static::MAX_ATTEMPTS);
  }

  /**
   * Increments number of attempts
   *
   * @return null|string
   */
  function incrementAttempts() {
    $this->attempts++;

    return $this->store();
  }
}
