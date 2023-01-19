<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Progress bar helper, used in uploads
 */
class CProgressBar {
  protected $id;
  protected $max;
  protected $value;

  /**
   * Init the progress bar
   *
   * @param string $id  Id
   * @param int    $max Max value
   */
  function __construct($id, $max = null) {
    ob_end_clean();

    $this->id = $id;

    if ($max) {
      $this->init($max);
    }
  }

  /**
   * Init the progress bar's max value
   *
   * @param int $max the max value
   *
   * @return void
   */
  function init($max) {
    $this->value = 0;
    $this->max = $max;

    CAppUI::js("var p=window.parent.$('$this->id');p.style.display=null;p.max=$this->max;p.value=0;");
  }

  /**
   * Increment the progress bar
   *
   * @param int $inc Increment value
   *
   * @return void
   */
  function adv($inc = 1) {
    $this->value += $inc;

    $this->output();
  }

  /**
   * Set progress bar value
   *
   * @param int $value Value of the progress bar
   *
   * @return void
   */
  function advTo($value) {
    $this->value = $value;

    $this->output();
  }

  /**
   * Output callback
   *
   * @return void
   */
  protected function output() {
    CAppUI::js("window.parent.$('$this->id').value=$this->value;");

    flush();
    ob_flush();
  }
}
