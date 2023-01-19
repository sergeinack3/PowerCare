{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value_locale_prefix value="pref-$var-"}}
{{mb_default var=use_locale value=1}}

<select name="pref[{{$var}}]">
    <option value="">&mdash; {{tr}}Ditto{{/tr}}</option>

    {{foreach from=$values key=_key item=_value}}
      <option value="{{$_key}}" {{if $pref.user == $_key}} selected="selected" {{/if}}>
          {{if $use_locale}}
              {{tr}}{{$value_locale_prefix}}{{$_value}}{{/tr}}
          {{else}}
              {{$_value}}
          {{/if}}
      </option>
    {{/foreach}}
</select>
