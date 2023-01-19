{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit{{$idex->_guid}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="dosql" value="do_idsante400_aed"/>
  <input type="hidden" name="del" value="0"/>
  {{mb_key object=$idex}}
  <input type="hidden" name="callback" value="reloadId400"/>

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$idex show_identifiers=false show_notes=false show_id=true}}

    {{if $idex->_id}}
    <tr>
      <th>{{mb_label object=$idex field="datetime_create"}}</th>
      <td>{{mb_value object=$idex   field="datetime_create"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$idex field="last_update"}}</th>
      <td>{{mb_value object=$idex   field="last_update"}}</td>
    </tr>
    {{/if}}

    <tr>
      <th>{{mb_label object=$filter field="object_class"}}</th>
      <td>
        {{if $dialog && $target}}
          <input type="hidden" name="object_class" class="{{$filter->_props.object_class}}" value="{{$filter->object_class}}"/>
          {{tr}}{{$filter->object_class}}{{/tr}}
        {{else}}
          <select name="object_class" class="{{$idex->_props.object_class}}">
            <option value="">&mdash; Choisir une classe</option>
            {{foreach from=$listClasses item=curr_class}}
              <option value="{{$curr_class}}" {{if $curr_class == $idex->object_class}}selected{{/if}}>
                {{$curr_class}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field="object_id"}}</th>
      <td>
        {{if $dialog && $target}}
          <input type="hidden" name="object_id" class="{{$filter->_props.object_id}}" value="{{$filter->object_id}}"/>
          {{$target->_view}}
        {{else}}
          {{mb_field object=$idex field=object_id}}
          <button class="search" type="button" onclick="ObjectSelector.initEdit()">Chercher</button>
          <script type="text/javascript">
            ObjectSelector.initEdit = function () {
              this.sForm = "edit{{$idex->_guid}}";
              this.sId = "object_id";
              this.sClass = "object_class";
              this.onlyclass = "false";
              this.pop();
            }
          </script>
        {{if $idex->_id}}
        <br/>
          {{$idex->_ref_object->_view}}
        {{/if}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$idex field="id400" }}</th>
      <td>{{mb_field object=$idex field="id400" canNull="false"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$idex field="tag"}}</th>
      <td>{{mb_field object=$idex field="tag" size="40"}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $idex->_id}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {
            typeName: 'l\'identifiant',
            objName: '{{$idex->_view|smarty:nodefaults|JSAttribute}}',
            }, Control.Modal.close )">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button type="submit" class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
