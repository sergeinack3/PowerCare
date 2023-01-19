{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function() {
    EAITransformationRule.actionSelect('{{$transf_rule->action_type}}');
  });
  submitFormRule = function () {
    var paramsRow     = document.getElementById("paramsRow");
    if(!paramsRow.hidden && EAITransformationRule.serializeParams() === false) {
      return false;
    }
    else if((!paramsRow.hidden && EAITransformationRule.serializeParams() === true)||paramsRow.hidden) {
      var form = getForm('editEAITransformationRule');
      form.elements.xpath_source.disabled = '';
      return onSubmitFormAjax(form, {
        onComplete: function () {
          Control.Modal.close();
          EAITransformationRuleSequence.displayDetails('{{$transformation_ruleset_id}}','{{$transf_rule_sequence_id}}');
        }
      });
    }
  };
</script>
<form name="editEAITransformationRule" action="?m={{$m}}" method="post" onsubmit="return submitFormRule();">
  {{mb_key object=$transf_rule}}
  {{mb_class object=$transf_rule}}
  <input type="hidden" name="del" value="0" />

  <input type="hidden" name="transformation_rule_sequence_id" value="{{$transf_rule_sequence_id}}" />

  {{mb_field object=$transf_rule field="xpath_target" hidden="1"}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$transf_rule}}

    <tr>
      <th style="width: 45%">{{mb_label object=$transf_rule field="name"}}</th>
      <td>{{mb_field object=$transf_rule field="name"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$transf_rule field="action_type"}}</th>
      <td>
        {{mb_field object=$transf_rule field="action_type" emptyLabel=Select
          onchange='EAITransformationRule.emptyParamsValue();EAITransformationRule.actionSelect(this.value);'}}
      </td>
    </tr>
    <tr id="paramsRow" {{if !in_array($transf_rule->action_type, $action_params)}}hidden{{/if}}>
      <th>{{mb_label object=$transf_rule field="params"}}</th>
      <td>
        {{mb_field object=$transf_rule field="params" hidden="1"}}
        <span id="paramsSerialize" {{if $transf_rule->params}}class="circled"{{/if}}>
          {{mb_value object=$transf_rule field="params"}}
        </span>
      </td>
    </tr>
    <tbody id="paramsEdit"></tbody>
    <tr>
      <th>{{mb_label object=$transf_rule field="xpath_source"}}</th>
      <td>
        <div class="small-info">{{tr}}CTransformationRule-msg-Xpath source explication{{/tr}}</div>
        <br/>
        <button type="button" onclick="EAITransformationRule.target({{$transf_rule_sequence_id}}, 'xpath_source')"
                class="target notext">
          {{tr}}Target{{/tr}}
        </button>
        <span id="EAITransformationRule-xpath_source">
          {{if $transf_rule->xpath_source}}
            {{foreach from="|"|explode:$transf_rule->xpath_source item=_component}}
              <span id="xpath_source_{{$_component}}" class="circled"
                    onclick="EAITransformationRule.deleteTarget('{{$_component}}','xpath_source');">{{$_component}}
              </span>
            {{/foreach}}
          {{/if}}
          <br/>
          {{mb_field object=$transf_rule field="xpath_source" disabled='disabled'}}

          <i class="fas fa-lock" onclick="EAITransformationRule.toggleDisabled('xpath_source');"></i>
        </span>
      </td>
    </tr>

    <tr id="xPathTargetRow">
      <th>{{mb_label object=$transf_rule field="xpath_target"}}</th>
      <td>
        <div class="small-info">
          {{tr}}CTransformationRule-msg-Xpath target explication{{/tr}}
        </div>
        <br/>
        <button type="button" class="target notext"
                onclick="EAITransformationRule.target({{$transf_rule_sequence_id}}, 'xpath_target')">
          {{tr}}Target{{/tr}}
        </button>
        <span id="EAITransformationRule-xpath_target">
          {{if $transf_rule->xpath_target}}
            {{foreach from="|"|explode:$transf_rule->xpath_target item=_component}}
              <span id="xpath_target_{{$_component}}" class="circled"
                    onclick="EAITransformationRule.deleteTarget('{{$_component}}','xpath_target');">
                {{$_component}}
              </span>
            {{/foreach}}
          {{/if}}
        </span>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$transf_rule field="active"}}</th>
      <td>{{mb_field object=$transf_rule field="active"}}</td>
    </tr>
    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$transf_rule options="{typeName: '', objName: '`$transf_rule`'}"
        options_ajax="function(){
          EAITransformationRuleSequence.displayDetails('$transformation_ruleset_id','$transf_rule_sequence_id');
          Control.Modal.close();}"}}
    </tr>
  </table>
</form>
