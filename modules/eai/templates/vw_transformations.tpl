{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=transformation_ruleset}}
{{mb_script module=eai script=transformation_rule_sequence}}
{{mb_script module=eai script=transformation_rule}}

<script>
  Main.add(function(){
    EAITransformationRuleSet.refreshList();
  });
</script>

<table class="main">
  <tr>
    <td style="width:250px">
      <table class="main tbl">
        <tr>
          <th class="title">
            {{tr}}CTransformationRuleSet-pl{{/tr}}
          </th>
          <th class="title narrow">
            <button onclick="EAITransformationRuleSet.edit();" class="button new notext">
              {{tr}}CTransformationRuleSet-title-create{{/tr}}
            </button>
          </th>
        </tr>
        <tbody id="list-transformation-ruleset">
          {{mb_include template=inc_list_transformation_ruleset}}
        </tbody>
      </table>
    </td>
    <td>
      <div id="transformation_rule_sequence_details">
          {{mb_include template=inc_display_details_transformation_rule_sequence}}
      </div>
    </td>
  </tr>
</table>
