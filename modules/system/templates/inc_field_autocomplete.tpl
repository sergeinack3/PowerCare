{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=options value=false}}

{{if $view_field == 1}}
    {{assign var=f value=$field}}
{{else}}
    {{assign var=f value=$view_field}}
{{/if}}

<ul style="text-align: left">
    {{foreach from=$matches item=match}}
        {{if !$options || !$options.no_show_elements || ($options.no_show_elements && !$match->_id|in_array:$options.no_show_elements)}}
    <li id="autocomplete-{{$match->_guid}}" data-id="{{$match->_id}}" data-guid="{{$match->_guid}}">
            {{if $template}}
                {{mb_include template=$template ignore_errors=true}}
            {{else}}
                {{mb_include module=system template=CMbObject_autocomplete nodebug=true}}
            {{/if}}
        {{/if}}
    </li>
    {{foreachelse}}
    <li>
        <span class="informal">
            {{if isset($ref_spec|smarty:nodefaults)}}
            <span class="view"></span>
            {{else}}
            <span class="view" style="display: none;">{{$input}}</span>
            {{/if}}
            <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
        </span>
    </li>
    {{/foreach}}
</ul>
