{{*
 * @package Mediboard\OxExploitation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
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
  {{if $user_id != "default"}}
    <td class="{{if $pref.template !== null || $pref.user !== null}} redefined {{else}} active {{/if}}">
      {{if $pref.default}}
        {{'Ox\Mediboard\Astreintes\CCategorieAstreinte::getName'|static_call:$pref.default}}
      {{/if}}
    </td>

    {{if !$user->template}}
      <td class="{{if $pref.user !== null}} redefined {{else}} active {{/if}}">
        {{if $pref.template}}
          {{'Ox\Mediboard\Astreintes\CCategorieAstreinte::getName'|static_call:$pref.template}}
        {{/if}}
      </td>
    {{/if}}
  {{/if}}
  <td>
    {{mb_include module=astreintes template="inc_pref_field_$spec"}}
  </td>
</tr>