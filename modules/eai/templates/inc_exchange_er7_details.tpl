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

{{mb_default var=msg_segment_group_altered value=null}}
<tr>
  <td>
    <script>
      Main.add(function () {
          Control.Tabs.create("tabs-actions", true, {afterChange: function(newContainer) {
                  switch(newContainer.id) {
                      case "tools-show-file" :
                          ExchangeDataFormat.showFileHL7('{{$exchange->_guid}}');
                  }
              }});
        Control.Tabs.create('tabs-contenu', true);
      });
    </script>

    <ul id="tabs-contenu" class="control_tabs">
      <li> 
        <a href="#msg-message"> 
          {{mb_title object=$exchange field="_message"}} 
          <button class="modify notext compact" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&message=1')"></button>
        </a> 
      </li>
      <li> 
        <a href="#ack" {{if !$exchange->_acquittement}}class="empty"{{/if}}>
          {{mb_title object=$exchange field="_acquittement"}}
          <button class="modify notext compact" onclick="window.open('?m=eai&a=download_exchange&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&ack=1')"></button>
          </a> 
      </li>
    </ul>

    <div id="msg-message" style="display: none;">
      <script>
        Main.add(function() {
          Control.Tabs.create("msg-message-tab", true, {afterChange: function(newContainer) {
            switch(newContainer.id) {
              case "msg-message-fast-queries" :
                getForm("form-fast-queries").onsubmit();
                break;
            }
          }});

          var tree = new TreeView("msg-message-tree");
          tree.collapseAll();
        });
      </script>

      <h1>{{$msg_segment_group->description}} ({{$msg_segment_group->version}} {{if $msg_segment_group->extension}}{{$msg_segment_group->extension}}{{/if}}) <span class="type">{{$msg_segment_group->name}}</span></h1>
      
      <ul class="control_tabs" id="msg-message-tab">
        <li><a href="#msg-message-er7-parsed">{{if $exchange->sender_id}}Message reçu{{else}}Message généré{{/if}}</a></li>
        {{if $msg_segment_group_altered}}<li><a href="#msg-altered-content-id">Message altéré</a></li>{{/if}}
        <li><a href="#msg-message-warnings" class="{{if $exchange->_doc_warnings_msg}}wrong{{else}}empty{{/if}}">Avertissements</a></li>
        <li><a href="#msg-message-errors" class="{{if $exchange->_doc_errors_msg}}wrong{{else}}empty{{/if}}">Erreurs</a></li>
        <li><a href="#msg-message-fast-queries">Requêtes rapides</a></li>
        <li><a href="#message-tools">Outils</a></li>
      </ul>

      <div id="msg-message-er7-parsed" style="display: none;">
        {{$msg_segment_group->flatten(true)|smarty:nodefaults}}
      </div>

        {{if $msg_segment_group_altered}}
            <div id="msg-altered-content-id">
                {{$msg_segment_group_altered->flatten(true)|smarty:nodefaults}}
            </div>
        {{/if}}
      
      <div id="msg-message-warnings" style="display: none;">
        {{mb_include module=hl7 template=inc_hl7v2_errors errors=$msg_segment_group->errors level=1}}
      </div>
      
      <div id="msg-message-errors" style="display: none;">
        {{mb_include module=hl7 template=inc_hl7v2_errors errors=$msg_segment_group->errors level=2}}
      </div>
      
      <div id="msg-message-fast-queries" style="display: none;">
        <form name="form-fast-queries" action="?m=hl7&a=ajax_show_fast_queries" onsubmit="return Url.update(this, 'er7-fast-queries')" method="post" class="prepared">
          <input type="hidden" name="m" value="hl7" />
          <input type="hidden" name="a" value="fast-queries" />
          <input type="hidden" name="suppressHeaders" value="1" />
          <input type="hidden" name="ajax" value="1" />
          <input type="hidden" name="exchange_id" value="{{$exchange->_id}}" />
          <textarea name="er7" style="display: none;">{{$msg_segment_group->data}}</textarea>
        </form>
        <div id="er7-fast-queries"></div>
      </div>

      <div id="message-tools" style="display : none;">
          <table style="table-layout: fixed; width : 100%;">
              <tr>
                  <td style="vertical-align: top; width: 100px;">
                      <ul id="tabs-actions" class="control_tabs_vertical small">
                          <li><a href="#tools-tree">Arbre</a></li>
                          <li><a href="#tools-input">ER7 Input</a></li>
                          <li><a href="#tools-xml">XML</a></li>
                          <li><a href="#tools-query">Requêteur</a></li>
                          <li><a href="#tools-show-file">Vue du fichier HL7</a></li>
                      </ul>
                  </td>
                  <td style="vertical-align: top; width: 100%;">
                      <div id="tools-tree" style="display: none;">
                          <ul id="msg-message-tree" class="hl7-tree"
                              {{mb_include module=hl7 template=inc_segment_group_children segment_group=$msg_segment_group}}
                          </ul>
                      </div>
                      <div id="tools-input" style="display: none">
                          <div id="msg-message-er7-input-view" >
                              {{$msg_segment_group->data|highlight:er7}}
                              <button type="button" class="edit" onclick="$('msg-message-er7-input-view').toggle(); $('msg-message-er7-edit').toggle();">{{tr}}Edit{{/tr}}</button>
                          </div>

                          <div id="msg-message-er7-edit" style="display: none;">
                              <form name="edit-er7" method="post" onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); ExchangeDataFormat.viewExchange('{{$exchange->_guid}}'); })">
                                  <input type="hidden" name="m" value="eai" />
                                  <input type="hidden" name="accept_utf8" value="1" />
                                  <input type="hidden" name="dosql" value="do_exchange_content_edit" />
                                  <input type="hidden" name="exchange_guid" value="{{$exchange->_guid}}" />
                                  <textarea name="_message" rows="15" style="white-space: pre; word-wrap: normal; font-family: 'lucida console', 'courier new', courier, monospace; font-size: 10px; line-height: 1.3; overflow-x: auto; resize: vertical;">{{$msg_segment_group->data}}</textarea>

                                  {{*
                                  <label><input type="radio" name="segment_terminator" value="CR"   {{if strpos($msg_segment_group->data,"\r")}} checked {{/if}} /> CR (\r)</label>
                                  <label><input type="radio" name="segment_terminator" value="LF"   {{if strpos($msg_segment_group->data,"\n")}} checked {{/if}} /> LF (\n)</label>
                                  <label><input type="radio" name="segment_terminator" value="CRLF" {{if strpos($msg_segment_group->data,"\r\n")}} checked {{/if}} /> CRLF (\r\n)</label>
                                  *}}
                                  <input type="hidden" name="segment_terminator" value="CR" />

                                  <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
                                  <button type="button" class="cancel" onclick="$('msg-message-er7-input-view').toggle(); $('msg-message-er7-edit').toggle();">{{tr}}Cancel{{/tr}}</button>
                              </form>
                          </div>
                      </div>
                      <div id="tools-xml" style="display: none; min-height: 300px;" class="highlight-fill">
                          {{if $msg_segment_group->_xml}}
                              {{$msg_segment_group->_xml|highlight:xml}}
                          {{/if}}
                      </div>

                      <div id="tools-query" style="display: none; text-align: left;">
                          <form name="er7-xml-document-form" action="?m=hl7&a=ajax_get_er7_xml" method="post" class="prepared">
                              <input type="hidden" name="m" value="hl7" />
                              <input type="hidden" name="a" value="ajax_get_er7_xml" />
                              <input type="hidden" name="suppressHeaders" value="1" />
                              <input type="hidden" name="ajax" value="1" />
                              <textarea name="er7" style="display: none;">{{$msg_segment_group->data}}</textarea>

                              <input type="text" name="query" value="" size="50" onkeyup="return Url.update(this.form, 'er7-query-result')" placeholder="Requête XPath (// implicite, sensible à la casse)" />
                          </form>
                          <div id="er7-query-result"></div>
                      </div>

                      <div id="tools-show-file" style="display: none"></div>

                  </td>
              </tr>
          </table>

      </div>
    </div>

    <div id="ack" style="display: none;">
      {{if $ack_segment_group}}
      <script>
        Main.add(function() {
          Control.Tabs.create("ack-message-tab");
          var tree = new TreeView("ack-message-tree");
          tree.collapseAll();

            Control.Tabs.create('tabs-actions-ack', true);
        });
      </script>

      <h1>{{$ack_segment_group->description}} ({{$ack_segment_group->version}} {{if $ack_segment_group->extension}}{{$ack_segment_group->extension}}{{/if}}) <span class="type">{{$ack_segment_group->name}}</span></h1>

      <ul class="control_tabs" id="ack-message-tab">
        <li><a href="#ack-message-er7-parsed">{{if $exchange->sender_id}}ACK généré{{else}}ACK reçu{{/if}}</a></li>
        <li><a href="#ack-message-warnings" class="{{if $exchange->_doc_warnings_ack}}wrong{{else}}empty{{/if}}">Avertissements</a></li>
        <li><a href="#ack-message-errors" class="{{if $exchange->_doc_errors_ack}}wrong{{else}}empty{{/if}}">Erreurs</a></li>
        <li><a href="#ack-message-tools">Outils</a></li>
      </ul>

      <div id="ack-message-er7-parsed" style="display: none;">
        {{$ack_segment_group->flatten(true)|smarty:nodefaults}}
      </div>

      <div id="ack-message-warnings" style="display: none;">
        {{mb_include module=hl7 template=inc_hl7v2_errors errors=$ack_segment_group->errors level=1}}
      </div>

      <div id="ack-message-errors" style="display: none;">
        {{mb_include module=hl7 template=inc_hl7v2_errors errors=$ack_segment_group->errors level=2}}
      </div>

      <div id="ack-message-tools" style="display: none;">
          <table style="table-layout: fixed; width : 100%;">
              <tr>
                  <td style="vertical-align: top; width: 100px;">
                      <ul id="tabs-actions-ack" class="control_tabs_vertical small">
                          <li><a href="#ack-tools-tree">Arbre</a></li>
                          <li><a href="#ack-tools-input">ER7 Input</a></li>
                          <li><a href="#ack-tools-xml">XML</a></li>
                      </ul>
                  </td>
                  <td style="vertical-align: top; width: 100%;">
                      <div id="ack-tools-tree" style="display: none;">
                          <ul id="ack-message-tree" class="hl7-tree">
                              {{mb_include module=hl7 template=inc_segment_group_children segment_group=$ack_segment_group}}
                          </ul>
                      </div>
                      <div id="ack-tools-input" style="display: none">
                          <div id="ack-message-er7-input-view">
                              {{$ack_segment_group->data|highlight:er7}}
                              <button type="button" class="edit" onclick="$('msg-message-er7-input-view').toggle(); $('msg-message-er7-edit').toggle();">{{tr}}Edit{{/tr}}</button>
                          </div>

                          <div id="ack-message-er7-edit" style="display: none;">
                              <form name="edit-er7" method="post" onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); ExchangeDataFormat.viewExchange('{{$exchange->_guid}}'); })">
                                  <input type="hidden" name="m" value="eai" />
                                  <input type="hidden" name="accept_utf8" value="1" />
                                  <input type="hidden" name="dosql" value="do_exchange_content_edit" />
                                  <input type="hidden" name="exchange_guid" value="{{$exchange->_guid}}" />
                                  <textarea name="_acquittement" rows="15" style="white-space: pre; word-wrap: normal; font-family: 'lucida console', 'courier new', courier, monospace; font-size: 10px; line-height: 1.3; overflow-x: auto; resize: vertical;">{{$ack_segment_group->data}}</textarea>

                                  {{*
                                  <label><input type="radio" name="segment_terminator" value="CR"   {{if strpos($msg_segment_group->data,"\r")}} checked {{/if}} /> CR (\r)</label>
                                  <label><input type="radio" name="segment_terminator" value="LF"   {{if strpos($msg_segment_group->data,"\n")}} checked {{/if}} /> LF (\n)</label>
                                  <label><input type="radio" name="segment_terminator" value="CRLF" {{if strpos($msg_segment_group->data,"\r\n")}} checked {{/if}} /> CRLF (\r\n)</label>
                                  *}}
                                  <input type="hidden" name="segment_terminator" value="CR" />

                                  <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
                                  <button type="button" class="cancel" onclick="$('ack-message-er7-input-view').toggle(); $('ack-message-er7-edit').toggle();">{{tr}}Cancel{{/tr}}</button>
                              </form>
                          </div>
                      </div>
                      <div id="ack-tools-xml" style="display: none;">
                          {{if $ack_segment_group->_xml}}
                              {{$ack_segment_group->_xml|highlight:xml}}
                          {{/if}}
                      </div>
                  </td>
              </tr>
          </table>
      </div>
      {{else}}
      <div class="big-info">{{tr}}CExchange-no-acquittement{{/tr}}</div>
      {{/if}}
    </div>
  </td>
</tr>
