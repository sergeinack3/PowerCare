<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

$reference_class   = CView::post("reference_class", "str notNull");
$reference_id      = CView::post("reference_id", "ref class|CMbObject meta|reference_class notNull");
$ex_class_event_id = CView::post("ex_class_event_id", "ref class|CExClassEvent notNull");
$callback          = CView::post("callback", "str notNull");

CView::checkin();

$ex_class_event = new CExClassEvent();
$ex_class_event->load($ex_class_event_id);

/** @var CMbObject $reference */
$reference = new $reference_class();
$reference->load($reference_id);

$host = new $ex_class_event->host_class;

$method = "formTabAction_" . $callback;

if (!method_exists($host, $method)) {
  throw new CMbException("Form tab action method '$callback' does not exist");
}

$object = $host->$method($reference);

$ex_class_event->getTabHostObject($object);

$struct = array(
  "msg"               => CAppUI::getMsg(),
  "ex_class_event_id" => $ex_class_event->_id,
  "event_name"        => $ex_class_event->event_name,
  "ex_class_id"       => $ex_class_event->ex_class_id,
  "host_class"        => $ex_class_event->_host_object->_class,
  "host_id"           => $ex_class_event->_host_object->_id,
  "tab_actions"       => $ex_class_event->_tab_actions,
);

CApp::json($struct);
