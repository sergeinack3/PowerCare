{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=category_versions value=$_families->_versions_category.$_category_name}}
{{assign var=prefix_translate_version value=$_families->_prefix_translate_version}}
{{if $category_versions|@count > 1}}
  <select name="version" onchange="this.form.onsubmit()">
    {{foreach from=$category_versions key=main_version item=_category_version}}
      {{if is_array($_category_version)}}
        <optgroup label="{{tr}}{{$_family_name}}-versions.{{$main_version}}{{/tr}}">
          {{foreach from=$_category_version item=version}}
            <option value="{{$main_version}}|{{$version}}"
                    {{if $_message_supported->version=="$main_version|$version"}}selected="selected"{{/if}}>
              {{$version}} ({{tr}}{{$prefix_translate_version}}-versions.{{$main_version}}{{/tr}})
            </option>
          {{/foreach}}
        </optgroup>
      {{else}}
        {{assign var=version value=$_category_version}}
        <option value="{{$version}}" {{if $_message_supported->version=="$version"}}selected="selected"{{/if}}>
          {{tr}}{{$prefix_translate_version}}-versions.{{$version}}{{/tr}}
        </option>
      {{/if}}
    {{/foreach}}
  </select>
{{else}}
  {{assign var=version value=$category_versions|@first}}
  <input type="hidden" name="version" value="{{$version}}">
  <span>[{{tr}}{{$prefix_translate_version}}-versions.{{$version}}{{/tr}}]</span>
{{/if}}
