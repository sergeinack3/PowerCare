<?php
/**
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Interop\Eai\CExchangeTransportLayer;
use phpmailerException;

/**
 * Description
 */
class CExchangeSMTP extends CExchangeTransportLayer {
  /** @var integer Primary key */
  public $exchange_smtp_id;

  public $creation_date;
  public $subject;
  public $cc;
  public $re;
  public $attempts;
  public $content_hash;

  public $_cc = array();

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = 'exchange_smtp';
    $spec->key      = 'exchange_smtp_id';
    $spec->loggable = false;
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["source_class"]  = "enum list|CSourceSMTP";
    $props["source_id"]  .= " cascade back|echange_smtp";
    $props['date_echange']  = 'dateTime';
    $props['destinataire']  = 'str notNull';
    $props['input']         = 'html show|0';
    $props['creation_date'] = 'dateTime notNull';
    $props['subject']       = 'str notNull';
    $props['cc']            = 'str';
    $props['re']            = 'str';
    $props['attempts']      = 'num default|0';
    $props['content_hash']  = 'str maxLength|64';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    if ($this->cc) {
      $this->_cc = explode('|', $this->cc);
    }
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->function_name = 'send';
      $this->creation_date = ($this->creation_date) ?: CMbDT::dateTime();
    }

    if (!$this->_id || $this->fieldModified('input')) {
      $this->input        = CMbString::purifyHTML($this->input);
      $this->content_hash = hash('SHA256', $this->subject . $this->input);
    }

    return parent::store();
  }

  /**
   * Increment number of attemps
   *
   * @return null|string
   */
  function incrementAttemps() {
    $this->attempts++;

    return $this->store();
  }

  /**
   * Set email FROM field
   *
   * @param string $mail Email address
   *
   * @return mixed
   */
  function setFrom($mail) {
    return $this->emetteur = $mail;
  }

  /**
   * Set email TO field
   *
   * @param string $mail Email address
   *
   * @return mixed
   */
  function setTo($mail) {
    return $this->destinataire = $mail;
  }

  /**
   * Set email CC field
   *
   * @param string|array $mails Email addresses
   *
   * @return string|array
   */
  function setCC($mails) {
    return $this->cc = (is_array($mails)) ? implode('|', $mails) : $mails;
  }

  /**
   * Set email subject
   *
   * @param string $subject Subject
   *
   * @return mixed
   */
  function setSubject($subject) {
    return $this->subject = $subject;
  }

  /**
   * Set email body
   *
   * @param string $body Body
   *
   * @return mixed
   */
  function setBody($body) {
    return $this->input = $body;
  }

  /**
   * Set exchange source object
   *
   * @param CSourceSMTP $source Exchange source
   *
   * @return CSourceSMTP
   */
  function setSource(CSourceSMTP $source) {
    $this->source_class = $source->_class;
    $this->source_id    = $source->_id;

    $this->setFrom($source->email);

    return $this->_ref_source = $source;
  }

  /**
   * Set exchange source as default system-message source
   *
   * @return CSourceSMTP
   */
  function setSystemSource() {
    return $this->setSource(CSourceSMTP::getSystemSource());
  }

  /**
   * @inheritdoc
   */
  function unserialize() {
    return;
  }

  /**
   * Init and send data content
   *
   * @return bool
   */

  /**
   * Mail sending
   *
   * @return bool
   * @throws CMbException
   * @throws phpmailerException
   */
  function send() {
    if (!$this->_id) {
      return false;
    }

    /** @var CSourceSMTP $exchange_source */
    $exchange_source = $this->loadRefSource();

    if (!$exchange_source || !$exchange_source->_id) {
      return false;
    }

    if ($msg = $this->incrementAttemps()) {
      return false;
    }

    $this->date_echange = CMbDT::dateTime();
    $start              = microtime(true);

    try {
      $exchange_source->init();
      $exchange_source->addTo($this->destinataire);

      foreach ($this->_cc as $_cc) {
        $exchange_source->addCc($_cc);
      }

      if ($this->re) {
        $exchange_source->addRe($this->re);
      }

      $exchange_source->setSubject($this->subject);
      $exchange_source->setBody(CMbString::purifyHTML($this->input));

      $exchange_source->send();
    }
    catch (phpmailerException $e) {
      throw $e;
    }
    catch (CMbException $e) {
      throw $e;
    }

    $this->response_datetime = CMbDT::dateTime();
    $this->response_time     = round((microtime(true) - $start), 2);

    $this->store();

    return true;
  }
}
