{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_brancardage value=1}}

{{assign var=use_brancardage value=false}}
{{if "brancardage"|module_active && "brancardage General use_brancardage"|gconf && $modules.brancardage->_can->read}}
  {{assign var=use_brancardage value=true}}
{{/if}}
{{assign var=brancard_use_retour_sans_sspi value=0}}

{{if $use_brancardage && $show_brancardage}}
  {{assign var=brancard_use_retour_sans_sspi value="brancardage General use_retour_sortie_sans_sspi"|gconf}}
  {{assign var=bloc_brancard value=$selOp->_ref_salle->bloc_id}}
  <table class="form me-no-box-shadow" style="float:left;min-width:120px;width:{{if $brancard_use_retour_sans_sspi}}12{{else}}15{{/if}}%;margin:0;">
    <tr>
      <th class="category narrow">
        {{if $brancard_use_retour_sans_sspi}}
          {{tr}}CBrancardage-arrivee-title{{/tr}}
        {{else}}
          {{tr}}CBrancardage{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <td rowspan="2">
        <div id="brancardage-aller-{{$selOp->_guid}}">
          {{mb_include module=brancardage template=inc_exist_brancard colonne="demandeBrancardage"
          object=$selOp brancardage_to_load="aller"}}
        </div>
      </td>
    </tr>
  </table>
{{/if}}

{{assign var=check_identity_pat value="dPsalleOp COperation check_identity_pat"|gconf}}

{{if $check_identity_pat}}
  {{if !$one_timing_filled}}
    <button type="button" class="barcode me-tertiary" title="{{tr}}CPatient-Choose an administrative file number{{/tr}}"
            onclick="searchPatientByNDA('{{$selOp->sejour_id}}', '{{$selOp->_id}}');">{{tr}}COperation-Validate patient identity{{/tr}}</button>

    {{mb_return}}
  {{/if}}

  {{assign var=modif_operation value=0}}
{{/if}}

{{assign var=use_incision           value="dPsalleOp timings use_incision"|gconf}}
{{assign var=use_tto                value="dPsalleOp timings use_tto"|gconf}}
{{assign var=use_sortie_sejour_ext  value="dPsalleOp timings use_sortie_sejour_ext"|gconf}}
{{assign var=timings_induction      value="dPsalleOp timings timings_induction"|gconf}}
{{assign var=garrots_multiples      value="dPsalleOp COperation garrots_multiples"|gconf}}
{{assign var=use_alr_ag             value="dPsalleOp timings use_alr_ag"|gconf}}
{{assign var=see_pec_anesth         value="dPsalleOp timings see_pec_anesth"|gconf}}
{{assign var=see_remise_anesth      value="dPsalleOp timings see_remise_anesth"|gconf}}
{{assign var=see_fin_pec_anesth     value="dPsalleOp timings see_fin_pec_anesth"|gconf}}
{{assign var=see_patient_stable     value="dPsalleOp timings see_patient_stable"|gconf}}
{{assign var=place_pec_anesth       value="dPsalleOp timings place_pec_anesth"|gconf}}
{{assign var=place_remise_chir      value="dPsalleOp timings place_remise_chir"|gconf}}
{{assign var=see_entree_reveil      value="dPsalleOp timings see_entree_reveil_timing"|gconf}}
{{assign var=use_debut_installation value="dPsalleOp timings use_debut_installation"|gconf}}
{{assign var=use_fin_installation   value="dPsalleOp timings use_fin_installation"|gconf}}

{{if $use_sortie_sejour_ext && $selOp->_ref_sejour->type == "exte"}}
  <form name="timing_use_sortie_sejour_ext" method="post" style="display:none;">
    {{mb_class object=$selOp->_ref_sejour}}
    {{mb_key   object=$selOp->_ref_sejour}}
    <input type="hidden" name="sortie_reelle" value="{{$selOp->_ref_sejour->sortie_reelle}}"/>
  </form>
{{/if}}

<table class="form me-no-box-shadow vw-timings"
   {{if $use_brancardage && $show_brancardage}}
     style="float: left; width: {{if $brancard_use_retour_sans_sspi}}75{{else}}85{{/if}}%; margin: 0;"
   {{/if}}
  >
  {{mb_ternary var=submit test=isset($submitTiming|smarty:nodefaults) value=$submitTiming other=submitTiming}}
  {{assign var=opid value=$selOp->operation_id}}
  {{assign var=form value=timing$opid}}

  {{* Affichage des colonnes selon les configurations*}}
  {{assign var=see_cell_preaparation value=false}}
  {{if "dPsalleOp timings use_entry_room"|gconf || "dPsalleOp timings use_cleaning_timings"|gconf
        || "dPsalleOp timings use_entry_exit_room"|gconf || $use_tto || $see_pec_anesth
        || $use_fin_installation || $use_debut_installation || "dPsalleOp timings use_delivery_surgeon"|gconf}}
    {{assign var=see_cell_preaparation value=true}}
  {{/if}}
  {{assign var=see_cell_operation value=false}}
  {{if "dPsalleOp timings use_garrot"|gconf || "dPsalleOp timings use_end_op"|gconf
        || "dPsalleOp timings use_suture"|gconf || "dPsalleOp timings use_prep_cutanee"|gconf || $use_incision || $see_patient_stable}}
    {{assign var=see_cell_operation value=true}}
  {{/if}}
  {{assign var=see_cell_sortie value=false}}
  {{if "dPsalleOp timings use_entry_exit_room"|gconf || "dPsalleOp timings use_exit_without_sspi"|gconf
        || "dPsalleOp timings use_cleaning_timings"|gconf || $see_entree_reveil
        || ($use_sortie_sejour_ext && $selOp->_ref_sejour->type == "exte")}}
    {{assign var=see_cell_sortie value=true}}
  {{/if}}

  <tr>
    {{if $see_cell_preaparation}}
      <th class="category">{{tr}}common-Operating preparation-court{{/tr}}</th>
    {{/if}}
    {{if $timings_induction || $use_alr_ag || $see_remise_anesth || $see_fin_pec_anesth}}
      <th class="category">{{tr}}common-Operating anesthesie{{/tr}}</th>
    {{/if}}
    {{if $see_cell_operation}}
      <th class="category">{{tr}}common-Operation{{/tr}}</th>
    {{/if}}
    {{if $see_cell_sortie}}
      <th class="category">{{tr}}common-Operating sortie-court{{/tr}}</th>
    {{/if}}
  </tr>

  <tr>
    {{if $see_cell_preaparation}}
      <td class="me-valign-top">
        {{if "dPsalleOp timings use_entry_room"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=entree_bloc}}
        {{/if}}

        {{if $see_pec_anesth && $place_pec_anesth == "under_entree_bloc"}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=pec_anesth}}
        {{/if}}

        {{if "dPsalleOp timings use_delivery_surgeon"|gconf && $place_remise_chir == "below_entree_salle"}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=remise_chir}}
        {{/if}}

        {{if "dPsalleOp timings use_preparation_op"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=preparation_op}}
        {{/if}}

        {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=entree_salle}}
        {{/if}}

        {{if "dPsalleOp timings use_delivery_surgeon"|gconf && $place_remise_chir == "under_entree_salle"}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=remise_chir}}
        {{/if}}

        {{if $use_debut_installation}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=installation_start}}
        {{/if}}
        {{if $use_fin_installation}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=installation_end}}
        {{/if}}

        {{if $use_tto}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=tto}}
        {{/if}}

        {{if $see_pec_anesth && $place_pec_anesth == "end_preparation"}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=pec_anesth}}
        {{/if}}
      </td>
    {{/if}}

    {{if $timings_induction || $use_alr_ag || $see_remise_anesth || $see_fin_pec_anesth}}
      <td class="me-valign-top">
        {{if $timings_induction}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=induction_debut}}
        {{/if}}
        {{if $use_alr_ag}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=debut_alr}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_alr}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=debut_ag}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_ag}}
        {{/if}}
        {{if $timings_induction}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=induction_fin}}
        {{/if}}
        {{if $see_remise_anesth}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=remise_anesth}}
        {{/if}}
        {{if $see_fin_pec_anesth}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_pec_anesth}}
        {{/if}}
      </td>
    {{/if}}

    {{if $see_cell_operation}}
      <td class="me-valign-top">
        {{if "dPsalleOp timings use_prep_cutanee"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=prep_cutanee}}
        {{/if}}

        {{if "dPsalleOp timings use_garrot"|gconf && !$garrots_multiples}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=pose_garrot}}
        {{/if}}

        {{if "dPsalleOp timings use_end_op"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=debut_op use_disabled=$selOp->entree_salle|default:'yes'}}
        {{/if}}

        {{if $use_incision}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=incision}}
        {{/if}}

        {{if "dPsalleOp timings use_suture"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=suture_fin}}
        {{/if}}

        {{if "dPsalleOp timings use_end_op"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_op use_disabled=$selOp->debut_op|default:'yes'}}
        {{/if}}

        {{if "dPsalleOp timings use_garrot"|gconf && !$garrots_multiples}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=retrait_garrot}}
        {{/if}}
        {{if $see_patient_stable}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=patient_stable}}
        {{/if}}
      </td>
    {{/if}}

    {{if $see_cell_sortie}}
      <td style="text-align: center;" class="me-valign-top">
        {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=sortie_salle use_disabled=$selOp->fin_op|default:'yes'}}
        {{/if}}

        {{if "dPsalleOp timings use_cleaning_timings"|gconf}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=cleaning_start}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=cleaning_end}}
        {{/if}}

        {{if $see_entree_reveil}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=entree_reveil}}
        {{/if}}

        <form name="timing{{$selOp->operation_id}}-sortie_sans_sspi" method="post">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_planning_aed" />
          {{mb_key object=$selOp}}
          <input type="hidden" name="del" value="0" />
          {{if "dPsalleOp timings use_exit_without_sspi"|gconf && $modif_operation}}
            {{mb_include module=salleOp template=inc_field_timing object=$selOp field=sortie_sans_sspi form="timing`$selOp->_id`-sortie_sans_sspi"}}
          {{/if}}
          {{if $conf.dPplanningOp.COperation.use_poste && $postes && $nbOperations && $postes <= $nbOperations}}
            <div class="small-warning" style="width: 170px;">
              {{tr}}CPosteSSPI.complet{{/tr}}
              <button type="button" class="change notext" onclick="reloadTiming('{{$selOp->_id}}');">{{tr}}Refresh{{/tr}}</button>
            </div>
          {{/if}}
          {{if $use_sortie_sejour_ext && $selOp->_ref_sejour->type == "exte"}}
            <div class="button">
              <table class="form me-no-box-shadow" style="width: 100%;">
                <tr>
                  <th class="halfPane" style="font-weight: bold;">{{mb_label object=$selOp->_ref_sejour field=sortie_reelle}}</th>
                  <td>
                    {{if $selOp->_ref_sejour->sortie_reelle}}
                      {{mb_field object=$selOp field=_datetime_reel value=$selOp->_ref_sejour->sortie_reelle form="$form-sortie_sans_sspi" register=true onchange="changeSortieReelle(this);"}}
                    {{else}}
                      <input type="hidden" name="_datetime_reel" value="" onchange="changeSortieReelle(this);"/>
                      <input type="hidden" name="_set__datetime_reel" value="1" />
                      <button type="button" class="submit notext" onclick="$V(this.form._datetime_reel, 'current', true);"></button>
                    {{/if}}
                  </td>
                </tr>
              </table>
            </div>
          {{/if}}
        </form>

        {{if "dPsalleOp timings use_validation_timings"|gconf}}
          <div class="button">
            {{if @$modules.dPbloc->_can->edit}}
              <form name="timing{{$selOp->_id}}-validation_timing" method="post">
                {{mb_class object=$selOp}}
                {{mb_key   object=$selOp}}
                {{mb_field object=$selOp field=validation_timing hidden=true}}

                <button class="{{if $selOp->validation_timing}}cancel{{else}}tick{{/if}}" type="button"
                        onclick="$V(this.form.validation_timing, $V(this.form.validation_timing) ? '' : 'current');{{$submit}}(this.form);">
                  {{if $selOp->validation_timing}}
                    {{tr}}COperation-validation_timing-cancel{{/tr}}
                  {{else}}
                    {{mb_label object=$selOp field=validation_timing}}
                  {{/if}}
                </button>
              </form>
            {{else}}
              {{mb_include module=salleOp template=inc_form_timing object=$selOp field=validation_timing disabled="yes" modif_operation=false}}
            {{/if}}
          </div>
        {{/if}}
      </td>
    {{/if}}
  </tr>
  {{if !$modif_operation}}
    <tr>
      <th class="category">{{tr}}common-Operating induction{{/tr}}</th>
      <th class="category">SSPI</th>
      <th class="category" colspan="10"></th>
    </tr>
    <tr>
      <td class="me-valign-top">
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=induction_debut}}
        {{if $use_alr_ag}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=debut_alr}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_alr}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=debut_ag}}
          {{mb_include module=salleOp template=inc_form_timing object=$selOp field=fin_ag}}
        {{/if}}
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=induction_fin}}
      </td>
      <td class="me-valign-top">
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=entree_reveil}}
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=sortie_reveil_possible}}
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=sortie_reveil_reel}}
        {{mb_include module=salleOp template=inc_form_timing object=$selOp field=sortie_sans_sspi}}
      </td>
      <td colspan="10"></td>
    </tr>
  {{/if}}
</table>

{{if $use_brancardage && $show_brancardage && $brancard_use_retour_sans_sspi
&& "dPsalleOp timings use_exit_without_sspi"|gconf && $selOp->_ref_last_brancardage->_id
&& $selOp->_ref_last_brancardage->_id != $selOp->_ref_current_brancardage->_id}}
  <table class="form me-no-box-shadow" style="float:left;min-width:120px;width:13%;margin:0;">
    <tr>
      <th class="category narrow">{{tr}}CBrancardage-retour-title{{/tr}}</th>
    </tr>
    <tr>
      <td id="brancardage-retour-{{$selOp->_guid}}" rowspan="2">
        {{mb_include module=brancardage template=inc_exist_brancard colonne="demandeBrancardage" object=$selOp
        brancardage_to_load="infini" add_step=true}}
      </td>
    </tr>
  </table>
{{/if}}
