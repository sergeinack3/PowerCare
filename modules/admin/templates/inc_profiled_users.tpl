{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $user->template}} 
<table class="tbl">
  {{foreach from=$user->_ref_profiled_users item=_user}}
  <tr>
    <td><span onmouseover="ObjectTooltip.createEx(this, '{{$_user->_guid}}');">{{$_user}}</span></td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">{{tr}}CUser-none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
   
{{else}}
<div class="small-info">
  {{tr}}CUser-msg-noprofiled-not-template{{/tr}}
</div>
{{/if}}

