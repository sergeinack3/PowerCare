{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !empty($formats_tabular|smarty:nodefaults)}}
<fieldset>
  <legend>{{tr}}CExchangeTabular{{/tr}}</legend>
  <br />

  {{foreach from=$formats_tabular item=_format_tabular}}
    <button onclick="InteropActor.viewMessagesSupported('{{$actor_guid}}', '{{$_format_tabular->_class}}', true)">
      <img src="modules/{{$_format_tabular->_ref_module->mod_name}}/images/icon.png" width="16"/>
        {{tr}}{{$_format_tabular->_class}}{{/tr}}
    </button>
  {{/foreach}}
  
  {{if !empty($messages_tabular|smarty:nodefaults)}}
    {{foreach from=$messages_tabular key=_message_tabular item=_messages_tabular_supported}}
      {{mb_include template="inc_messages_available" message=$_message_tabular messages_supported=$_messages_tabular_supported}}
    {{/foreach}}
  {{else}}
    <div class="small-warning">{{tr}}CMessageSupported.none{{/tr}}</div>
  {{/if}}
</fieldset>
{{/if}}

{{if !empty($formats_xml|smarty:nodefaults)}}
<fieldset> 
  <legend>{{tr}}CEchangeXML{{/tr}}</legend>
   <br />

  {{foreach from=$formats_xml item=_format_xml}}
    <button onclick="InteropActor.viewMessagesSupported('{{$actor_guid}}', '{{$_format_xml->_class}}', true)">
      <img src="modules/{{$_format_xml->_ref_module->mod_name}}/images/icon.png" width="16"/>{{tr}}{{$_format_xml->_class}}{{/tr}}
    </button>
  {{/foreach}}
  
  {{if !empty($messages_xml|smarty:nodefaults)}}
    {{foreach from=$messages_xml key=_message_xml item=_messages_xml_supported}}
      {{mb_include template="inc_messages_available" message=$_message_xml messages_supported=$_messages_xml_supported}}
    {{/foreach}}
  {{else}}
    <div class="small-warning">{{tr}}CMessageSupported.none{{/tr}}</div>
  {{/if}}
</fieldset>
{{/if}}

{{if !empty($formats_binary|smarty:nodefaults)}}
<fieldset> 
  <legend>{{tr}}CExchangeBinary{{/tr}}</legend>
  <br />

  {{foreach from=$formats_binary item=_format_binary}}
    <button onclick="InteropActor.viewMessagesSupported('{{$actor_guid}}', '{{$_format_binary->_class}}', true)">
      <img src="modules/{{$_format_binary->_ref_module->mod_name}}/images/icon.png" width="16"/>
        {{tr}}{{$_format_binary->_class}}{{/tr}}
    </button>
  {{/foreach}}
  
  {{if !empty($messages_binary|smarty:nodefaults)}}
    {{foreach from=$messages_binary key=_message_binary item=_messages_binary_supported}}
      {{mb_include template="inc_messages_available" message=$_message_binary messages_supported=$_messages_binary_supported}}
    {{/foreach}}
  {{else}}
    <div class="small-warning">{{tr}}CMessageSupported.none{{/tr}}</div>
  {{/if}}
</fieldset>
{{/if}}