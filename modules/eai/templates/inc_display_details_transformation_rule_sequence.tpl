{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {EAITransformationRuleSequence.play('{{$transf_rule_sequence->_id}}');});
</script>

<table class="main tbl">
  {{if $transf_rule_sequence->_id !== null}}
    <tr>
      <th colspan="5" class="title">{{mb_value object=$transf_rule_sequence field="name"}}</th>
      <th class="title narrow">
        <button class="button edit notext compact"
                onclick="EAITransformationRuleSequence.edit('{{$transformation_ruleset_id}}','{{$transf_rule_sequence->_id}}');">
          {{tr}}CTransformationRuleSequence-edit{{/tr}}
        </button>
      </th>
    </tr>
    <tr>
      <td colspan="6">{{mb_value object=$transf_rule_sequence field="description"}}</td>
    </tr>
    <tr>
      <table class="main tbl">
        <tr>
          <th colspan="6" class="section">{{tr}}mod-eai-tab-transformation-rule_sequence_filters{{/tr}}</th>
        </tr>
        <tr>
          <th>{{mb_label object=$transf_rule_sequence field="standard"}}</th>
          <th>{{mb_label object=$transf_rule_sequence field="domain"}}</th>
          <th>{{mb_label object=$transf_rule_sequence field="profil"}}</th>
          <th>{{mb_label object=$transf_rule_sequence field="transaction"}}</th>
          <th>{{mb_label object=$transf_rule_sequence field="message_type"}}</th>
        </tr>
        <tr>
          <td>{{mb_value object=$transf_rule_sequence field="standard"}}</td>
          <td>{{mb_value object=$transf_rule_sequence field="domain"}}</td>
          <td>{{mb_value object=$transf_rule_sequence field="profil"}}</td>
          <td>{{mb_value object=$transf_rule_sequence field="transaction"}}</td>
          <td>{{tr}}{{mb_value object=$transf_rule_sequence field="message_type"}}{{/tr}}</td>
        </tr>
        <tr>
          <th colspan="5">
            {{mb_label object=$transf_rule_sequence field="message_example"}}
              <button type="button" class="fas fa-sync"
                  onclick="EAITransformationRuleSequence.displayDetails('{{$transformation_ruleset_id}}','{{$transf_rule_sequence->_id}}', 'HL7');">Affichage HL7
              </button>
              <button type="button" class="fas fa-sync"
                  onclick="EAITransformationRuleSequence.displayDetails('{{$transformation_ruleset_id}}','{{$transf_rule_sequence->_id}}', 'XML');">Affichage XML
              </button>
          </th>
        </tr>
        <tr>
          <td colspan="5" style="max-width: 500px;">
            {{if $transf_rule_sequence->_message|instanceof:'Ox\Interop\Hl7\CHL7v2Message'}}
                {{if $display_type === "XML" && $xml}}
                    <pre>{{$xml}}</pre>
                {{else}}
                    {{$transf_rule_sequence->_message->flatten(true)|smarty:nodefaults}}
                {{/if}}
            {{else}}
              {{mb_value object=$transf_rule_sequence field="message_example"}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th colspan="5">
            {{tr}}CTransformationRuleSequence-msg-Result transformation|pl{{/tr}}
            <button type="button" class="fas fa-play-circle notext" title="{{tr}}CTransformationRule-action-Apply{{/tr}}"
                    onclick="EAITransformationRuleSequence.play('{{$transf_rule_sequence->_id}}')"></button>
          </th>
        </tr>
        <tr>
          <td colspan="5" id="result_transformations_sequence" style="max-width: 500px;"></td>
        </tr>
      </table>
    </tr>
    <tr>
      <table class="main tbl" style="margin-top: 30px;">
        <tr>
          <th colspan="5" class="section">{{tr}}CTransformationRule-pl{{/tr}}</th>
          <th class="section" style="text-align: right">
            <button onclick="EAITransformationRule.edit(null,{{$transf_rule_sequence->_id}});" class="button new notext">
              {{tr}}CTransformationRule-title-create{{/tr}}
            </button>
          </th>
        </tr>
        <tr>
          <th class="narrow button"></th>
          <th class="category narrow">{{mb_title class=CTransformationRule field=rank}}</th>
          <th class="category">{{mb_title class=CTransformationRule field=name}}</th>
          <th class="category">{{tr}}CTransformationRule-msg-Resume{{/tr}}</th>
          <th class="category">{{mb_title class=CTransformationRule field=params}}</th>
          <th class="category">{{tr}}CTransformationRule-msg-Result{{/tr}}</th>
        </tr>

        {{foreach from=$transf_rule_sequence->_ref_transformation_rules item=_transformation_rule}}
          <tr {{if !$_transformation_rule->active}}class="opacity-30"{{/if}}>
            <td>
              <button class="button edit notext compact" title="{{tr}}Edit{{/tr}}"
                      onclick="EAITransformationRule.edit({{$_transformation_rule->_id}},{{$transf_rule_sequence->_id}});">
                {{tr}}Edit{{/tr}}
              </button>
              <button type="button" class="fas fa-play-circle notext" title="{{tr}}CTransformationRule-action-Apply{{/tr}}"
              onclick="EAITransformationRule.apply('{{$_transformation_rule->_id}}')"></button>
            </td>
            <td class="text compact">{{mb_value object=$_transformation_rule field="rank"}}
              <!-- Order -->
              <form name="formOrderRule-{{$_transformation_rule->_id}}" method="post"
                    onsubmit="return onSubmitFormAjax(this, EAITransformationRuleSequence.displayDetails.curry(
                      '{{$transformation_ruleset_id}}','{{$transf_rule_sequence->_id}}'));">

                <input type="hidden" name="dosql" value="do_manage_transformation_rule"/>
                <input type="hidden" name="m" value="eai"/>
                <input type="hidden" name="ajax" value="1"/>
                <input type="hidden" name="transformation_rule_id_move" value="{{$_transformation_rule->_id}}"/>
                <input type="hidden" name="direction" value=""/>

                <img src="./images/icons/updown.gif" usemap="#map-{{$_transformation_rule->_id}}"/>
                <map name="map-{{$_transformation_rule->_id}}">
                  <area coords="0,0,10,7" href="#1" onclick="$V(this.up('form').direction, 'up');
                    EAITransformationRule.moveRowUp(this.up('tr'));   this.up('form').onsubmit();"/>
                  <area coords="0,8,10,14" href="#1" onclick="$V(this.up('form').direction, 'down');
                    EAITransformationRule.moveRowDown(this.up('tr')); this.up('form').onsubmit();"/>
                </map>
              </form>
            </td>
            <td class="text compact">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_transformation_rule->_guid}}');">
                {{mb_value object=$_transformation_rule field="name"}}
              </span>
            </td>
            <td class="text compact">
              {{mb_title class=CTransformationRule field=action_type}} : {{mb_value object=$_transformation_rule field="action_type"}}
              <br/>
              {{mb_title class=CTransformationRule field=xpath_source}} :
              {{foreach from="|"|explode:$_transformation_rule->xpath_source item=_component}}
                <span class="circled">{{$_component}}</span>
              {{/foreach}}
              <br/>
              {{mb_title class=CTransformationRule field=xpath_target}} :
              {{foreach from="|"|explode:$_transformation_rule->xpath_target item=_component}}
                <span class="circled">{{$_component}}</span>
              {{/foreach}}
            </td>
            <td class="text compact">
              {{if $_transformation_rule->params}}
                <span class="circled">{{$_transformation_rule->params}}</span>
              {{/if}}
            </td>
            <td class="text compact" style="max-width: 500px;" id="rule_{{$_transformation_rule->_id}}"></td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty" colspan="16">{{tr}}CTransformationRule.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </tr>
  {{else}}
    <tr>
      <td colspan="2">
        <div class="small-info">{{tr}}mod-eai-tab-please_select_rule_sequence{{/tr}}</div>
      </td>
    </tr>
  {{/if}}
</table>
