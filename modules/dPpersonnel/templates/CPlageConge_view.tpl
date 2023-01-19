{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=CMbObject_view}}

{{if $object->_can->edit}}
  {{assign var=plage value=$object}}
  <table class="tbl">
    <tr>
      <td class="button">
        <button class="edit" onclick="PlageConge.editModal('{{$plage->_id}}','{{$plage->user_id}}')">
          {{tr}}Modify{{/tr}}
        </button>
      </td>
    </tr>
  </table>
{{/if}}