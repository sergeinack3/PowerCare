{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value_locale_prefix value="pref-$var-"}}
{{mb_default var=use_locale value=1}}

{{if !is_array($values)}} 
  {{assign var=values value='|'|explode:$values}}
{{/if}}

<select name="pref[{{$var}}]">
  {{if $user_id != "default"}} 
    <option value="">&mdash; {{tr}}Ditto{{/tr}}</option>
  {{/if}}

  {{foreach from=$values item=_value}}
  <option value="{{$_value}}" {{if $pref.user == $_value}} selected="selected" {{/if}}>
    {{if $use_locale}}
      {{tr}}{{$value_locale_prefix}}{{$_value}}{{/tr}}
    {{else}}
      {{$_value}}
    {{/if}}
	</option>
	{{/foreach}}
</select>
