{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=print value=false}}

<table class="{{if $print}}tbl print_sejour{{else}}main form{{/if}} me-no-align me-no-box-shadow" style="margin-top: 10px;">
  <tr>
    <th class="{{if $print}}title{{else}}category{{/if}}" colspan="5">{{tr}}CRPUReevalPEC-Reevaluation of the management{{/tr}} ({{$rpu->_count_rpu_reevaluations_pec}})</th>
  </tr>
  <tr>
    <th class="section">{{mb_label class=CRPUReevalPEC field=datetime}}</th>
    {{if "dPurgences Display display_cimu"|gconf}}
      <th class="section">{{mb_label class=CRPUReevalPEC field=cimu}}</th>
    {{/if}}
    <th class="section">{{mb_label class=CRPUReevalPEC field=ccmu}}</th>
    <th class="section">{{mb_label class=CRPUReevalPEC field=commentaire}}</th>
    {{if !$print}}
      <th class="section">{{tr}}common-Action{{/tr}}</th>
    {{/if}}
  </tr>
  {{foreach from=$rpu->_ref_rpu_reevaluations_pec item=_reevaluation_pec}}
    <tr>
      <td>{{mb_value object=$_reevaluation_pec field=datetime}}</td>
      {{if "dPurgences Display display_cimu"|gconf}}
        <td class="text">{{mb_value object=$_reevaluation_pec field=cimu}}</td>
        {{/if}}
      <td class="text">{{mb_value object=$_reevaluation_pec field=ccmu}}</td>
      <td class="text">{{mb_value object=$_reevaluation_pec field=commentaire}}</td>
      {{if !$print}}
        <td class="button narrow">
          <button type="button" class="edit notext" onclick="Urgences.editReevaluatePEC('{{$_reevaluation_pec->_id}}', '{{$_reevaluation_pec->rpu_id}}');">
              {{tr}}Edit{{/tr}}
          </button>
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
</table>
