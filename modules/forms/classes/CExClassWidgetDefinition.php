<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CExClassWidgetDefinition implements IShortNameAutoloadable {
  public $name;
  
  public $template_name;

  public $default_dimensions = array(
    "width"  => null,
    "height" => null,
  );
  
  /** @var CSmartyDP */
  protected $template;

  /**
   * Prepare template for inclusion
   *
   * @param CExObject $ex_object Reference ExObject, to get data from (patient, sejour, etc)
   * @param string    $mode      Display mode : preview or normal
   *
   * @return void
   */
  function prepareTemplate(CExObject $ex_object, $mode = "normal") {
    
  }

  /**
   * Display template
   *
   * @param CExObject $ex_object Reference ExObject, to get data from (patient, sejour, etc)
   * @param string    $mode      Display mode : preview or normal
   *
   * @return void
   */
  function display(CExObject $ex_object, $mode = "normal") {
    $this->template = new CSmartyDP("modules/forms");
    $this->template->assign("mode", $mode);
    $this->prepareTemplate($ex_object, $mode);
    $this->template->display("widgets/$this->template_name.tpl");
  }
}
