{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=perm}}

<style type="text/css">
  div.bullet {
    width: 6px;
    height: 6px;
    display: inline-block;
  }

  div.bullet.read {
    background-color: #139DF9;
  }

  div.bullet.edit {
    background-color: #97E406;
  }

  div.bullet.empty {
    background-color: #ddd;
  }

  table.tbl td.case_droit {
    font-size: 1px;
    text-align: center;
  }
</style>

<form name="listFilterFunction" action="?" method="get" class="prepared">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="viewAllPerms" />

  <table class="main layout me-margin-10">
    <tr>
      <td colspan="2">
        <button type="submit" class="tick me-float-left">
          {{if (is_array($users_ids) && $users_ids|@count === 0)
          || (is_array($profiles_ids) && $profiles_ids|@count === 0)}}
            {{tr}}common-action-Display selection{{/tr}}
          {{else}}
            {{tr}}common-action-Display all{{/tr}}
          {{/if}}
        </button>
        <button type="button" class="search me-float-left" onclick="Perm.legend();">{{tr}}Legend{{/tr}}</button>
      </td>
    </tr>
  </table>

  <table class="tbl me-margin-10 me-table-col-separated" style="font-size: 0.9em;">
    {{if !$only_profil}}
      <tr>
        <th class="narrow" style="background-color: gray;"></th>
        <th colspan="3" class="text" style="background-color: gray; color: #000;">{{tr}}common-noun-Profile{{/tr}}</th>

        {{foreach from=$list_modules item=curr_mod}}
          {{th_vertical class="text"}}{{tr}}module-{{$curr_mod->mod_name}}-court{{/tr}}{{/th_vertical}}
        {{/foreach}}
      </tr>
      {{foreach from=$profiles item=_profile}}
        {{assign var=user_id value=$_profile->_id}}
        <tr>
          <td style="text-align: center;">
            <input type="checkbox" name="profiles_ids[{{$_profile->_id}}]" value="{{$_profile->_id}}">
          </td>
          <td colspan="3">{{$_profile}}</td>
          {{foreach from=$list_modules item=curr_mod}}
            {{assign var=mod_id value=$curr_mod->_id}}
            <td
              style="font-size: 1px; text-align: center; {{if $matrix_profil.$user_id.$mod_id.type == "spécifique"}} background-color: #faa;{{/if}}"
              title="{{$matrix_profil.$user_id.$mod_id.text}} ({{$matrix_profil.$user_id.$mod_id.type}})">
              <div class="bullet {{$matrix_profil.$user_id.$mod_id.permIcon}}"></div>
              <div class="bullet {{$matrix_profil.$user_id.$mod_id.viewIcon}}"></div>
            </td>
          {{/foreach}}
        </tr>
      {{/foreach}}
    {{/if}}

    {{if !$only_user}}
      {{foreach from=$list_functions item=curr_func}}
        <tr>
          <th class="narrow" style="background-color: #{{$curr_func->color}};"></th>
          <th class="text" style="background-color: #{{$curr_func->color}}; color: #000;">{{$curr_func}}</th>
          <th style="background-color: #{{$curr_func->color}}; color: #000;">Profil</th>
          {{assign var=color value=$curr_func->color}}
          {{th_vertical class="text" style="background-color: #$color; color: #000;"}}{{tr}}Remote access{{/tr}}{{/th_vertical}}

          {{foreach from=$list_modules item=curr_mod}}
            {{th_vertical class="text"}}{{tr}}module-{{$curr_mod->mod_name}}-court{{/tr}}{{/th_vertical}}
          {{/foreach}}
        </tr>
        {{foreach from=$curr_func->_ref_users key=user_id item=curr_user}}
          <tr>
            <td class="me-text-align-center">
              <input type="checkbox" name="users_ids[{{$user_id}}]" value="{{$curr_func}}">
            </td>
            <td>{{$curr_user}}</td>
            <td>{{$curr_user->_ref_profile->user_last_name}}</td>
            <td style="background: {{if $curr_user->remote}}#d99{{else}}#9d9{{/if}};">{{if
                $curr_user->remote}}{{tr}}common-No{{/tr}}{{else}}{{tr}}common-Yes{{/tr}}{{/if}}</td>
            {{if $curr_user->actif}}
              {{foreach from=$list_modules item=curr_mod}}
                {{assign var=mod_id value=$curr_mod->_id}}
                <td class="case_droit"
                    {{if $matrice.$user_id.$mod_id.type === "spécifique"}}
                      style="background-color: #faa; cursor: pointer;"
                      onclick="Perm.editPermModule('{{$user_id}}', '{{$mod_id}}', this);"
                    {{/if}}
                    title="{{$matrice.$user_id.$mod_id.text}} ({{$matrice.$user_id.$mod_id.type}})">
                  <div class="bullet {{$matrice.$user_id.$mod_id.permIcon}}"></div>
                  <div class="bullet {{$matrice.$user_id.$mod_id.viewIcon}}"></div>
                </td>
              {{/foreach}}
            {{else}}
              <td class="cancelled" colspan="100">{{tr}}common-Inactive{{/tr}}</td>
            {{/if}}
          </tr>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
  </table>
</form>
