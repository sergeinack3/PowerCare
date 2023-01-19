{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="import-{{$type}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-import-{{$type}}')">
  <input type="hidden" name="m" value="{{$import_module}}"/>
  <input type="hidden" name="dosql" value="{{$import_action}}"/>
  <input type="hidden" name="type" value="{{$type}}"/>
</form>

<div id="result-import-{{$type}}"><div>
