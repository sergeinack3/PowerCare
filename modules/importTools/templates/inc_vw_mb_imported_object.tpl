{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object && $object->_id}}
  <h2>{{$object}}</h2>
  {{mb_include module=system template=CMbObject_view object=$object}}
  {{else}}
  <span class="empty">{{tr}}CMbObject.none{{/tr}}</span>
{{/if}}
