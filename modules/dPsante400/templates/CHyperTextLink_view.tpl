{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id && !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}

      {{tr}}CHyperTextLink{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label object=$object field="name"}}</th>
    <td>{{mb_value object=$object field="name"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$object field="link"}}</th>
    <td>{{mb_value object=$object field="link"}}</td>
  </tr>

  <tr>
    <td colspan="2" class="button">
      <button type="button" class="submit"
              onclick="HyperTextLink.edit('{{$object->object_id}}', '{{$object->object_class}}', '{{$object->_id}}', 1);">Modifier
      </button>
    </td>
  </tr>
</table>