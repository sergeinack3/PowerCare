{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour value=$_operation->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
<tbody class="hoverable" data-plage_id="hors_plage"{{if $hiddenPlages|strpos:'hors_plage' !== false}} style="display: none;"{{/if}}>
  <tr>
    {{assign var=background value=""}}
    {{if $_operation->entree_salle && $_operation->sortie_salle}}
      {{assign var=background value="background-image:url(images/icons/ray.gif); background-repeat:repeat;"}}
    {{elseif $_operation->entree_salle}}
      {{assign var=background value="background-color:#cfc;"}}
    {{elseif $_operation->sortie_salle}}
      {{assign var=background value="background-color:#fcc;"}}
    {{elseif $_operation->entree_bloc}}
      {{assign var=background value="background-color:#ffa;"}}
    {{/if}}
    <td rowspan="2" class="narrow" style="{{$background}}"></td>

    <td rowspan="2" class="top text" class="top">
      {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
      {{if $prescription && $prescription->_id && $prescription->_counts_by_chapitre|@array_sum}}
        <img src="images/icons/ampoule_blue.png" style="float: right;" />
      {{/if}}
      {{if $patient->_ref_dossier_medical->_id && $patient->_ref_dossier_medical->_count_allergies}}
        {{me_img src="warning.png" icon="warning" class="me-warning" style="float:right" onmouseover="ObjectTooltip.createEx(this, '`$patient->_guid`', 'allergies')"}}
      {{/if}}

      {{if $_operation->annulee}}
        [{{tr}}COperation-CANCELED{{/tr}}]
      {{else}}
        <strong>
          {{mb_value object=$_operation field=time_operation}}
        </strong>
      {{/if}}

      <a href="{{$patient->_dossier_cabinet_url}}">
        <strong
          class="{{if !$sejour->entree_reelle}}patient-not-arrived{{/if}}"
          onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}} - {{$patient->_age}}</strong>
      </a>

      <form name="edit-time_operation-{{$_operation->_guid}}" action="?m={{$m}}" method="post" class="prepared" style="display: block;"
            onsubmit="return onSubmitFormAjax(this, function() {updateListOperations('{{$date}}'); refreshListPlage();})">
        <input type="hidden" name="m" value="dPplanningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="operation_id" value="{{$_operation->_id}}" />
        {{mb_label object=$_operation field=temp_operation}} :
        {{if !"dPplanningOp COperation only_admin_can_change_time_op"|gconf || @$modules.dPplanningOp->_can->admin || $app->_ref_user->isAdmin()}}
          {{mb_field object=$_operation field=temp_operation register=true form="edit-time_operation-"|cat:$_operation->_guid onchange="this.form.onsubmit();"}}
        {{else}}
          {{mb_value object=$_operation field=temp_operation}}
        {{/if}}
      </form>

      {{tr}}CSejour.type.{{$sejour->type}}.short{{/tr}} - le {{mb_value object=$sejour field=entree_prevue}}

      <form name="change-salle-{{$_operation->_guid}}" method="post" class="prepared" style="display: block;"
            onsubmit="return onSubmitFormAjax(this, function() {updateListOperations('{{$date}}'); refreshListPlage();})">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="operation_id" value="{{$_operation->_id}}" />
        <select name="salle_id" onchange="this.form.onsubmit();">
          <option value="{{$_operation->salle_id}}">&mdash; Basculer vers</option>
          {{foreach from=$salles item=_salle}}
            {{if $_salle->_id != $_operation->salle_id}}
              <option value="{{$_salle->_id}}">
                {{$_salle->_view}}
              </option>
            {{/if}}
          {{/foreach}}
        </select>
      </form>
    </td>
    <td class="text top">
      {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$_operation->_patient_id object=$patient display_center=0 float_right=1}}
      {{if "syntheseMed"|module_active}}
        {{mb_include module=syntheseMed template=inc_button_synthese float="right"}}
      {{/if}}

      <button type="button" class="soins" style="float: right;" onclick="Operation.showDossierSoins('{{$_operation->sejour_id}}', 'suivi_clinique', updateListOperations)">
        {{tr}}soins.button.Dossier-soins{{/tr}}
      </button>
      <button type="button" class="injection" style="float: right;" onclick="Operation.dossierBloc('{{$_operation->_id}}', updateListOperations.curry('{{$date}}'))">
        {{tr}}mod-dPsalleOp-tab-ajax_vw_operation{{/tr}}
      </button>
      <span style="float: right; margin: 5px;"
              {{if $_operation->_codes_ccam|@count == 0}} class="circled error" title="{{tr}}COperation-msg-codage-none{{/tr}}"
              {{elseif $_operation->_count.codes_ccam != $_operation->_count.actes_ccam}} class="circled warning" title="{{tr}}COperation-msg-codage-pending{{/tr}}"
              {{else}} class="circled ok" title="{{tr}}COperation-msg-codage-complete{{/tr}}"{{/if}}>
                {{tr}}COperation-msg-codage{{/tr}}
              </span>
      <a href="#1" onclick="Operation.editModal('{{$_operation->_id}}', '{{$_operation->plageop_id}}', updateListOperations)" style="float: left;">
        {{if $_operation->salle_id}}Effectué en salle {{$_operation->_ref_salle}}{{/if}}
        {{mb_include template=inc_vw_operation}}
        ({{mb_label object=$_operation field=cote}} {{mb_value object=$_operation field=cote}})
      </a>
      {{assign var=commande value=$_operation->_ref_commande_mat.bloc}}
      {{if $commande && $commande->_id}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$commande->_guid}}')" style="float: left;">
                &nbsp;&nbsp;{{tr}}COperation-materiel-court{{/tr}} {{mb_value object=$commande field=etat}}
              </span>
      {{/if}}
      {{assign var=commande_pharma value=$_operation->_ref_commande_mat.pharmacie}}
      {{if $commande_pharma && $commande_pharma->_id}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$commande_pharma->_guid}}')" style="float: left;">
                &nbsp;&nbsp;{{tr}}COperation-materiel_pharma-court{{/tr}} {{mb_value object=$commande_pharma field=etat}}
              </span>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td class="top">
      {{mb_include template=inc_documents_operation operation=$_operation preloaded=true}}
    </td>
  </tr>
</tbody>