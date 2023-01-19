<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CSSPILink;

CCanDo::check();

$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire");

CView::checkin();

$sspi_link = new CSSPILink();

$where = array(
  "bloc_id" => "= '$bloc_id'"
);

$sspi_ids = $sspi_link->loadColumn("sspi_id", $where);

$count_sspi_ids = count($sspi_ids);

echo CMbArray::toJSON(
  array(
    "count"   => $count_sspi_ids,
    "sspi_id" => $count_sspi_ids === 1 ? reset($sspi_ids) : null
  )
);