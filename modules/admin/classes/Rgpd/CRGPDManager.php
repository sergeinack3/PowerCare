<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;

use DOMNode;
use DOMNodeList;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\System\CSourceSMTP;

/**
 * Description
 */
class CRGPDManager implements IShortNameAutoloadable {
  const EMAIL_SUBJECT = 'RGPD';
  const PROOF_FILENAME = 'RGPD.txt';
  const SMTP_SOURCE_NAME = 'rgpd_consent';
  const SPECIAL_MODEL_NAME = '[RGPD]';

  const DEFAULT_CONF_FILE = 'modules/admin/resources/default_rgpd_conf.xml';

  const ENABLE_MANUAL = 1;
  const ENABLE_NOTIFICATION = 2;
  const ENABLE_NOTIFICATION_ACTION = 3;

  const CONFIGURATION_MODES = [
    self::ENABLE_MANUAL,
    self::ENABLE_NOTIFICATION,
    // Disabled (testing purposes)
    //self::ENABLE_NOTIFICATION_ACTION,
  ];

  const COMPLIANT_CLASSES = [
    'CPatient', 'CMedecin', 'CCorrespondantPatient', 'CUser',
  ];

  /** @var int CGroups ID */
  private $group_id;

  /**
   * CRGPDManager constructor.
   *
   * @param int $group_id
   *
   * @throws CRGPDException
   */
  public function __construct($group_id) {
    if (!$group_id) {
      throw new CRGPDException('common-error-Object %s not found', "CGroups-{$group_id}");
    }

    $this->group_id = $group_id;
  }

  /**
   * Get implemented compliant classes
   *
   * @return array
   */
  public static function getCompliantClasses() {
    return self::COMPLIANT_CLASSES;
  }

  /**
   * Get notification configuration level according to given object class
   *
   * @param string $object_class
   *
   * @return bool|mixed
   */
  private function getNotificationConfiguration($object_class) {
    if (!$object_class || !in_array($object_class, self::getCompliantClasses())) {
      return false;
    }

    return CAppUI::gconf("admin CRGPDConsent {$object_class} enable", $this->group_id);
  }

  /**
   * Tell if ask for consent must be handled manually
   *
   * @param string $object_class
   *
   * @return bool
   */
  public function inManualMode($object_class) {
    return ($this->getNotificationConfiguration($object_class) >= self::ENABLE_MANUAL);
  }

  /**
   * Tell if ask for consent must be send
   *
   * @param string $object_class
   *
   * @return bool
   */
  public function canNotify($object_class) {
    return ($this->getNotificationConfiguration($object_class) >= self::ENABLE_NOTIFICATION);
  }

  /**
   * Tell if ask for consent must be send with response tokens
   *
   * @param string $object_class
   *
   * @return bool
   */
  public function canNotifyWithActions($object_class) {
    return ($this->getNotificationConfiguration($object_class) == self::ENABLE_NOTIFICATION_ACTION);
  }

  /**
   * Tell if ask for consent is enabled (handler + configuration level)
   *
   * @param IRGPDCompliant $object
   *
   * @return bool
   */
  public function isEnabledFor(IRGPDCompliant $object) {
    // Todo: Handle _class properly (explicitly)
    return ($this->isHandlerEnabled() && ($this->getNotificationConfiguration($object->_class) > 0));
  }

  /**
   * Tell if GDPR handler is enabled
   *
   * @return bool
   */
  public function isHandlerEnabled() {
    return HandlerManager::isObjectHandlerActive('CRGPDHandler', $this->group_id);
  }

  /**
   * Get the RGPD representative user
   *
   * @return int
   */
  public function getRGPDUserID() {
    return CAppUI::conf('admin CRGPDConsent user_id');
  }

  /**
   * Gets the SMTP source for consent approval
   *
   * @return CSourceSMTP|null
   */
  public function getRGPDSource() {
    $source       = new CSourceSMTP();
    $source->name = $this->getRGPDSourceName();

    $source->loadMatchingObject();

    if ($source && $source->_id) {
      return $source;
    }

    return null;
  }

  /**
   * @return string
   */
  public function getRGPDSourceName() {
    return self::SMTP_SOURCE_NAME;
  }

  /**
   * @return string
   */
  public function getEmailSubject() {
    return self::EMAIL_SUBJECT;
  }

  /**
   * @return string
   */
  public function getProofFileName() {
    return self::PROOF_FILENAME;
  }

  /**
   * @return string
   */
  public function getSpecialModelName() {
    return self::SPECIAL_MODEL_NAME;
  }

  /**
   * Return default RGPD text configurations
   *
   * @return array
   */
  static public function getRGPDDefaultConf() {
    $text = [];

    if (!is_readable(self::DEFAULT_CONF_FILE)) {
      return $text;
    }

    $dom = new CMbXMLDocument();

    if (!$dom->load(self::DEFAULT_CONF_FILE)) {
      return $text;
    }

    $xpath = new CMbXPath($dom);

    $configurations = $xpath->query('configuration');

    if (!$configurations instanceof DOMNodeList) {
      return $text;
    }

    /** @var DOMNode $_configuration */
    foreach ($configurations as $_configuration) {
      $_context = $_configuration->getAttribute('context');

      if (!isset($text[$_context])) {
        $text[$_context] = [];
      }

      $text[$_context][$_configuration->getAttribute('key')] = trim(utf8_decode($_configuration->nodeValue));
    }

    return $text;
  }

  /**
   * Returns the RGPD configuration model
   *
   * @return array
   */
  static function getRGPDConfigurationModel() {
    static $configurations = [];

    if ($configurations) {
      return $configurations;
    }

    static $classes = [];

    if (!$classes) {
      $classes = self::getCompliantClasses();
    }

    $configurations = array_fill_keys(
      $classes,
      [
        'enable'          => 'enum list|0|' . implode('|', self::getEnableConfigurations()) . ' default|0 localize',
        'rgpd_intro'      => 'text',
        'rgpd_data'       => 'text',
        'rgpd_droits'     => 'text',
        'rgpd_contact'    => 'text',
        'rgpd_conclusion' => 'text',
      ]
    );

    // Particular case, we disable notifications for users
    if (isset($configurations['CUser'])) {
      $configurations['CUser']['enable'] = 'enum list|0|1 default|0 localize';
    }

    return $configurations;
  }

  /**
   * Returns the RGPD text by part according to given IRGPDCompliant class
   *
   * @param string $object_class Object class
   *
   * @return array
   */
  public function getRGPDText($object_class) {
    if (!$object_class || !in_array($object_class, self::getCompliantClasses())) {
      return [];
    }

    $keys = ['intro', 'data', 'droits', 'contact', 'conclusion'];

    $text = [];
    foreach ($keys as $_key) {
      $text[$_key] = CAppUI::gconf("admin CRGPDConsent {$object_class} rgpd_{$_key}", $this->group_id);
    }

    return $text;
  }

  /**
   * Get configuration modes
   *
   * @return array
   */
  static private function getEnableConfigurations() {
    return self::CONFIGURATION_MODES;
  }

  /**
   * Get RGPDConsent object for given entity
   *
   * @param IRGPDCompliant $object
   * @param int            $tag
   *
   * @return CRGPDConsent|null
   */
  public function getConsentForObject(IRGPDCompliant $object, $tag = CRGPDConsent::TAG_DATA) {
    // Todo: Handle _id properly (explicitly)
    if (!$object || !$object->_id) {
      return null;
    }

    $consent           = new CRGPDConsent();
    $consent->tag      = $tag;
    $consent->group_id = $this->group_id;
    $consent->setObject($object);

    if ($consent->loadMatchingObject()) {
      return $consent;
    }

    return null;
  }

  /**
   * Initialises CRGPDConsent object
   *
   * @param IRGPDCompliant $object
   * @param int            $tag
   *
   * @return CRGPDConsent
   * @throws CRGPDException
   */
  public function initConsent(IRGPDCompliant $object, $tag = CRGPDConsent::TAG_DATA) {
    $consent           = new CRGPDConsent();
    $consent->tag      = $tag;
    $consent->group_id = $this->group_id;
    $consent->setObject($object);
    $consent->setManager($this);

    return $consent->markAsGenerated();
  }

  /**
   * Retrieves or initializes a consent for given object
   * Todo: Handle other TAGS
   *
   * @param IRGPDCompliant $object
   * @param int            $tag
   *
   * @return CRGPDConsent|null
   * @throws CRGPDException
   */
  public function getOrInitConsent(IRGPDCompliant $object, $tag = CRGPDConsent::TAG_DATA) {
    $consent = $this->getConsentForObject($object, $tag);

    if (!$consent || !$consent->_id) {
      return $this->initConsent($object, $tag);
    }

    return $consent;
  }

  /**
   * Checks if consent has been validated
   *
   * @param IRGPDCompliant $object
   * @param int            $tag
   *
   * @return bool
   * @throws CRGPDException
   */
  public function checkConsentFor(IRGPDCompliant $object, $tag = CRGPDConsent::TAG_DATA) {
    if (!$object->shouldAskConsent()) {
      return true;
    }

    $consent = $this->getOrInitConsent($object, $tag);

    return ($consent && $consent->_id) ? $consent->isOK() : false;
  }

  /**
   * @param IRGPDCompliant $object
   *
   * @return bool
   */
  public function shouldAskConsentFor(IRGPDCompliant $object) {
    return $object->shouldAskConsent();
  }

  /**
   * @param IRGPDCompliant $object
   *
   * @return bool
   */
  public function canAskConsentFor(IRGPDCompliant $object) {
    return $object->canAskConsent();
  }

  /**
   * @param IRGPDCompliant $object
   *
   * @return void
   * @throws CRGPDException
   */
  public function askConsentFor(IRGPDCompliant $object) {
    if (!$object->shouldAskConsent()) {
      throw new CRGPDException('CRGPD-error-Should not ask consent');
    }

    // Todo: Handle _class properly (explicitly)
    if (!$this->canNotify($object->_class)) {
      return;
    }

    $consent = $this->getConsentForObject($object);

    if (!$consent || !$consent->_id) {
      $consent = $this->initConsent($object);
    }

    $object->setRGPDConsent($consent);

    // No synchronous sending
//    if (!$this->canAskConsent()) {
//      throw new CRGPDException('CRGPD-error-Cannot ask consent');
//    }

//    if (!$consent || !$consent->_id) {
//      throw new CRGPDException('CRGPD-error-No consent object');
//    }
//
//    $consent->sendRequest();
  }

  /**
   * Sends asking for consent
   *
   * @param CRGPDConsent $consent
   * @param CSourceSMTP  $source
   *
   * @return void
   * @throws CRGPDException
   */
  public function send(CRGPDConsent $consent, CSourceSMTP $source) {
    if (!$consent->_id) {
      throw new CRGPDException('common-error-Object %s not found', CRGPDConsent::class);
    }

    if (!$source->_id) {
      throw new CRGPDException('common-error-Object %s not found', CSourceSMTP::class);
    }

    /** @var IRGPDCompliant $context */
    $context = $consent->loadTargetObject();

    if (!$consent || !$consent->_id) {
      throw new CRGPDException('common-error-Object %s not found', "{$consent->object_class}-{$consent->object_id}");
    }

    $subject = $consent->getEmailSubject();
    $body    = $consent->getEmailBody();
    $email   = $context->getEmail();

    if (!CApp::sendEmail($subject, $body, null, null, null, $email, $source)) {
      throw new CRGPDException('common-error-Unable to send mail');
    }

    $consent->markAsSent();
  }
}
