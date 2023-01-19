{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$transf_rulesets item=_transformation_ruleset}}
  <tr>
    <td colspan="2">
      <p style="font-size: 15px;"><strong>{{mb_value object=$_transformation_ruleset field="name"}}</strong>
        <button class="button edit notext compact" onclick="EAITransformationRuleSet.edit('{{$_transformation_ruleset->_id}}');">
          {{tr}}CTransformationRuleSet-title-edit{{/tr}}
        </button>
      </p>
      <span class="text compact">{{$_transformation_ruleset->description}}</span>
      <br/>
      <br/>
      {{tr}}CTransformationRuleSequence{{/tr}}
      <button class="button new notext compact"
              onclick="EAITransformationRuleSequence.edit('{{$_transformation_ruleset->_id}}',null);">
        {{tr}}CTransformationRuleSequence-title-create{{/tr}}
      </button>

      {{if $_transformation_ruleset->_ref_transformation_rule_sequences}}
        <ul style="list-style: none">
          {{foreach from=$_transformation_ruleset->_ref_transformation_rule_sequences item=_transformation_rule_sequence}}
            <li>
              <a href="#" onclick="EAITransformationRuleSequence.displayDetails(
                '{{$_transformation_ruleset->_id}}','{{$_transformation_rule_sequence->_id}}');">
                {{mb_value object=$_transformation_rule_sequence field="name"}}
              </a>
            </li>
          {{/foreach}}
        </ul>
      {{else}}
        <div class="small-info">{{tr}}CTransformationRuleSequence.none{{/tr}}
        </div>
      {{/if}}
    </td>
    {{**<td class="narrow">
      <button class="button edit notext compact" onclick="EAITransformationRuleSet.edit('{{$_transformation_ruleset->_id}}');">
        {{tr}}CTransformationRuleSet-title-edit{{/tr}}
      </button>
    </td>**}}
  </tr>
  {{**<tr>
    <td colspan="2" class="text compact">
      {{mb_value object=$_transformation_ruleset field="description"}}
    </td>
  </tr>**}}
  {{**<tr>
    <td class="section">{{tr}}CTransformationRuleSequence{{/tr}}</td>
    <td class="section narrow">
      <button class="button new notext compact"
              onclick="EAITransformationRuleSequence.edit('{{$_transformation_ruleset->_id}}',null);">
        {{tr}}CTransformationRuleSequence-title-create{{/tr}}
      </button>
    </td>
  </tr>**}}

  {{**<tr>
      <td colspan="2" class="text compact">
          <ul style="list-style: none">
              {{foreach from=$_transformation_ruleset->_ref_transformation_rule_sequences item=_transformation_rule_sequence}}
                  <li>
                      <a href="#" onclick="EAITransformationRuleSequence.displayDetails(
                          '{{$_transformation_ruleset->_id}}','{{$_transformation_rule_sequence->_id}}');">
                          {{mb_value object=$_transformation_rule_sequence field="name"}}
                      </a>
                  </li>
              {{foreachelse}}
          </ul>
      </td>
    </tr>

    <tr>
      <td class="empty" colspan="2">{{tr}}CTransformationRuleSequence.none{{/tr}}</td>
    </tr>
  {{/foreach}}**}}
{{foreachelse}}
  <tr>
    <td class="empty">{{tr}}CTransformationRuleSet.none{{/tr}}</td>
  </tr>
{{/foreach}}
