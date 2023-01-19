{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var="redirect_tab" value="0"}}
{{mb_default var="ajax_salle" value="0"}}
{{mb_default var="non_placees" value=0}}
{{mb_default var="mode_presentation" value=0}}
{{mb_default var="practitioner" value=0}}
{{mb_default var=view_light value=0}}
{{mb_default var=dmi_active value=0}}

<!-- Entêtes -->
<tr>
  {{if $urgence && $salle}}
    <th>{{tr}}common-Practitioner{{/tr}}</th>
    {{assign var=practitioner value=1}}
  {{else}}
    {{if $app->user_prefs.bloc_display_duration_intervention && ($urgence || $non_placees)}}
      <th>{{mb_title class=COperation field=temp_operation}}</th>
      {{assign var=practitioner value=0}}
    {{else}}
      <th>{{tr}}common-Hour{{/tr}}</th>
    {{/if}}
  {{/if}}
  <th>{{tr}}CPatient{{/tr}}</th>
  <th>{{tr}}COperation-court{{/tr}}</th>
  <th>{{mb_title class=COperation field=cote}}</th>
  {{if !$vueReduite}}
    <th>{{tr}}COperation-temp_operation-court{{/tr}}</th>
  {{/if}}
  {{if $mode_presentation && @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
    <th class="narrow">{{tr}}CBrancardage{{/tr}}</th>
  {{/if}}
</tr>

{{assign var=systeme_materiel value="dPbloc CPlageOp systeme_materiel"|gconf}}
{{assign var=save_op value=$operations|@first}}

{{foreach from=$operations item=_operation name=ops}}
  {{if "dPsalleOp COperation allow_change_room"|gconf}}
    {{assign var="rowspan" value=2}}
  {{else}}
    {{assign var="rowspan" value=1}}
  {{/if}}

  {{if !$_operation->annulee || ($_operation->annulee && $app->user_prefs.planning_bloc_show_cancelled_operations)}}
    {{if $smarty.foreach.ops.index > 0}}
      {{assign var=preop_curr value=$_operation->presence_preop}}
      {{assign var=timeop_curr value=$_operation->time_operation}}
      {{assign var=postop_save value=$save_op->presence_postop}}
      {{assign var=timeop_save value=$save_op->time_operation}}
      {{assign var=tempop_save value=$save_op->temp_operation}}
        {{if $save_op->pause != '00:00:00'}}
          <tr>
            <th colspan="5" class="section">
              [{{tr}}Pause{{/tr}}] ({{$save_op->pause|date_format:$conf.time}})
            </th>
          </tr>
        {{/if}}
      {{assign var=save_op value=$_operation}}
    {{/if}}
    <tbody class="hoverable">
      <tr {{if $_operation->_id == $operation_id}}class="selected"{{/if}}>
        {{if $_operation->_deplacee && $_operation->salle_id != $salle->_id}}
          <td class="text" rowspan="{{$rowspan}}" style="background-color:#ccf">
        {{elseif $_operation->entree_salle && $_operation->sortie_salle}}
          <td class="text me-hatching" rowspan="{{$rowspan}}" style="background-image:url(images/icons/ray.gif); background-repeat:repeat;">
        {{elseif $_operation->entree_salle}}
          <td class="text" rowspan="{{$rowspan}}" style="background-color:#cfc">
        {{elseif $_operation->sortie_salle}}
          <td class="text" rowspan="{{$rowspan}}" style="background-color:#fcc">
        {{elseif $_operation->entree_bloc}}
          <td class="text" rowspan="{{$rowspan}}" style="background-color:#ffa">
        {{else}}
          <td class="text" rowspan="{{$rowspan}}">
        {{/if}}
          {{if $salle}}
            <a {{if $redirect_tab}}
                href="?m=salleOp&tab=vw_operations&operation_id={{$_operation->_id}}&salle={{$salle->_id}}"
              {{else}}
                href="#1" onclick="SalleOp.loadOperation('{{$_operation->_id}}', this.up('tr'))"
              {{/if}}
              title="Coder l'intervention">
          {{/if}}
            {{if (($urgence && $salle) || $_operation->_ref_plageop->spec_id) && $practitioner}}
              {{$_operation->_ref_chir}}
              {{if $vueReduite && $_operation->_ref_anesth->_id && $practitioner}}
                {{$_operation->_ref_anesth}} -
              {{/if}}
            {{/if}}
            {{if $_operation->time_operation != "00:00:00" && !$_operation->entree_salle}}
              {{if $app->user_prefs.bloc_display_duration_intervention && $urgence}}
                {{if !$practitioner}}{{mb_value object=$_operation field=temp_operation}}{{/if}}
              {{else}}
                {{mb_value object=$_operation field=time_operation}}
                <br />
                {{$_operation->_fin_prevue|date_format:$conf.time}}
              {{/if}}
            {{elseif $_operation->entree_salle}}
              {{mb_value object=$_operation field=entree_salle}}
              {{if $_operation->sortie_salle}}
                <br />
                {{mb_value object=$_operation field=sortie_salle}}
              {{/if}}
            {{else}}
              {{if $app->user_prefs.bloc_display_duration_intervention && ($urgence || $non_placees) && !$practitioner}}
                {{mb_value object=$_operation field=temp_operation}}
              {{else}}
                NP
              {{/if}}
            {{/if}}
            {{if $vueReduite && $urgence && $salle}}
              ({{$_operation->temp_operation|date_format:$conf.time}})

              {{if "dPbloc affichage view_prepost_suivi"|gconf}}
                {{if $_operation->presence_preop}}
                  <div>
                    Pré-op : {{$_operation->presence_preop|date_format:$conf.time}}
                  </div>
                {{/if}}
                {{if $_operation->presence_postop}}
                  <div>
                    Post-op : {{$_operation->presence_postop|date_format:$conf.time}}
                  </div>
                {{/if}}
              {{/if}}
            {{/if}}
          {{if $salle}}
            </a>
          {{/if}}

          {{if $_operation->_ref_chirs|@count > 1}}
            <span class="noteDiv" onmouseover="ObjectTooltip.createDOM(this, 'chirs_{{$_operation->_guid}}');">
              <button class="user notext">Chirurgiens multiples</button>
              <span class="countertip" style="margin-top:2px;">
                {{$_operation->_ref_chirs|@count}}
              </span>
            </span>
            <div style="display:none;" id="chirs_{{$_operation->_guid}}">
              <table class="main form">
                <tr>
                  <th colspan="2" class="title">
                    Intervention
                    {{if !$_operation->plageop_id}}[HP]{{/if}}
                    le {{$_operation->_datetime|date_format:$conf.date}}
                    {{if $_operation->_ref_patient}}de {{$_operation->_ref_patient}}
                    <span class="only-printable">({{mb_value object=$_operation->_ref_patient field=naissance}})</span>{{/if}}</th>
                </tr>
                {{foreach from=$_operation->_ref_chirs key=key_chir item=_chir}}
                  <tr>
                    <th>{{mb_label object=$_operation field=$key_chir}}</th>
                    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_chir}}</td>
                  </tr>
                {{/foreach}}
                {{if $_operation->_ref_anesth->_id}}
                  <tr>
                    <td colspan="2"><hr style="width: 50%;"/></td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$_operation field=anesth_id}}</th>
                    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth}}</td>
                  </tr>
                {{/if}}
              </table>
            </div>
          {{/if}}

          {{if $vueReduite && $systeme_materiel == "expert"}}
            <button class="print notext not-printable" onclick="printFicheBloc({{$_operation->_id}})">{{tr}}Print{{/tr}}</button>
            {{if $urgence && $salle && $_operation->_ref_besoins|@count}}
              <i class="me-icon toolbox me-primary" onmouseover="ObjectTooltip.createDOM(this, 'besoins_{{$_operation->_id}}')"></i>
              <div id="besoins_{{$_operation->_id}}" style="display: none;">
                {{tr}}CBesoinRessource.all{{/tr}} :
                <ul>
                  {{foreach from=$_operation->_ref_besoins item=_besoin}}
                   <li>
                     {{$_besoin->_ref_type_ressource}}
                   </li>
                  {{/foreach}}
                </ul>
              </div>
            {{/if}}
          {{/if}}
        </td>

        {{if $_operation->_deplacee && $_operation->salle_id != $salle->_id}}
          <td class="text" colspan="5">
            <div class="warning">
              <span class="{{if !$_operation->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_operation->_ref_sejour->septique}}septique{{/if}}"
                    onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_ref_sejour->_ref_patient->_guid}}')">
                {{$_operation->_ref_patient}}
                {{if $vueReduite}}
                  ({{$_operation->_ref_patient->_age}})
                {{/if}}
                <span class="only-printable">({{mb_value object=$_operation->_ref_patient field=naissance}})</span>
              </span>

              {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_operation->_ref_patient}}
                {{if $_operation->_ref_patient->_homonyme}}
                    {{mb_include module=dPpatients template=patient_state/inc_flag_homonyme}}
                {{/if}}

              <br />
              {{tr var1=$_operation->_ref_salle->_view}}COperation-Moved To{{/tr}}
            </div>
          </td>
        {{else}}
          <td class="text">
            {{if $salle}}
              <a {{if $redirect_tab}}
                  href="?m=salleOp&tab=vw_operations&operation_id={{$_operation->_id}}&salle={{$salle->_id}}"
                {{else}}
                  href="#1" onclick="SalleOp.loadOperation('{{$_operation->_id}}', this.up('tr'))"
                {{/if}}>
            {{/if}}
            <span class="{{if !$_operation->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_operation->_ref_sejour->septique}}septique{{/if}}"
                  onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_ref_sejour->_ref_patient->_guid}}')">
              {{$_operation->_ref_patient}}
              {{if $vueReduite}}
                ({{$_operation->_ref_patient->_age}})
              {{/if}}
              <span class="only-printable">({{mb_value object=$_operation->_ref_patient field=naissance}})</span>

              {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_operation->_ref_patient}}
                {{if $_operation->_ref_patient->_homonyme}}
                    {{mb_include module=dPpatients template=patient_state/inc_flag_homonyme}}
                {{/if}}
              {{if $_operation->_ref_sejour->presence_confidentielle}}
                {{mb_include module=planningOp template=inc_badge_sejour_conf}}
              {{/if}}
            </span>
            {{if $_operation->_ref_affectation && $_operation->_ref_affectation->_ref_lit && $_operation->_ref_affectation->_ref_lit->_id && "dPbloc affichage chambre_operation"|gconf == 1}}
              <div style="font-size: 0.9em; white-space: nowrap; border: none;">
                {{$_operation->_ref_affectation->_ref_lit->_ref_chambre->_ref_service}} &rarr; {{$_operation->_ref_affectation->_ref_lit}}
              </div>
            {{/if}}
            {{if $salle}}
              </a>
            {{/if}}
            {{if 'dPsalleOp timings use_validation_timings'|gconf && $_operation->validation_timing && !$_operation->annulee}}
              <span class="circled" style="color: forestgreen; border-color: forestgreen;">{{tr}}COperation-msg-timings_validated{{/tr}}</span>
            {{/if}}
          </td>

          <td class="text">
            {{mb_include module=system template=inc_object_notes object=$_operation float="right"}}

            {{if $dmi_active}}
              {{assign var=prescription_sejour value=$_operation->_ref_sejour->_ref_prescription_sejour}}
              {{if $prescription_sejour->_id}}
                {{mb_include module=dmi template=inc_list_dm lines_dm=$prescription_sejour->_ref_lines_dm context=$prescription_sejour}}
              {{/if}}
            {{/if}}

            {{mb_ternary var=direction test=$urgence value=vw_edit_urgence other=vw_edit_planning}}
            {{if $vueReduite}}
              <span style="float:right;">
                {{mb_include module=planningOp template=inc_reload_infos_interv operation=$_operation just_button=1}}
              </span>
            {{/if}}
            {{if $_operation->urgence}}
              <img src="images/icons/attente_fourth_part.png" title="{{tr}}COperation-emergency{{/tr}}" style="float:right;"/>
            {{/if}}
            {{if $salle}}
              <a {{if $redirect_tab}}
                  href="?m=salleOp&tab=vw_operations&operation_id={{$_operation->_id}}&salle={{$salle->_id}}"
                {{else}}
                  href="#1" onclick="SalleOp.loadOperation('{{$_operation->_id}}', this.up('tr'))"
                {{/if}}
                {{if !$_operation->_count_actes}}style="border-color: #F99" class="mediuser"{{/if}}>
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')" >
            {{if $_operation->libelle}}
              {{$_operation->libelle}}
            {{else}}
              {{foreach from=$_operation->_ext_codes_ccam_princ item=_code}}
                {{$_code->code}}
              {{/foreach}}
            {{/if}}
            {{if $_operation->_ref_sejour->type == "comp" && $vueReduite}}
              ({{$_operation->_ref_sejour->type|truncate:1:""|capitalize}} {{$_operation->_ref_sejour->_duree}}j)
            {{else}}
               ({{$_operation->_ref_sejour->type|truncate:1:""|capitalize}})
            {{/if}}
            </span>

            {{mb_include module=planningOp template=inc_icon_panier operation=$_operation float="right" title=1 font_size="1.3em"}}

            <br/>
            {{if "dPbloc affichage view_tools"|gconf && $_operation->_ref_besoins|is_countable && $_operation->_ref_besoins|@count && "dPbloc CPlageOp systeme_materiel"|gconf == "expert"}}
              <strong>Mat.</strong> :
              {{foreach from=$_operation->_ref_besoins item=_matos name=matos}}
                {{$_matos->_ref_type_ressource}}
                {{if !$smarty.foreach.matos.last}}, {{/if}}
              {{/foreach}}<br/>
            {{/if}}
            {{if "dPbloc affichage view_required_tools"|gconf && $_operation->materiel}}
              <strong>Mat. à P.</strong> : {{$_operation->materiel}}<br/>
            {{/if}}
            {{if "dPbloc affichage view_required_tools"|gconf && $_operation->exam_per_op}}
              <strong>Exam per-op</strong> : {{$_operation->exam_per_op}}<br/>
            {{/if}}
            {{if "dPbloc affichage view_anesth_type"|gconf && $_operation->type_anesth}}
              <strong>T Anesth.</strong> : {{mb_value object=$_operation field=type_anesth}}<br/>
            {{/if}}
            {{if "dPbloc affichage view_rques"|gconf && $_operation->rques}}
              <strong>Rques</strong> : {{mb_value object=$_operation field=rques}}<br/>
            {{/if}}

            {{if $salle}}
              </a>
            {{/if}}
            {{if $view_light}}
              <form name="editMaterielSterilise_{{$_operation->_id}}" action="?m={{$m}}" method="post">
                {{mb_key   object=$_operation}}
                {{mb_class object=$_operation}}
                {{mb_field object=$_operation field=materiel_sterilise typeEnum=checkbox onchange="return onSubmitFormAjax(this.form);"}}
                <label for="__materiel_sterilise">{{tr}}COperation-materiel_sterilise-court{{/tr}}</label>
              </form>
            {{/if}}
            {{if $_operation->annulee}}
              <span class="circled" style="color: firebrick; border-color: firebrick; float: right;">{{tr}}COperation-annulee-court{{/tr}}</span>
            {{/if}}
          </td>
          <td>
            {{if $conf.dPplanningOp.COperation.verif_cote && ($_operation->cote == "droit" || $_operation->cote == "gauche") && !$vueReduite && !$view_light}}
              <form name="editCoteOp{{$_operation->_id}}" action="?m={{$m}}" method="post">
                <input type="hidden" name="m" value="dPplanningOp" />
                <input type="hidden" name="dosql" value="do_planning_aed" />
                {{mb_key object=$_operation}}
                {{mb_field emptyLabel="COperation-cote_bloc" object=$_operation field="cote_bloc" onchange="return onSubmitFormAjax(this.form);"}}
              </form>
            {{else}}
            {{mb_value object=$_operation field="cote"}}
          {{/if}}
          </td>
          {{if $mode_presentation && @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
            <td id="brancardage-{{$_operation->_guid}}">
              {{mb_include module=brancardage template=inc_exist_brancard colonne="demandeBrancardage"
              object=$_operation brancardage_to_load="aller" mode_presentation=1}}
            </td>
          {{/if}}
          {{if !$vueReduite}}
            <td {{if $_operation->_presence_salle}}style="background-image:url(images/icons/ray.gif); background-repeat:repeat;" class="me-hatching"{{/if}}>
              {{if $_operation->_presence_salle}}
                {{mb_value object=$_operation field=_presence_salle}}
              {{else}}
                {{$_operation->temp_operation|date_format:$conf.time}}
              {{/if}}
            </td>
          {{/if}}
        {{/if}}
      </tr>

      {{if "dPsalleOp COperation allow_change_room"|gconf && !($_operation->_deplacee && $_operation->salle_id != $salle->_id) && @$allow_moves|default:1}}
        <tr {{if $_operation->_id == $operation_id}}class="selected"{{/if}}>
          <td colspan="5" class="not-printable">
            <form name="changeSalle{{$_operation->_id}}" action="?m={{$m}}" method="post" {{if $ajax_salle}}onsubmit="return onSubmitFormAjax(this, {onComplete: window.updateSuiviSalle || SalleOp.refreshListOp});" {{/if}}>
            <input type="hidden" name="dosql" value="do_planning_aed" />
            <input type="hidden" name="m" value="dPplanningOp" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="operation_id" value="{{$_operation->_id}}" />
            <select name="salle_id" onchange="this.form.{{if $ajax_salle}}on{{/if}}submit();">
              {{if $urgence && !$_operation->salle_id}}
                <option value="">&mdash; Choisir une salle</option>
              {{/if}}
              {{foreach from=$listBlocs item=curr_bloc}}
              <optgroup label="{{$curr_bloc->nom}}">
                {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                <option value="{{$curr_salle->_id}}" {{if $curr_salle->_id == $_operation->salle_id}}selected="selected"{{/if}}>
                  {{$curr_salle->nom}}
                </option>
                {{foreachelse}}
                <option value="" disabled="disabled">{{tr}}CSalle.none{{/tr}}</option>
                {{/foreach}}
              </optgroup>
              {{/foreach}}
            </select>
            </form>
          </td>
        </tr>
      {{/if}}
    </tbody>
  {{/if}}
{{/foreach}}
