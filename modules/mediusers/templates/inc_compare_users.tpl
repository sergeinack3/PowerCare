{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="section" colspan="6">{{tr}}CUser{{/tr}}</th>
</tr>

<tr>
  <th class="narrow" colspan="2" width="33%">{{tr}}CUser-user_username{{/tr}}</th>
  {{if !$new_profile}}
    <td align="center" colspan="2" width="33%">{{$compare.old_profile->user_username}}</td>
  {{/if}}
  <td align="center" colspan="{{if $new_profile}}4{{else}}2{{/if}}" width="{{if $new_profile}}66%{{else}}33%{{/if}}"
      {{if $compare.old_profile->user_username !== $compare.new_profile->user_username}}class="warning"{{/if}}>
    {{$compare.new_profile->user_username}}
  </td>
</tr>

<tr>
  {{assign var=_old_type value=$compare.old_profile->user_type}}
  {{assign var=_new_type value=$compare.new_profile->user_type}}
  <th class="narrow" colspan="2">{{tr}}CUser-user_type{{/tr}}</th>
  {{if !$new_profile}}
    <td align="center" colspan="2">{{$user_types.$_old_type}}</td>
  {{/if}}
  <td align="center" colspan="{{if $new_profile}}4{{else}}2{{/if}}" {{if $user_types.$_old_type !== $user_types.$_new_type}}class="warning"{{/if}}>
    {{$user_types.$_new_type}}
  </td>
</tr>