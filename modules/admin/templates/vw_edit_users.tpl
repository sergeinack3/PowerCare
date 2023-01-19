{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}
{{mb_script module=admin script=user_permission}}

<script>
  Main.add(function() {
    {{if $user_id}}
      UserPermission.editUser('{{$user_id}}', '{{$tab_name}}');
    {{/if}}
    getForm('listFilterUser').onsubmit();
  });
</script>

<div style="padding-bottom: 5px" class="me-margin-top-8">
  {{if $can->edit}}
    {{if !'Ox\Core\CAppUI::restrictUserCreation'|static_call:null}}
      <a href="#" onclick="UserPermission.editUser(0)" class="button new">
        {{tr}}CMediusers-title-create{{/tr}}
      </a>
    {{else}}
      <div class="small-info">
        Seule la création d'utilisateurs depuis l'AD est disponible. Veuillez vous rendre dans le module <strong>Mediusers</strong>.
      </div>
    {{/if}}
  {{/if}}

  <style>
    fieldset.fieldset_search div {
      display: inline-block;
    }
  </style>
</div>

<form name="listFilterUser" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result_search_users')">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_search_users" />
  <input type="hidden" name="page" value="0" onchange="this.form.onsubmit()"/>
  <input type="hidden" name="order_col" value="user_username"/>
  <input type="hidden" name="order_way" value="ASC""/>

  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

      <td>
        <table class="main form">
          <tr>
            <th> {{tr}}Keywords{{/tr}} : </th>
            <td> <input type="text" name="filter" value="{{$filter}}" style="width: 20em;" onchange="$V(this.form.page, 0)" /> </td>

            <th><label for="user_type" title="{{tr}}CUser-User type-desc{{/tr}}">{{tr}}CUser-user_type{{/tr}}</label></th>
            <td>
              <select name="user_type">
                <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                {{foreach from=$utypes key=_key item=type}}
                  <option value="{{$_key}}" {{if $_key == $user_type}}selected{{/if}}>{{$type}}</option>
                {{/foreach}}
              </select>
            </td>

            <th colspan=""><label for="template" title="{{tr}}common-Status{{/tr}}">{{tr}}common-Status{{/tr}}</label></th>

            <td class="text">
              <select name="template">
                <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                <option value="0" {{if $template == "0"}}selected{{/if}}>{{tr}}CUser-template.user{{/tr}}</option>
                <option value="1" {{if $template == "1"}}selected{{/if}}>{{tr}}CUser-template.profile{{/tr}}</option>
              </select>
            </td>
          </tr>

          <tr>
            <th> {{tr}}CUser-Locking{{/tr}} </th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="locked" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}CUser-_login_locked{{/tr}} <input name="locked" value="1" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}CUser-_login_not_locked{{/tr}} <input name="locked" value="0" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>

            <th>{{tr}}common-Inactive{{/tr}}</th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="inactif" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}common-Inactive|pl{{/tr}} <input name="inactif" value="1" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}common-Active|pl{{/tr}} <input name="inactif" value="0" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>

            <th> {{tr}}CUser-Type of user{{/tr}} </th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="user_loggable" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}common-Human{{/tr}} <input name="user_loggable" value="human" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>{{tr}}common-Bot{{/tr}} <input name="user_loggable" value="robot" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>
          </tr>

          <tr>
            <td colspan="6">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>

<div id="result_search_users" style="overflow: hidden" class="me-padding-10"></div>
