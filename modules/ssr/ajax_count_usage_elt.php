<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkEdit();

$line_elt_id = CView::get("line_elt_id", "ref class|CPrescriptionLineElement");
CView::checkin();

$line = new CPrescriptionLineElement();
$line->load($line_elt_id);

echo $line->countUsageElement();