<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CView;

$module = CView::get("module", "str");
$name   = CView::get("name", "str");
$value  = CView::get("value", "str");

// Ajout de la valeur en session
$_SESSION[$module][$name] = $value;
CView::checkin();
