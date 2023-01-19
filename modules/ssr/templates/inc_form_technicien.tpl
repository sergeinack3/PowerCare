{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CTechnicien" action="?m={{$m}}" method="post" onsubmit="return Technicien.onSubmit(this)">
  {{mb_key   object=$technicien}}
  {{mb_class object=$technicien}}
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    <tr>
      {{if $technicien->_id}}
        <th class="title modify" colspan="2">
          {{mb_include module=system template=inc_object_notes      object=$technicien}}
          {{mb_include module=system template=inc_object_idsante400 object=$technicien}}
          {{mb_include module=system template=inc_object_history    object=$technicien}}
          {{tr}}CTechnicien-title-modify{{/tr}}
          '{{$technicien}}'
        </th>
      {{else}}
        <th class="title me-th-new" colspan="2">{{tr}}CTechnicien-title-create{{/tr}}</th>
      {{/if}}
    </tr>
    <tr>
      <th>{{mb_label object=$technicien field=plateau_id}}</th>
      <td>
        {{mb_field object=$technicien field=plateau_id hidden=1}}
        {{mb_value object=$technicien field=plateau_id}}
        </td>
    </tr>
    <tr>
      <th>{{mb_label object=$technicien field=kine_id}}</th>
      <td>
        <select name="kine_id" class="{{$technicien->_props.kine_id}}">
          <option value="0">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$kines selected=$technicien->kine_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$technicien field=actif}}</th>
      <td>{{mb_field object=$technicien field=actif}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $technicien->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {
            typeName:'le technicien ',
            objName:'{{$technicien->_view|smarty:nodefaults|JSAttribute}}',
            ajax: 1}, Technicien.edit.curry('{{$plateau->_id}}', ''))">
            {{tr}}Delete{{/tr}}
          </button>

        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

    {{if $alteregos|@count}}
      <tr>
        <td class="button" colspan="2">
          {{mb_label object=$technicien field=_transfer_id}}
          {{mb_field object=$technicien field=_transfer_id options=$alteregos}}
          <button class="change" type="submit" onclick="return Technicien.confirmTransfer(this.form, '{{$technicien->_count_sejours_date}}')">
            {{tr}}Transfer{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>