{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td style="width: 20%;">
  {{mb_value object=$_perm field=permission}}
  <div style="float: right;">
    {{mb_include module=system template=inc_object_history object=$_perm}}
  </div>
  {{if $owner == "user"}}
  <div style="float: right; margin: 0 1em;">
    <button class="edit notext" type="button" onclick="Modal.open('save_perm_obj-{{$_perm->_guid}}', {width: 300, showClose: true} );">{{tr}}Edit{{/tr}}</button>
    <div id="save_perm_obj-{{$_perm->_guid}}" style="display: none;">
      <form name="Edit-{{$_perm->_guid}}" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); LoadListExistingRights();});">
        {{mb_key object=$_perm}}

        <input type="hidden" name="m" value="admin" />
        <input type="hidden" name="dosql" value="do_perm_object_aed" />
        <input type="hidden" name="del" value="0" />

        <input type="hidden" name="@token" value="{{$token_perm_object_item}}" />

        <table class="tbl">
          <tr>
            <th class="title" colspan="3">
              {{if $_perm->_ref_db_object->_id}}
                {{$_perm->_ref_db_object->_view}}
              {{else}}
                {{tr}}CPermObject-General right|pl{{/tr}}
              {{/if}}
            </th>
          </tr>
          <tr>
            <th>
              {{mb_label object=$permModule field=permission}}
            </th>
            <th></th>
          </tr>
          <tr>
            <td class="narrow">
              {{mb_field object=$_perm field=permission}}
            </td>
            {{if $_perm->_ref_db_object->_view}}
              {{assign var=objname value=$_perm->_ref_db_object->_view|smarty:nodefaults|JSAttribute}}
            {{else}}
              {{assign var=objname value="Droits généraux"}}
            {{/if}}
            <td class="narrow">
              <span style="margin: 0 1em;">
                <button class="save notext" type="submit">{{tr}}Save{{/tr}}</button>
                <button class="trash notext" type="button" onclick="confirmDeletion(this.form,{typeName:'la permission sur',objName:'{{$objname}}'}, function() { Control.Modal.close(); LoadListExistingRights();})">{{tr}}Delete{{/tr}}</button>
              </span>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
  {{/if}}
</td>

