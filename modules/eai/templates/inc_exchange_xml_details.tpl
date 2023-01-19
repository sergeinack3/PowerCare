{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $exchange->_message === null && $exchange->_acquittement === null}}
  <div class="small-info">{{tr}}{{$exchange->_class}}-purge-desc{{/tr}}</div>
  {{mb_return}}
{{/if}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-contenu', true));
</script>

<ul id="tabs-contenu" class="control_tabs">
  <li> 
    <a href="#message"> 
      {{mb_title object=$exchange field="_message"}} 
      <button class="save notext compact" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&message=1')"></button>
    </a> 
  </li>
  <li> 
    <a href="#ack" {{if !$exchange->_acquittement}}class="empty"{{/if}}>
      {{mb_title object=$exchange field="_acquittement"}}
      <button class="save notext compact" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&ack=1')"></button>
    </a> 
  </li>
</ul>

<div id="message" style="display: none;">
  {{if $exchange->message_valide != 1 || $exchange->_doc_errors_msg|@count > 0}}
  <div class="{{if $exchange->_doc_errors_msg}}big-error{{else}}small-error{{/if}}">
    <strong>{{tr}}CExchange-message-invalide{{/tr}}</strong> <br />
    {{if $exchange->_doc_errors_msg}}
    <ul>
      {{foreach from=$exchange->_doc_errors_msg item=_error}}
      <li>{{$_error}}</li>
      {{/foreach}}
    </ul>
    {{/if}}
  </div>
  {{/if}}

  <div id="msg-message-view">
    <div id="msg-message-view-content" style="height: 400px;" class="highlight-fill">
      {{mb_value object=$exchange field="_message" advanced=true}}
    </div>
    
    <button type="button" class="edit" onclick="$('msg-message-view').toggle(); $('msg-message-edit').toggle();">
      {{tr}}Edit{{/tr}}
    </button>
  </div>
  
  <div id="msg-message-edit" style="display: none;">
    <form name="edit-xml-message" method="post" onsubmit="return onSubmitFormAjax(this, function(){ Control.Modal.close(); ExchangeDataFormat.viewExchange('{{$exchange->_guid}}'); })">
      <input type="hidden" name="m" value="eai" />
      <input type="hidden" name="dosql" value="do_exchange_content_edit" />
      <input type="hidden" name="accept_utf8" value="1" />
      <input type="hidden" name="exchange_guid" value="{{$exchange->_guid}}" />
      <textarea name="_message" rows="20" style="white-space: pre; word-wrap: normal; font-family: 'lucida console', 'courier new', courier, monospace; font-size: 10px; line-height: 1.3; overflow-x: auto; resize: vertical;">{{$exchange->_message}}</textarea>
      <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
      <button type="button" class="cancel" onclick="$('msg-message-view').toggle(); $('msg-message-edit').toggle();">{{tr}}Cancel{{/tr}}</button>
    </form>
  </div>
</div>

<div id="ack" style="display: none;">
  {{if $exchange->message_valide == 1 || $exchange->acquittement_valide == 1}}
    {{if $exchange->_acquittement}}
      <div style="height: 400px;" class="highlight-fill">
        {{mb_value object=$exchange field="_acquittement" advanced=true}}
      </div>
      
      {{mb_include module=$exchange->_ref_module->mod_name template="`$exchange->_class`_observations_inc"}}
    {{else}}
      <div class="small-info">{{tr}}CExchange-no-acquittement{{/tr}}</div>
    {{/if}}
  {{else}}
    {{if count($exchange->_doc_errors_ack) > 0}}
      <div class="big-error">
        <strong>{{tr}}CExchange-acquittement-invalide{{/tr}}</strong> <br />
        <ul>
        {{foreach from=$exchange->_doc_errors_ack item=_error}}
          <li>{{$_error}}</li> 
         {{/foreach}} 
        </ul>
      </div>
    {{else}}
      <div class="small-warning">{{tr}}CExchange-no-acquittement-or-invalid{{/tr}}</div>
      {{mb_value object=$exchange field="_acquittement" advanced=true}}
    {{/if}}
  {{/if}}
</div>