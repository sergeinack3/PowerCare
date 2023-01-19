{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function(){
    Control.Tabs.create('tabs-configs-formats', true).activeLink.up().onmousedown();
  });
</script>

<ul id="tabs-configs-formats" class="control_tabs small">
  {{foreach from=$formats_tabular item=_format_tabular}}
    {{assign var=configs value=$_format_tabular->_configs_format}}

    {{if $configs}}
      <li onmousedown="InteropActor.viewConfigsFormat('{{$actor_guid}}', '{{$configs->_guid}}');">
        <a {{if !$configs->_id}}class="wrong"{{/if}} href="#format_{{$configs->_guid}}">
          {{tr}}{{$configs->_class}}{{/tr}}
        </a>
      </li>
    {{/if}}
  {{/foreach}}

  {{foreach from=$formats_xml item=_format_xml}}
    {{assign var=configs value=$_format_xml->_configs_format}}

    {{if $configs}}
      <li onmousedown="InteropActor.viewConfigsFormat('{{$actor_guid}}', '{{$configs->_guid}}');">
        <a {{if !$configs->_id}}class="wrong"{{/if}} href="#format_{{$configs->_guid}}">
          {{tr}}{{$configs->_class}}{{/tr}}
        </a>
      </li>
    {{/if}}
  {{/foreach}}

  {{foreach from=$formats_binary item=_format_binary}}
    {{assign var=configs value=$_format_binary->_configs_format}}

    {{if $configs}}
      <li onmousedown="InteropActor.viewConfigsFormat('{{$actor_guid}}', '{{$configs->_guid}}');">
        <a {{if !$configs->_id}}class="wrong"{{/if}} href="#format_{{$configs->_guid}}">
          {{tr}}{{$configs->_class}}{{/tr}}
        </a>
      </li>
    {{/if}}
  {{/foreach}}
</ul>

{{foreach from=$formats_xml item=_format_xml}}
  {{assign var=configs value=$_format_xml->_configs_format}}

  {{if $configs}}
    <div id="format_{{$configs->_guid}}" style="display: none;"></div>
  {{/if}}
{{/foreach}}

{{foreach from=$formats_tabular item=_format_tabular}}
  {{assign var=configs value=$_format_tabular->_configs_format}}

  {{if $configs}}
    <div id="format_{{$configs->_guid}}" style="display: none;"></div>
  {{/if}}
{{/foreach}}

{{foreach from=$formats_binary item=_format_binary}}
  {{assign var=configs value=$_format_binary->_configs_format}}

  {{if $configs}}
    <div id="format_{{$configs->_guid}}" style="display: none;"></div>
  {{/if}}
{{/foreach}}
</td>
