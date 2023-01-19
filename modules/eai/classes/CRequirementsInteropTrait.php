<?php
/**
 * @package Mediboard\Core\Requirements
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;


use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\Module\Requirements\CRequirementsItem;
use Ox\Core\Module\Requirements\CRequirementsManager;
use Ox\Interop\Hl7\CHL7Config;
use Ox\Interop\Hprimxml\CHprimXMLConfig;
use Ox\Interop\Phast\CPhastConfig;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CRequirementsInteropManager
 *
 * @package Ox\Core\Module\Requirements
 */
trait CRequirementsInteropTrait {

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $field
   * @param mixed                 $expected
   *
   * @return void
   */
  protected function assertReceiverFieldEquals(?CInteropReceiver $receiver, string $field, $expected) {
    $this->assertInteropActorFieldCheck($receiver, $field, $expected);
  }

  /**
   * @param CInteropActor|null $actor
   * @param string             $field
   * @param mixed              $expected
   * @param string|null        $type
   *
   * @return void
   */
  private function assertInteropActorFieldCheck(?CInteropActor $actor, string $field, $expected, ?string $type = null): void {
    $actual = $actor && $actor->_id && property_exists($actor, $field) ? $actor->$field : null;
    $check  = $this->check($actual, $expected, $type);
    $item   = new CRequirementsItem(CRequirementsManager::TYPE_EXPECTED_NOTNULL, $actual, $check);
    if ($field === '_id') {
      $field = $actor->_spec->key ?? $field;
    }

    $item->setDescription($actor->_class . '-' . $field);
    $item->setSection(CClassMap::getSN($actor));
    $this->addItems($item);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $field
   *
   * @return void
   */
  protected function assertReceiverFieldTrue(?CInteropReceiver $receiver, string $field): void {
    $this->assertInteropActorFieldCheck($receiver, $field, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $field
   *
   * @return void
   */
  protected function assertReceiverFieldFalse(?CInteropReceiver $receiver, string $field): void {
    $this->assertInteropActorFieldCheck($receiver, $field, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $field
   *
   * @return void
   */
  protected function assertReceiverFieldNotNull(?CInteropReceiver $receiver, string $field): void {
    $this->assertInteropActorFieldCheck($receiver, $field, self::TYPE_EXPECTED_NOTNULL, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $field
   *
   * @return void
   */
  protected function assertSenderFieldNotNull(?CInteropSender $sender, string $field): void {
    $this->assertInteropActorFieldCheck($sender, $field, self::TYPE_EXPECTED_NOTNULL, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $field
   * @param mixed               $expected
   *
   * @return void
   */
  protected function assertSenderFieldEquals(?CInteropSender $sender, string $field, $expected): void {
    $this->assertInteropActorFieldCheck($sender, $field, $expected);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $field
   *
   * @return void
   */
  protected function assertSenderFieldTrue(?CInteropSender $sender, string $field): void {
    $this->assertInteropActorFieldCheck($sender, $field, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $field
   *
   * @return void
   */
  protected function assertSenderFieldFalse(?CInteropSender $sender, string $field): void {
    $this->assertInteropActorFieldCheck($sender, $field, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $class source class
   * @param string[]              $profiles
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverHasSourceActive(?CInteropReceiver $receiver, string $class, array $profiles): void {
    $this->assertReceiverSourceFieldTrue($receiver, $class, "_id", $profiles);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverSourceFieldTrue(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles): void {
    $this->assertReceiverSourceFieldCheck($receiver, $source_class, $field, $profiles, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   * @param mixed                 $expected
   * @param string|null           $type
   *
   * @return void
   * @throws Exception
   */
  private function assertReceiverSourceFieldCheck(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles, $expected, ?string $type = null): void {
    $sources = [];
    if ($receiver) {
      $sources = $this->getSourcesFromReceiver($receiver, $source_class, $profiles);
    }

    foreach ($profiles as $profile) {
      $source = CMbArray::get($sources, $profile);
      $actual = $source && $source->_id && property_exists($source, $field) ? $source->$field : null;
      $check  = $this->check($actual, $expected, $type);
      $item   = new CRequirementsItem($expected, $actual, $check);

      $item->setSection(CAppUI::tr(CClassMap::getSN($source_class)) . " [$profile]");
      if ($field === "_id") {
        $field = $source->_spec->key ?? $field;
      }
      $description = CClassMap::getSN($source_class) . "-" . $field;
      $item->setDescription($description);
      $this->addItems($item);
    }
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $class
   * @param array                 $profiles
   *
   * @return CExchangeSource[]
   * @throws Exception
   */
  private function getSourcesFromReceiver(?CInteropReceiver $receiver, string $class, array $profiles = []): array {
    $source  = null;
    $sources = $receiver->loadRefsExchangesSources();

    foreach ($sources as $profile => $source) {

      if (!in_array($profile, $profiles)) {
        unset($sources[$profile]);
      }
    }

    return $sources;
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   * @param mixed                 $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverSourceFieldEquals(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles, $expected): void {
    $this->assertReceiverSourceFieldCheck($receiver, $source_class, $field, $profiles, $expected);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   * @param string                $regex
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverSourceFieldRegex(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles, string $regex): void {
    $this->assertReceiverSourceFieldCheck($receiver, $source_class, $field, $profiles, $regex, self::TYPE_EXPECTED_REGEX);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverSourceFieldNotNull(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles): void {
    $this->assertReceiverSourceFieldCheck($receiver, $source_class, $field, $profiles, self::TYPE_EXPECTED_NOTNULL, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $source_class
   * @param string                $field
   * @param array                 $profiles
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverSourceFieldFalse(?CInteropReceiver $receiver, string $source_class, string $field, array $profiles): void {
    $this->assertReceiverSourceFieldCheck($receiver, $source_class, $field, $profiles, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $conf_path
   * @param mixed                 $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverConfEquals(?CInteropReceiver $receiver, string $conf_path, $expected): void {
    $this->assertInteropActorConfCheck($receiver, $conf_path, $expected);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverConfNotNull(?CInteropReceiver $receiver, string $conf_path): void {
    $this->assertInteropActorConfCheck($receiver, $conf_path, self::TYPE_EXPECTED_NOTNULL, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropActor|null $actor
   * @param string             $conf_path
   * @param mixed              $expected
   * @param string|null        $type
   *
   * @return void
   * @throws Exception
   */
  private function assertInteropActorConfCheck(?CInteropActor $actor, string $conf_path, $expected, ?string $type = null): void {
    $check = false;
    if ($actor) {
      $actor->loadRefObjectConfigs();

      $object = $actor->_ref_object_configs;
      $actual = $object && $object->_id ? $object->$conf_path : null;
      $check  = $this->check($actual, $expected, $type);
    }

    $item = new CRequirementsItem($expected, $actual ?? null, $check);
    $tr = $actor && $object ? $object->_class .'-'. $conf_path : "interop actor [$conf_path]";
    $item->setDescription($tr, $actor && $object);
    if ($actor && $actor->_ref_object_configs) {
      $item->setSection($object->_class);
    }
    $this->addItems($item);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverConfTrue(?CInteropReceiver $receiver, string $conf_path) {
    $this->assertInteropActorConfCheck($receiver, $conf_path, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverConfFalse(?CInteropReceiver $receiver, string $conf_path) {
    $this->assertInteropActorConfCheck($receiver, $conf_path, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropReceiver|null $receiver
   * @param string                $conf_path
   * @param string                $regex
   *
   * @return void
   * @throws Exception
   */
  protected function assertReceiverConfRegex(?CInteropReceiver $receiver, string $conf_path, string $regex) {
    $this->assertInteropActorConfCheck($receiver, $conf_path, $regex, CRequirementsManager::TYPE_EXPECTED_REGEX);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $conf_path
   * @param mixed               $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderConfEquals(?CInteropSender $sender, string $conf_path, $expected) {
    $this->assertInteropActorConfCheck($sender, $conf_path, $expected);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderConfTrue(?CInteropSender $sender, string $conf_path) {
    $this->assertInteropActorConfCheck($sender, $conf_path, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderConfFalse(?CInteropSender $sender, string $conf_path) {
    $this->assertInteropActorConfCheck($sender, $conf_path, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $conf_path
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderConfNotNull(?CInteropSender $sender, string $conf_path) {
    $this->assertInteropActorConfCheck($sender, $conf_path, self::TYPE_EXPECTED_NOTNULL, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $class
   * @param string              $field
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldFalse(?CInteropSender $sender, string $class, string $field) {
    $this->assertSenderStandardFieldCheck($sender, $class, $field, false, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $class
   * @param string              $field
   * @param mixed               $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldEquals(?CInteropSender $sender, string $class, string $field, $expected) {
    $this->assertSenderStandardFieldCheck($sender, $class, $field, $expected);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $class
   * @param string              $field
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldTrue(?CInteropSender $sender, string $class, string $field) {
    $this->assertSenderStandardFieldCheck($sender, $class, $field, true, CRequirementsManager::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $class
   * @param string              $field
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldNotNull(?CInteropSender $sender, string $class, string $field) {
    $this->assertSenderStandardFieldCheck($sender, $class, $field, self::TYPE_EXPECTED_NOTNULL, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $conf_path
   * @param string              $field
   * @param string              $regex
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldRegex(?CInteropSender $sender, string $conf_path, string $field, string $regex) {
    $this->assertSenderStandardFieldCheck($sender, $conf_path, $field, $regex, CRequirementsManager::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $class
   * @param string              $field
   * @param mixed               $expected
   * @param string|null         $type
   *
   * @return void
   * @throws Exception
   */
  protected function assertSenderStandardFieldCheck(?CInteropSender $sender, string $class, string $field, $expected, string $type = null) {
    $standard = null;
    if ($sender) {
      $standard = $this->getStandardFromSender($sender, $class);
    }

    $actual = $standard && $standard->_id && property_exists($standard, $field)? $standard->$field : null;
    $check  = $this->check($actual, $expected, $type);
    $item   = new CRequirementsItem($expected, $actual, $check);

    $item->setSection(CAppUI::tr(CClassMap::getSN($class)));
    if ($field === "_id") {
      $field = $source->_spec->key ?? $field;
    }
    $description = CClassMap::getSN($class) . "-" . $field;
    $item->setDescription($description);
    $this->addItems($item);
  }

  /**
   * @param CInteropSender|null $sender
   * @param string              $standard_class
   *
   * @return CExchangeDataFormatConfig|null
   * @throws Exception
   */
  private function getStandardFromSender(?CInteropSender $sender, string $standard_class): ?CExchangeDataFormatConfig {
    switch ($standard_class) {

      case CPhastConfig::class:
        return $sender->loadBackRefConfigPhast();

      case CHL7Config::class:
        return $sender->loadBackRefConfigHL7();

      case CHprimXMLConfig::class:
        return $sender->loadBackRefConfigHprimXML();

      default:
        return null;
    }
  }
}
