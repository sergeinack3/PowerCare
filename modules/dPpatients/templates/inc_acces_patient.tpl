{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  togglePerm = function (input) {
    window.save_input = input;

    var form = getForm("alterPerm");

    $V(form.user_id, input.get("user_id"));

    var id = input.get("id");

    $V(form.del, "0");
    $V(form.perm_object_id, "");

    if (id) {
      $V(form.perm_object_id, id);
      $V(form.del, "1");
    }

    onSubmitFormAjax(form);
  };

  callbackPerm = function (perm_id) {
    window.save_input.set("id", perm_id ? perm_id : "");
  };

  refuse_sharing = function () {
    $$('.perm_checkboxes:checked').invoke('click');
  }
</script>

<table class="tbl">
  <thead>
  <tr>
    <th colspan="2">
      <button type="button" class="cancel" style="float: left" onclick="refuse_sharing()">{{tr}}CPatient-refuse-sharing{{/tr}}</button>
      {{tr}}Practitioner{{/tr}}
    </th>
  </tr>
  </thead>
  {{foreach from=$users item=_user key=_user_id}}
    {{assign var=checked value=1}}
    {{assign var=_perm value=null}}
    {{if isset($perms_by_user.$_user_id|smarty:nodefaults)}}
      {{assign var=checked value=0}}
      {{assign var=_perm value=$perms_by_user.$_user_id}}
    {{/if}}
    <tr>
      <td>
        <input type="checkbox" class="perm_checkboxes" id="perm_{{$_user_id}}" {{if $checked}}checked{{/if}} onclick="togglePerm(this);"
          {{if $_perm}}data-id="{{$_perm->_id}}"{{/if}} data-user_id="{{$_user_id}}" />
      </td>
      <td>
        <label for="perm_{{$_user_id}}">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user}}
        </label>
      </td>
    </tr>
  {{/foreach}}
</table>

<form name="alterPerm" method="post">
  {{mb_class object=$perm}}
  {{mb_key   object=$perm}}
  {{mb_field object=$perm field=object_class hidden=1}}
  {{mb_field object=$perm field=object_id    hidden=1}}
  {{mb_field object=$perm field=user_id      hidden=1}}
  {{mb_field object=$perm field=permission   hidden=1}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="callbackPerm" />
</form>