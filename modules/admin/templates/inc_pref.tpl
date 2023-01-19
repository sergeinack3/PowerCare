{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}
{{mb_default var=parite value=0}}

<tr class="preferences-line">
  {{if $can->admin}}
    <td class="narrow">
      <button class="search notext compact" type="button" onclick="Preferences.report('{{$var}}');">
        {{tr}}Report{{/tr}}
      </button>
    </td>
  {{/if}}
  <th>
    <label for="pref[{{$var}}]" title="{{tr}}pref-{{$var}}-desc{{/tr}}">{{tr}}pref-{{$var}}{{/tr}}</label>
  </th>

  {{assign var=pref value=$prefs.$module.$var}}
  {{if $user_id !== "default"}}
    <td class="{{if $pref.template !== null || $pref.user !== null}} redefined {{else}} active {{/if}}">
      {{if $readonly && $can->admin && $pref.user != $pref.default}}
        <button class="copy notext" type="button"
                onclick="Preferences.savePreference('pref[{{$var}}]', '{{$pref.user}}', '')" style="float: right;">{{tr}}mod-admin-copy-prop{{/tr}}</button>
      {{/if}}
      {{mb_include template="inc_pref_value_$spec" value=$pref.default}}
    </td>

    {{if !$user->template}}
      <td class="{{if $pref.user !== null}} redefined {{else}} active {{/if}}">
        {{if $readonly && $can->admin && $user->profile_id && $pref.user != $pref.template}}
          <button class="copy notext" type="button" style="float: right;"
                  onclick="Preferences.savePreference('pref[{{$var}}]', '{{$pref.user}}', '{{$user->profile_id}}')">{{tr}}mod-admin-copy-prop{{/tr}}</button>
        {{/if}}
        {{if $pref.template !== null}}
          {{mb_include template="inc_pref_value_$spec" value=$pref.template}}
        {{/if}}

      </td>
    {{/if}}
  {{/if}}
  <td>
    {{mb_include template="inc_pref_field_$spec"}}
  </td>
</tr>
