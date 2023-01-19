{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr}}mod-admin-tab-report_prefs{{/tr}}: <strong>{{$key}}</strong></h2>
<strong>{{tr}}pref-{{$key}}{{/tr}}</strong>
<div>{{tr}}pref-{{$key}}-desc{{/tr}}</div>
<hr />

<table class="tbl">
  <tr>
    <th>{{mb_title class=CPreferences field=user_id}}</th>
    <th colspan="2">{{mb_title class=CPreferences field=value}}</th>
  </tr>
  
  {{foreach from=$hierarchy key=profile_id item=user_ids}}
  
  <tr style="font-weight: bold;">
    {{if $profile_id == "default"}} 
      {{assign var=profile_pref value=$default}}
      <td>DEFAULT</td>
    {{else}}
      {{assign var=_profile value=$users.$profile_id}}
      {{assign var=profile_pref value=$_profile->_ref_preference}}
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_profile->_guid}}');">
          {{$_profile}}
        </span>
      </td>
    {{/if}}
    <td class="text">
      {{mb_include template=inc_pref_tooltip preference=$profile_pref}}
    </td>
    <td class="narrow">
      <button class="edit compact notext" onclick="Preferences.edit('{{$profile_pref->_id}}');">
        {{tr}}Edit{{/tr}}
      </button>
    </td>
  </tr>

  {{foreach from=$user_ids item=user_id}}
    {{assign var=_user value=$users.$user_id}}
    {{assign var=user_pref value=$_user->_ref_preference}}
    <tr>
      <td style="padding-left: 2em;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_user->_guid}}');">
          {{$_user}}
        </span>
      </td>
      <td class="text">  
        {{mb_include template=inc_pref_tooltip preference=$user_pref}}
      </td>
      <td class="narrow">
        <button class="edit compact notext" onclick="Preferences.edit('{{$user_pref->_id}}');">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
  {{/foreach}}  
  
  {{/foreach}}
</table>
