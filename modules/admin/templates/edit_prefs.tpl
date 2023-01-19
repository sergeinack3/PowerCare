{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=preferences ajax=true}}

<script>
  defaultPrefs = function (user_id) {
    var url = new Url('admin', "edit_prefs");
    url.addParam('user_id', user_id);
    url.addParam('show_icone', 0);
    url.requestModal("80%", "80%");
  }
</script>

<style>
  /* MODULE ICONS OVERRIDE */
  .module-icon {
    float: left;
  }
</style>

<table class="main">
  <tr>
    <td class="narrow">
      <ul id="tab-modules-pref" class="control_tabs_vertical {{if !$show_icone}}small{{/if}}" style="width:{{if $show_icone}} 20{{else}}10{{/if}}em;">
        {{foreach from=$prefs key=module item=_prefs}}
            {{if $_prefs && ($module == "common" || $module|module_active)}}
            <li>
              <a href="#module-{{$module}}" style="line-height: 24px;">
                {{if $module != "common" && $show_icone}}
                  <div class="module-icon-prefs">
                    {{mb_module_icon mod_name=$module mod_category=$mod_categories.$module}}
                  </div>
                {{/if}}
                {{tr}}module-{{$module}}-court{{/tr}}
                <small>({{$_prefs|@count}})</small>
              </a>
            </li>
          {{/if}}
        {{/foreach}}
        {{if is_dir("./mobile")}}
          <li>
            <a href="#module-mobile" style="line-height: 24px;">
              <img src="mobile/images/icon.png" width="24" style="float: left;" />
              {{tr}}mbMobile{{/tr}}
            </a>
          </li>
        {{/if}}
      </ul>
      <script>
        Main.add(Control.Tabs.create.curry('tab-modules-pref', true));
      </script>
    </td>
    <td>
      <form name="form-edit-preferences" action="?m=admin{{if !$ajax}}&{{$actionType}}={{$action}}{{/if}}" method="post" onsubmit="return Preferences.onSubmitAll(this, '{{$show_icone}}')">
        <input type="hidden" name="dosql" value="do_preference_aed" />
        <input type="hidden" name="m" value="admin" />
        <input type="hidden" name="user_id" value="{{$user->_id}}" />

        <table class="form">
          <col style="width: 40%;" />
          {{if $user_id != "default"}}
            <col style="width: 15%;" />
            {{if !$user->template}}
            <col style="width: 15%;" />
            {{/if}}
            <col style="width: 30%;" />
          {{else}}
            <col style="width: 40%;" />
          {{/if}}

          <tr>
            <th class="title" {{if $can->admin}} colspan="2" {{/if}} >
              {{tr}}Preference{{/tr}}
            </th>
            <th class="title">
              {{if $can->admin && $user_id != "default"}}
                {{if $show_icone}}
                  <a href="?m=admin&tab=edit_prefs&user_id=default" class="button edit">{{tr}}Default{{/tr}}</a>
                {{else}}
                  <button type="button" class="edit" onclick="Control.Modal.close(); defaultPrefs('default');">{{tr}}Default{{/tr}}</button>
                {{/if}}
              {{else}}
                {{tr}}Default{{/tr}}
              {{/if}}
            </th>

            {{if $user_id != "default"}}
              {{if !$user->template}}
                <th class="title">
                  {{tr}}User template{{/tr}} :
                  <br />
                  {{if $can->edit && $prof->_id}}
                    {{if $show_icone}}
                      <a href="?m={{$m}}&tab=edit_prefs&user_id={{$prof->_id}}" class="button edit">{{$prof}}</a>
                    {{else}}
                      <button type="button" class="edit" onclick="Control.Modal.close(); defaultPrefs('{{$prof->_id}}');">{{$prof}}</button>
                    {{/if}}
                  {{else}}
                    {{if $prof->_id}}{{$prof}}{{else}}{{tr}}None{{/tr}}{{/if}}
                  {{/if}}
                </th>
              {{/if}}
              <th class="title">
                {{tr}}{{$user->template|ternary:"User template":"CUser"}}{{/tr}} :
                <br/>{{if $user->_id}}{{$user}}{{else}}{{tr}}None{{/tr}}{{/if}}
              </th>
            {{/if}}
          </tr>

          <!-- Tous modules confondus -->
          {{assign var="module" value="common"}}
          <tbody style="display: none" id="module-{{$module}}">
            {{mb_include template=inc_pref spec=enum var=LOCALE values=$locales value_locale_prefix="language."}}
            {{if $can->admin}}
              {{mb_include template=inc_pref spec=enum var=FALLBACK_LOCALE values=$locales value_locale_prefix="language."}}
            {{/if}}
            {{mb_include template=inc_pref spec=module var=DEFMODULE}}
            {{mb_include template=inc_pref spec=bool var=touchscreen}}
            {{mb_include template=inc_pref spec=bool var=accessibility_dyslexic}}
            {{mb_include template=inc_pref spec=enum var=tooltipAppearenceTimeout values="short|medium|long" value_locale_prefix=""}}
            {{mb_include template=inc_pref spec=enum var=autocompleteDelay values="short|medium|long" value_locale_prefix=""}}
            {{mb_include template=inc_pref spec=bool var=showCounterTip}}
            {{mb_include template=inc_pref spec=bool var=useEditAutocompleteUsers}}
            {{mb_include template=inc_pref spec=enum var=textareaToolbarPosition values="right|left"}}
            {{mb_include template=inc_pref spec=bool var=planning_dragndrop}}
            {{mb_include template=inc_pref spec=bool var=planning_resize}}
            {{mb_include template=inc_pref spec=enum var=planning_hour_division values="2|3|4|6"}}
            {{if $conf.session_handler == "zebra" || $conf.session_handler == "mysql" || $conf.session_handler == "redis"}}
              <tr>
                <td colspan="2">
                  <div style="float: right;" class="info">{{tr var1=$session_lifetime}}CPreferences-msg-Server session lifetime configuration : %s minutes{{/tr}}</div>
                </td>
                <td colspan="3"></td>
              </tr>
              {{mb_include template=inc_pref spec=enum var=sessionLifetime values=$session_lifetime_enum}}
            {{/if}}
            {{mb_include template=inc_pref spec=bool var=notes_anonymous}}
            {{mb_include template=inc_pref spec=num var=navigationHistoryLength}}
            {{mb_include template=inc_pref spec=bool var=displayUTCDate}}
          </tbody>

          {{foreach from=$prefs key=module item=_prefs}}
            {{if $module != "common" && $module|module_active}}
            <tbody style="display: none;" id="module-{{$module}}">
              {{mb_include module=$module template=preferences}}
            </tbody>
            {{/if}}
          {{/foreach}}

          <tr>
            <td class="button" colspan="5">
              <button type="submit" class="submit singleclick">{{tr}}Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
