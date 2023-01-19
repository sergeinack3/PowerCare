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
      <button class="modify notext" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&message=1')"></button>
    </a>
  </li>
  <li>
    <a href="#ack" {{if !$exchange->_acquittement}}class="empty"{{/if}}>
      {{mb_title object=$exchange field="_acquittement"}}
      <button class="modify notext" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&ack=1')"></button>
    </a>
  </li>
</ul>

<div id="message" style="display: none;">
  {{if $exchange->_specs._message == "str"}}
    <code>{{$exchange->_message}}</code>
  {{else}}
    <code>{{mb_value object=$exchange field="_message" advanced=true}}</code>
  {{/if}}

  {{if $exchange->message_valide != 1 && count($exchange->_doc_errors_msg) > 0}}
  <div class="big-error">
    <strong>{{tr}}CExchange-message-invalide{{/tr}}</strong> <br />
    <ul>
    {{foreach from=$exchange->_doc_errors_msg item=_error}}
      <li>{{$_error}}</li>
     {{/foreach}}
    </ul>
  </div>
  {{/if}}
</div>

<div id="ack" style="display: none;">
  {{if $exchange->message_valide == 1 || $exchange->acquittement_valide == 1}}
    {{if $exchange->_acquittement}}
      {{if $exchange->_specs._acquittement == "str"}}
        <pre>{{$exchange->_acquittement}}</pre>
      {{else}}
        {{mb_value object=$exchange field="_acquittement" advanced=true}}
      {{/if}}
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
      <div class="small-info">{{tr}}CExchange-no-acquittement{{/tr}}</div>
    {{/if}}
  {{/if}}
</div>