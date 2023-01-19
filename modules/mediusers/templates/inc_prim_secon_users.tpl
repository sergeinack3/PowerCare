{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$function->_id}}
  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab_user', true);

    CMediusers.filter_same_function = false;
  });
</script>


<ul id="tab_user" class="control_tabs small me-no-border-top">
  <li>
    <a {{if !$total_sec_functions}}class="empty"{{/if}} href="#list-primary-users" id="list-primary-users-title">
      Principaux <small>({{$total_sec_functions}})</small>
    </a>
  </li>
  <li>
    <a {{if !$function->_back.secondary_functions|@count}}class="empty"{{/if}} href="#list-secondary-users" id="list-secondary-users-title">
      Secondaires <small>({{$function->_back.secondary_functions|@count}})</small>
    </a>
  </li>
</ul>

<div id="list-primary-users" style="display: none;" class="me-padding-0">
  <table class="tbl me-padding-0 me-w100 me-margin-top-0 me-no-border-radius-top">
    <tr>
      <td colspan="6">
        <form name="listFilterPrimaryUsers" action="?" method="get">
          {{mb_include module=system template=inc_pagination total=$total_sec_functions current=$page_function change_page='changePagePrimaryUsers' step=25}}
        </form>
      </td>
    </tr>
    <tr>
      <th>{{mb_title class=CUser field=user_username}}</th>
      <th>{{mb_title class=CUser field=user_last_name}}</th>
      <th>{{mb_title class=CUser field=user_first_name}}</th>
      <th>{{mb_title class=CUser field=user_type}}</th>
      <th>{{mb_title class=CUser field=profile_id}}</th>
      <th>{{mb_title class=CMediusers field=_user_last_login}}</th>
    </tr>
    {{foreach from=$primary_users item=_user}}
    <tr {{if !$_user->actif}}class="hatching"{{/if}}>
      {{assign var=user_id value=$_user->_id}}
      {{assign var="href" value="?m=mediusers&tab=vw_idx_mediusers&user_id=$user_id"}}
      <td><a href="{{$href}}">{{$_user->_user_username}}</a></td>
      <td><a href="{{$href}}">{{$_user->_user_last_name}}</a></td>
      <td><a href="{{$href}}">{{$_user->_user_first_name}}</a></td>
      <td>
        {{assign var=type value=$_user->_user_type}}
        {{if array_key_exists($type, $utypes)}}{{$utypes.$type}}{{/if}}
      </td>
      <td>{{$_user->_ref_profile->user_username}}</td>
      <td>
        {{if $_user->_user_last_login}}
        <label title="{{mb_value object=$_user field=_user_last_login}}">
          {{mb_value object=$_user field=_user_last_login format=relative}}
        </label>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CFunctions-back-users.empty{{/tr}}</td>
    </tr>
    {{/foreach}}
  </table>
</div>

<div id="list-secondary-users" style="display: none;">
  {{if $can->edit}}
  <form name="addSecUser" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: changePagePrimaryUsers})">
    {{mb_class class=CSecondaryFunction}}
    {{mb_field class=CSecondaryFunction field=secondary_function_id hidden=true}}
    <input type="hidden" name="function_id" value="{{$function->_id}}" />
    <input type="hidden" name="del" value="0" />
    <table class="form">
      <tr>
        <th class="title" colspan="2">
          Ajout d'un utilisateur
        </th>
      </tr>

      <tr>
        <th>{{mb_label object=$secondary_function field="user_id"}}</th>
        <td>
          {{mb_script module=mediusers script=CMediusers ajax=1}}
          <script>
            Main.add(CMediusers.standardAutocomplete.curry('addSecUser', 'user_id', '_user_view'));
          </script>
          {{mb_field object=$secondary_function field="user_id" hidden=1}}
          <input type="text" name="_user_view" value="" class="autocomplete"/>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button class="submit" name="btnFuseAction" type="submit">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
  {{/if}}

  <table class="tbl">
    <tr>
      <th>{{mb_title class=CUser field=user_username}}</th>
      <th>{{mb_title class=CUser field=user_last_name}}</th>
      <th>{{mb_title class=CUser field=user_first_name}}</th>
      <th>{{mb_title class=CUser field=user_type}}</th>
      <th>{{mb_title class=CUser field=profile_id}}</th>
      <th>{{mb_title class=CUser field=_user_last_login}}</th>
      <th></th>
    </tr>
    {{foreach from=$function->_back.secondary_functions item=curr_function}}
    <tr>
      {{assign var=user_id value=$curr_function->_ref_user->_id}}
      {{assign var="href" value="?m=mediusers&tab=vw_idx_mediusers&user_id=$user_id"}}
      <td><a href="{{$href}}">{{$curr_function->_ref_user->_user_username}}</a></td>
      <td><a href="{{$href}}">{{$curr_function->_ref_user->_user_last_name}}</a></td>
      <td><a href="{{$href}}">{{$curr_function->_ref_user->_user_first_name}}</a></td>
      <td>
        {{assign var=type value=$curr_function->_ref_user->_user_type}}
        {{if array_key_exists($type, $utypes)}}{{$utypes.$type}}{{/if}}
      </td>
      <td>{{$curr_function->_ref_user->_ref_profile->user_username}}</td>
      <td>
        {{if $curr_function->_ref_user->_user_last_login}}
        <label title="{{mb_value object=$curr_function->_ref_user field=_user_last_login}}">
          {{mb_value object=$curr_function->_ref_user field=_user_last_login format=relative}}
        </label>
        {{/if}}
      </td>
      <td class="button">
        {{if $can->edit}}
        <form name="delSecUser-{{$curr_function->_id}}" action="?" method="post">
          {{mb_class object=$curr_function}}
          {{mb_key   object=$curr_function}}
          <input type="hidden" name="del" value="1" />
          <button class="trash notext" type="button"
                  onclick="confirmDeletion(this.form, {
                    typeName: 'l\'utilisateur secondaire',
                    objName: '{{$curr_function->_ref_user->_view|smarty:nodefaults|JSAttribute}}',
                    ajax: true},
                    {onComplete: changePagePrimaryUsers})">
            {{tr}}Delete{{/tr}}
          </button>
        </form>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">{{tr}}CFunctions-back-secondary_functions.empty{{/tr}}</td>
    </tr>
    {{/foreach}}
  </table>
</div>