{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=is_static value=false}}

{{if $is_static}}
  {{assign var=config value='Ox\Mediboard\System\ConfigurationManager::getConfigSpec'|static_call:$_feature}}
{{else}}
  {{assign var=config value='Ox\Mediboard\System\CConfigurationModelManager::getConfigSpec'|static_call:$_feature}}
{{/if}}

{{if $is_last}}
  {{assign var=_list value='|'|explode:$_prop.list}}
  <select class="{{$_prop.string}}" name="c[{{$_feature}}]" {{if $is_inherited}} disabled {{/if}}>
    {{foreach from=$_list item=_item}}
      <option value="{{$_item}}" {{if $_item == $value}} selected {{/if}}>
        {{if "localize"|array_key_exists:$config}}
          {{tr}}config-{{$_feature|replace:' ':'-'}}.{{$_item}}{{/tr}}
        {{else}}
          {{$_item}}
        {{/if}}
      </option>
    {{/foreach}}
  </select>
{{else}}
  {{if "localize"|array_key_exists:$config}}
    {{tr}}config-{{$_feature|replace:' ':'-'}}.{{$value}}{{/tr}}
  {{else}}
    {{$value}}
  {{/if}}
{{/if}}
