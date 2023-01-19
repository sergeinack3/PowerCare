{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    {{foreach from='Ox\Mediboard\Admin\CUser::getProfiles'|static_call:null key=profile_id item=_profile}}
      <option value="{{$profile_id}}" {{if $profile_id == $value}}selected{{/if}}>
        {{$_profile->user_last_name}}
      </option>
    {{/foreach}}
  </select>
{{else}}
  {{if $value}}
    {{$value}}
  {{/if}}
{{/if}}