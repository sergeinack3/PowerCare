{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">{{tr}}mod-mediusers-tab-function_add{{/tr}}</th>
  </tr>
  <tr>
    <td>{{mb_label class=CFunctions field=group_id}}</td>
    <td>
      <select name="group_id"
              onchange="CMediuserFunctions.reloadAddFunctionWithGroup(this.value);" title="{{tr}}CFunctions-group_id{{/tr}}">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$groups item=_group}}
          <option value="{{$_group->_id}}" {{if $group_id === $_group->_id}}selected{{/if}}>{{$_group}}</option>
        {{/foreach}}
      </select>
    </td>
  </tr>
  {{if $group_id}}
    <tr>
      <td>{{mb_label class=CMediusers field=function_id}}</td>
      <td>
        <form name="addMediuserSecundaryFunction" class="prepared" method="post" onsubmit="return CMediuserFunctions.onAddSecFunctSubmit(this);">
          {{mb_class class=CSecondaryFunction}}
          {{mb_field class=CSecondaryFunction field=secondary_function_id hidden=true}}
          <input type="hidden" name="user_id" value="{{$user_id}}" />
          <select name="function_id" id="" onchange="CMediuserFunctions.changeAddPermValues(
                    (this.value === '') ? {{$group_id}} : this.value,
                    (this.value === '') ? 'CGroups' : 'CFunctions'
                  );">
            <option value="">&mdash; {{tr}}CFunctions.none{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_function list=$functions}}
          </select>
        </form>
     </td>
    </tr>
    <tr>
      <td>{{mb_label class=CPermModule field=permission}}</td>
      <td>
        <form name="addMediuserPermission" class="prepared" method="post" onsubmit="return CMediuserFunctions.onPermFormSubmit(this);">
          {{mb_class class=CPermObject}}
          <input type="hidden" name="user_id" value="{{$user_id}}" />
          <input type="hidden" name="object_id" />
          <input type="hidden" name="object_class" />
          {{mb_field object=$perm_object field=permission emptyLabel="CUser-back-permissions_objet.empty" value="1"}}
        </form>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td class="button" colspan="2">
      {{if $group_id}}
        <button type="button" class="add" id="mediuserSFSubmiter" onclick="getForm('addMediuserSecundaryFunction').onsubmit()">
          {{tr}}Add{{/tr}}
        </button>
      {{/if}}
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </td>
  </tr>
</table>