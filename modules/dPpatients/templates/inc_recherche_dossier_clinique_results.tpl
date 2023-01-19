{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation ajax=$ajax}}

<script>
  Main.add(function () {
    Consultation.moduleConsult = 'oxCabinet';
  });
</script>

<div style="width: 100%; padding-bottom: 5px; height: 20px;" class="not-printable">
  {{if $one_field}}
  <button type="button" style="float: left;" class="hslip me-tertiary" onclick="exportResults();">{{tr}}mod-dPpatients-tab-vw_recherche-action-export text{{/tr}}</button>
    <button type="button" style="float: left;" class="print me-tertiary" onclick="modal_results.print();">{{tr}}Print{{/tr}}</button>
    {{/if}}
</div>

{{if !$one_field}}
  <div class="small-info">
    {{tr}}mod-dPpatients-tab-vw_recherche-msg-please fill in at least one field in the form on the left to perform a search{{/tr}}
  </div>
{{else}}

  {{mb_include module=system template=inc_pagination
  total=$count_patient change_page="changePage" step=30 current=$start}}

  {{if $from || $to}}
    <h1 style="text-align: center; page-break-before: avoid;">
      {{if $from}}
        {{if $to}}
          Période du {{$from|date_format:$conf.date}} au {{$to|date_format:$conf.date}}
        {{else}}
          {{tr}}date.From_long{{/tr}} {{$from|date_format:$conf.date}}
        {{/if}}
      {{elseif $to}}
        {{tr}}date.To_long{{/tr}} {{$to|date_format:$conf.date}}
      {{/if}}
    </h1>
  {{/if}}
  <table class="main tbl">
    <tr>
      <th>{{mb_label class=CSejour field=patient_id}} {{if $group_by_patient}}({{tr}}CPatients-total of patient{{/tr}} = {{$count_nb_patient}}){{/if}}</th>
      <th>{{tr}}CDossierMedical{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-age at the time{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-event{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-prescription{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-DCI{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-ATC code{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-libelle ATC{{/tr}}</th>
      <th>{{tr}}DossierClinique-category-comment reason{{/tr}}</th>
    </tr>
      {{if $group_by_patient}}
          {{foreach from=$list_group_by_patient item=list_patient}}
              {{foreach from=$list_patient item=_patient name=patients_list}}
                  {{mb_include module=patients template=inc_recherche_dossier_clinique_result}}
              {{/foreach}}
              {{foreachelse}}
            <tr>
              <td class="empty" colspan="9">
                {{tr}}No result{{/tr}}
              </td>
            </tr>
          {{/foreach}}
      {{else}}
          {{foreach from=$list_patient item=_patient name=patients_list}}
              {{mb_include module=patients template=inc_recherche_dossier_clinique_result}}
              {{foreachelse}}
                <tr>
                  <td class="empty" colspan="9">
                    {{tr}}No result{{/tr}}
                  </td>
                </tr>
          {{/foreach}}
      {{/if}}
  </table>
{{/if}}
