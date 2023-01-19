{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=limit_size value=100}}

{{if $exchange->_message === null && $exchange->_acquittement === null}}
  <div class="small-info">{{tr}}{{$exchange->_class}}-purge-desc{{/tr}}</div>
  {{mb_return}}
{{/if}}

<tr>
  <td>
    <script>
      Main.add(Control.Tabs.create.curry('tabs-contenu', true));
    </script>

    <ul id="tabs-contenu" class="control_tabs">
      <li>
        <a href="#msg-message">
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

    <div id="msg-message" style="display: none;">
      {{assign var=key value="input"}}
      <script>
        Main.add(function(){
          Control.Tabs.create("message-tab-{{$key}}", true);
          var tree = new TreeView("message-{{$key}}-tree");
          tree.collapseAll();
        });
      </script>
      <div {{if $key != "input"}} style="display: none;" {{/if}} id="$msg_segment_group-{{$key}}">
        <h1>{{$msg_segment_group->description}} ({{$msg_segment_group->version}}) <span class="type">{{$msg_segment_group->name}}</span> [{{$msg_segment_group->getEncoding()}}]</h1>

        <ul class="control_tabs" id="message-tab-{{$key}}">
          <li><a href="#message-{{$key}}-tree">Arbre</a></li>
          <li><a href="#message-{{$key}}-hpr-input">HPR Input</a></li>
          <li><a href="#message-{{$key}}-hpr-output">HPR Output</a></li>
          <li><a href="#message-{{$key}}-xml">XML</a></li>
          <li><a href="#message-{{$key}}-warnings" class="{{if $exchange->_doc_warnings_msg}}wrong{{else}}empty{{/if}}">Avertissements</a></li>
          <li><a href="#message-{{$key}}-errors" class="{{if $exchange->_doc_errors_msg}}wrong{{else}}empty{{/if}}">Erreurs</a></li>
        </ul>

        <div id="message-{{$key}}-tree" style="display: none;">
          {{if $msg_segment_group->children|@count > $limit_size}}
            <div class="small-info">Message trop volumineux pour être affiché</div>
          {{else}}
            <ul class="hl7-tree">
              {{mb_include module=hprim21 template=inc_segment_group_children segment_group=$msg_segment_group}}
            </ul>
          {{/if}}
        </div>

        <div id="message-{{$key}}-hpr-input" style="display: none;">
          {{$msg_segment_group->data|highlight:er7}}
        </div>

        <div id="message-{{$key}}-hpr-output" style="display: none;">
          {{if $msg_segment_group->children|@count > $limit_size}}
            <div class="small-info">Message trop volumineux pour être affiché (voir volet "Input")</div>
          {{else}}
            {{$msg_segment_group->flatten(true)|smarty:nodefaults}}
          {{/if}}
        </div>

        <div id="message-{{$key}}-xml" style="display: none; height: 400px;" class="highlight-fill">
          {{$msg_segment_group->_xml|highlight:xml}}
        </div>

        <div id="message-{{$key}}-warnings" style="display: none;">
          {{mb_include module=hprim21 template=inc_hprim_errors errors=$msg_segment_group->errors level=1}}
        </div>

        <div id="message-{{$key}}-errors" style="display: none;">
          {{mb_include module=hprim21 template=inc_hprim_errors errors=$msg_segment_group->errors level=2}}
        </div>
      </div>
    </div>

    <div id="ack" style="display: none;">
      {{if $ack_segment_group}}
      <script>
        Main.add(function() {
          Control.Tabs.create("ack-message-tab");
          var tree = new TreeView("ack-message-tree");
          tree.collapseAll();
        });
      </script>

      <h1>{{$ack_segment_group->description}} ({{$ack_segment_group->version}}) <span class="type">{{$ack_segment_group->name}}</span></h1>

      <ul class="control_tabs" id="ack-message-tab">
        <li><a href="#ack-message-tree">Arbre</a></li>
        <li><a href="#ack-message-hpr-input">HPR Input</a></li>
        <li><a href="#ack-message-hpr-output">HPR Output</a></li>
        <li><a href="#ack-message-xml">XML</a></li>
        <li><a href="#ack-message-warnings" class="{{if $exchange->_doc_warnings_ack}}wrong{{else}}empty{{/if}}">Avertissements</a></li>
        <li><a href="#ack-message-errors" class="{{if $exchange->_doc_errors_ack}}wrong{{else}}empty{{/if}}">Erreurs</a></li>
      </ul>

      <ul id="ack-message-tree" style="display: none;">
        {{if $ack_segment_group->children|@count > $limit_size}}
          <div class="small-info">Message trop volumineux pour être affiché</div>
        {{else}}
          <ul class="hl7-tree">
            {{mb_include module=hprim21 template=inc_segment_group_children segment_group=$ack_segment_group}}
          </ul>
        {{/if}}
      </ul>

      <div id="ack-message-hpr-input" style="display: none;">
        {{$ack_segment_group->data|highlight:er7}}
      </div>

      <div id="ack-message-hpr-output" style="display: none;">
        {{if $ack_segment_group->children|@count > $limit_size}}
          <div class="small-info">Message trop volumineux pour être affiché (voir volet "Input")</div>
        {{else}}
          {{$ack_segment_group->flatten(true)|smarty:nodefaults}}
        {{/if}}
      </div>

      <div id="ack-message-xml" style="display: none;">
        {{if $ack_segment_group->_xml}}
          {{$ack_segment_group->_xml|highlight:xml}}
        {{/if}}
      </div>

      <div id="ack-message-warnings" style="display: none;">
        {{mb_include module=hprim21 template=inc_hprim_errors errors=$ack_segment_group->errors level=1}}
      </div>

      <div id="ack-message-errors" style="display: none;">
        {{mb_include module=hprim21 template=inc_hprim_errors errors=$ack_segment_group->errors level=2}}
      </div>
      {{else}}
        <div class="big-info">{{tr}}CExchange-no-acquittement{{/tr}}</div>
      {{/if}}
    </div>
  </td>
</tr>