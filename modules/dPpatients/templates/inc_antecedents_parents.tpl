{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toggleAtcdParent = function (element, status, input_name) {
    $(element).select("input[name=" + input_name + "]").each(function (elt) {
      elt.checked = status ? "checked" : "";
    });
  };
</script>

{{assign var=first_parent_atcd value=$first_parent->_ref_dossier_medical->_all_antecedents}}
{{assign var=second_parent_atcd value=$second_parent->_ref_dossier_medical->_all_antecedents}}

<div class="small-warning">
  {{tr}}CPatientFamilyLink-msg-Warning, the selected antecedents of the parents will be added in the family antecedents of the patient folder{{/tr}}
</div>

<div>
  <table class="main tbl">
    <tr>
      <th class="title" colspan="8">{{tr var1=$patient->_view}}CAntecedent-Antecedents of the parents of %s{{/tr}}</th>
    </tr>

    <tr>
      <td class="halfPane" style="vertical-align: top; padding: 0;">
        <table id="antecedents_parent1" class="main tbl">
          <tr>
            <th class="section" colspan="4">
              {{tr}}CPatient-Parent 1{{/tr}}
              (<span onmouseover="ObjectTooltip.createEx(this, '{{$first_parent->_guid}}')">{{$first_parent->_view}}</span>)
            </th>
          </tr>

          <tr>
            <th class="category narrow">
              <input type="checkbox" onclick="toggleAtcdParent('antecedents_parent1', this.checked, 'antecedent_parent1');" />
            </th>
            <th class="category">{{tr}}CAntecedent-rques{{/tr}}</th>
            <th class="category">{{tr}}CAntecedent-date{{/tr}}</th>
            <th class="category">{{tr}}CAntecedent-comment{{/tr}}</th>
          </tr>

          {{foreach from=$first_parent_atcd item=_antecedent}}
            {{mb_include module="patients" template="inc_line_antecedent_parent" antecedent=$_antecedent name="antecedent_parent1"}}
            {{foreachelse}}
            <tr>
              <td colspan="4" class="empty">{{tr}}CAntecedent.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>

      <td class="halfPane" style="vertical-align: top; padding: 0;">
        <table id="antecedents_parent2" class="tbl">
          <tr>
            <th class="section" colspan="4">
              {{tr}}CPatient-Parent 2{{/tr}}
              (<span onmouseover="ObjectTooltip.createEx(this, '{{$second_parent->_guid}}')">{{$second_parent->_view}}</span>)
            </th>
          </tr>

          <tr>
            <th class="category narrow">
              <input type="checkbox" onclick="toggleAtcdParent('antecedents_parent2', this.checked, 'antecedent_parent2');" />
            </th>
            <th class="category">{{tr}}CAntecedent-rques{{/tr}}</th>
            <th class="category">{{tr}}CAntecedent-date{{/tr}}</th>
            <th class="category">{{tr}}CAntecedent-comment{{/tr}}</th>
          </tr>

          {{foreach from=$second_parent_atcd item=_antecedent}}
            {{mb_include module="patients" template="inc_line_antecedent_parent" antecedent=$_antecedent name="antecedent_parent2"}}
            {{foreachelse}}
            <tr>
              <td colspan="4" class="empty">{{tr}}CAntecedent.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="8">
        <button class="fas fa-share-square"
                title="{{tr}}CAntecedent-title-Copy the selected antecedents as a family antecedents of the patient{{/tr}}"
                onclick="Patient.sendAntecedentsParent('{{$context_class}}', '{{$context_id}}');">
          {{tr}}CAntecedent-action-Copy the antecedents into the patient record{{/tr}}
        </button>
        <button type="button" class="close" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
</div>
