<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;

$lettre = CView::get("lettre", "str");
CView::checkin();

$file_path = CAppUI::conf("dPhospi CLit acces_icons_sortants") . "/$lettre.png";
$file_path = CAppUI::conf("dPhospi CLit acces_icons_sortants") . "/$lettre.png";

readfile($file_path);