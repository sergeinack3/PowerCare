<?php
/**
 * @package Mediboard\Core\Requirements
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Requirements;

use Ox\Core\CAppUI;

/**
 * Class CRequirementsItem
 */
class CRequirementsItem {

  /** @var string */
  const TAB_UNDEFINED = 'default';

  /** @var string */
  const GROUP_UNDEFINED = 'default';

  /** @var string */
  const SECTION_UNDEFINED = 'default';


  /** @var string */
  private $tab;

  /** @var string */
  private $group;

  /** @var string */
  private $section;

  /** @var string */
  private $description;

  /** @var mixed */
  private $expected;

  /** @var mixed */
  private $actual;

  /** @var bool */
  private $check;

  /**
   * CRequirementsItem constructor.
   *
   * @param mixed $expected
   * @param mixed $actual
   * @param bool  $check
   */
  public function __construct($expected, $actual, bool $check) {
    if ($expected === CRequirementsManager::TYPE_EXPECTED_NOTNULL) {
        $expected = CAppUI::tr('common-error-Must not be empty');
        $actual   = $actual === "" ? null : $actual;
    } elseif ($expected === false) {
        $expected = 0;
    } elseif ($expected === true) {
        $expected = 1;
    }
    $this->expected = $expected;
    $this->actual   = $actual;
    $this->check    = $check;
  }

  /**
   * @return string
   */
  public function getTab(): string {
    return $this->tab ?: self::TAB_UNDEFINED;
  }

  /**
   * @param string $tab
   */
  public function setTab(string $tab): void {
    $this->tab = $tab;
  }

  /**
   * @return string
   */
  public function getGroup(): string {
    return $this->group ?: self::GROUP_UNDEFINED;
  }

  /**
   * @param string $group
   */
  public function setGroup(string $group): void {
    $this->group = $group;
  }

  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description ?? "";
  }

  /**
   * @param string $description
   * @param bool   $translate
   *
   * @return void
   */
  public function setDescription(string $description, bool $translate = true): void {
    $this->description = $translate ? CAppUI::tr($description) : $description;
  }

  /**
   * @return mixed
   */
  public function getExpected() {
    return $this->expected;
  }

  /**
   * @return mixed
   */
  public function getActual() {
    return $this->actual;
  }

  /**
   * @return bool
   */
  public function isCheck(): bool {
    return $this->check;
  }

  /**
   * @return array
   */
  public function serialize(): array {
    return get_object_vars($this);
  }

  /**
   * @return string
   */
  public function getSection(): string {
    return $this->section ?: self::SECTION_UNDEFINED;
  }

  /**
   * @param string $section
   *
   * @return void
   */
  public function setSection(string $section): void {
    $this->section = $section;
  }
}
