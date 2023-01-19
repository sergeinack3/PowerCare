<?php
/**
 * @package Mediboard\Core\Requirements
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Requirements;

/**
 * Class CRequirementsDescription
 */
class CRequirementsDescription {

  private $description;


  /**
   * Get description
   *
   * @return string
   */
  public function render() {
    return $this->description;
  }

  /**
   * Get description
   *
   * @return string
   */
  public function hasDescription() {
    return !!$this->description;
  }

  /**
   * Set description
   *
   * @param string $description
   *
   * @return void
   */
  public function setDescription(string $description) {
    $this->description = $description;
  }

  /**
   * Add description
   *
   * @param string $description
   *
   * @return void
   */
  public function addDescription(string $description) {
    $this->description .= "$description \n";
  }

  /**
   * Add description list
   *
   * @param string $description
   * @param string $limiter
   *
   * @return void
   */
  public function addDescriptionList(string $description, $limiter = "*") {
    $this->addDescription("$limiter $description");
  }

  /**
   * Make line 
   *
   * @return void 
   */
  public function addLine() {
    $this->addDescription("***"); 
  }

  /**
   * Add Title
   *
   * @param string $title
   * @param int    $deep
   *
   * @return void
   */
  public function addTitle(string $title, int $deep = 1) {
    $limiter = str_repeat("#", $deep);

    $this->addDescription("$limiter $title $limiter");
  }
}
