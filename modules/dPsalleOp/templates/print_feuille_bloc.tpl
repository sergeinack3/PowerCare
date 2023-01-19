{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=surveillance value=""}}

<script>
  Main.add(function () {
    var url = new Url("patients", "httpreq_vw_constantes_medicales");
    url.addParam("patient_id", {{$operation->_ref_sejour->patient_id}});
    url.addParam("context_guid", "{{$operation->_ref_sejour->_guid}}");
    url.addParam("selection[]", ["pouls", "ta_gauche", "frequence_respiratoire", "score_sedation", "spo2", "diurese"]);
    url.addParam("date_min", "{{$operation->_datetime_reel}}");
    url.addParam("date_max", "{{$operation->_datetime_reel_fin}}");
    url.addParam("print", 1);
    url.requestUpdate("constantes");

    {{if "forms"|module_installed}}
    ExObject.loadExObjects("{{$operation->_class}}", "{{$operation->_id}}", "ex_objects_list", 3, null, {print: 1});
    {{/if}}
  });
</script>

{{assign var=sejour value=$operation->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=consult_anesth value=$operation->_ref_consult_anesth}}

<table class="print">
  <tr>
    <th class="title">
      <a href="#" onclick="window.print()" style="font-size: 1.3em;">
        Feuille de Bloc
      </a>
    </th>
  </tr>
</table>

<table class="print">
  <tr>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="3">{{tr}}CPatient-Patient information{{/tr}}</th>
        </tr>
        <tr>
          <td colspan="2">{{$patient->_view}}</td>
          {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
            <td rowspan="2">{{mb_include module=dPpatients template=vw_datamatrix_ins}}</td>
          {{/if}}
        </tr>
        <tr>
          <td colspan="2">
            Né{{if $patient->sexe != "m"}}e{{/if}} le {{mb_value object=$patient field=naissance}}
            ({{$patient->_age}})
            - sexe {{if $patient->sexe == "m"}} masculin {{else}} féminin {{/if}}
          </td>
        </tr>
        {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
          <tr>
            <td>
                {{tr}}CINSPatient{{/tr}} : {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
            </td>
          </tr>
        {{/if}}
      </table>

      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="2">Intervention</th>
        </tr>

        <tr>
          <th>{{mb_label object=$operation->_ref_plageop field=date}}</th>
          <td class="greedyPane">
            {{mb_value object=$operation field=_datetime}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=salle_id}}</th>
          <td class="text">
            {{$operation->_ref_salle->_view}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=libelle}}</th>
          <td class="text">
            {{if $operation->libelle}}
              {{mb_value object=$operation field=libelle}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=ASA}}</th>
          <td>
            {{if $operation->ASA}}
              {{mb_value object=$operation field=ASA}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=cote}}</th>
          <td>{{mb_value object=$operation field=cote}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=position_id}}</th>
          <td>
            {{if $operation->position_id}}
              {{mb_value object=$operation field=position_id}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=chir_id}}</th>
          <td class="text">Dr {{$operation->_ref_chir->_view}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=anesth_id}}</th>
          <td class="text">
            {{if $operation->_ref_anesth->user_id}}
              Dr {{$operation->_ref_anesth->_view}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>

        {{if $consult_anesth->type_anesth != $operation->type_anesth && $consult_anesth->type_anesth != ""}}
          <tr>
            <th
              title="{{tr}}CConsultAnesth-Type of anesthesia planned-desc{{/tr}}">{{tr}}CConsultAnesth-Type of anesthesia planned{{/tr}}</th>
            <td class="text">
              {{if $consult_anesth->type_anesth}}
                {{mb_value object=$consult_anesth field=type_anesth}}
              {{else}}
                &mdash;
              {{/if}}
            </td>
          </tr>
        {{/if}}

        <tr>
          <th title="{{tr}}COperation-type_anesth-desc{{/tr}}">{{tr}}COperation-Type of anesthesia performed{{/tr}}</th>
          <td class="text">
            {{if $operation->type_anesth}}
              {{mb_value object=$operation field=type_anesth}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>Personnel</th>
          <td class="text">
            <table>
              {{foreach from=$operation->_ref_affectations_personnel item=affectations key=emplacement}}
                <tr>
                  {{if $affectations|@count}}
                    <th colspan="2" style="text-align: left;">{{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}:</th>
                  {{/if}}
                </tr>
                {{foreach from=$affectations item=_affectation}}
                  <tr>
                    <td>{{$_affectation->_ref_personnel->_ref_user->_view}}</td>
                    <td>{{$_affectation->debut|date_format:$conf.time}} -
                      {{$_affectation->fin|date_format:$conf.time}}</td>
                  </tr>
                {{/foreach}}
                {{foreachelse}}
                <tr>
                  <td>{{tr}}CPersonnel.none{{/tr}}</td>
                </tr>
              {{/foreach}}
            </table>
          </td>
        </tr>
        {{if $operation->visitors}}
          <tr>
            <th>{{mb_title class=COperation field=visitors}}</th>
            <td>{{mb_value object=$operation field=visitors}}</td>
          </tr>
        {{/if}}
        <tr>
          <th>Matériel</th>
          <td class="text">
            {{if $operation->materiel}}
              {{mb_value object=$operation field=materiel}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=exam_per_op}}</th>
          <td class="text">
            {{if $operation->exam_per_op}}
              {{mb_value object=$operation field=exam_per_op}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>Remarques</th>
          <td class="text">
            {{if $operation->rques}}
              {{mb_value object=$operation field=rques}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_title object=$operation field=anapath}}</th>
          <td class="text">
            {{if $operation->anapath == 1}}
              {{if $operation->flacons_anapath}}{{mb_value object=$operation field=flacons_anapath}} flacons<br/>{{/if}}
              {{if $operation->labo_anapath_id}}{{mb_value object=$operation field=labo_anapath_id}}<br/>{{/if}}
              {{if $operation->description_anapath}}{{mb_value object=$operation field=description_anapath}}{{/if}}
              {{if !$operation->flacons_anapath && !$operation->labo_anapath_id && !$operation->description_anapath}}{{tr}}Yes{{/tr}}{{/if}}
            {{elseif $operation->anapath != 0}}
              {{tr}}No{{/tr}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_title object=$operation field=labo}}</th>
          <td class="text">
            {{if $operation->labo == 1}}
              {{if $operation->flacons_bacterio}}{{mb_value object=$operation field=flacons_bacterio}} flacons
                <br/>
              {{/if}}
              {{if $operation->labo_bacterio_id}}{{mb_value object=$operation field=labo_bacterio_id}}<br/>{{/if}}
              {{if $operation->description_bacterio}}{{mb_value object=$operation field=description_bacterio}}{{/if}}
              {{if !$operation->flacons_bacterio && !$operation->labo_bacterio_id && !$operation->description_bacterio}}{{tr}}Yes{{/tr}}{{/if}}
            {{elseif $operation->labo != 0}}
              {{tr}}No{{/tr}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{mb_title object=$operation field=rayons_x}}</th>
          <td class="text">
            {{if $operation->rayons_x}}
              {{if $operation->ampli_id}}{{mb_value object=$operation field=ampli_id}}<br/>{{/if}}
              {{if $operation->temps_rayons_x}}{{$operation->temps_rayons_x|date_format:"%Hh %Mmin %Ssec"}}<br/>{{/if}}
              {{if $operation->dose_rayons_x}}
                {{mb_value object=$operation field=dose_rayons_x}} {{mb_value object=$operation field=unite_rayons_x}}

              {{/if}}
              {{if $operation->description_rayons_x}}{{mb_value object=$operation field=description_rayons_x}}{{/if}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category" colspan="2">Visite préanesthésique</th>
        </tr>
        {{if $operation->prat_visite_anesth_id}}
          <tr>
            <th>{{mb_label object=$operation field=prat_visite_anesth_id}}</th>
            <td>Dr {{$operation->_ref_anesth_visite->_view}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=date_visite_anesth}}</th>
            <td>
              {{$operation->date_visite_anesth|date_format:$conf.date}}
              {{if "dPsalleOp COperation use_time_vpa"|gconf && $operation->time_visite_anesth}}
                à {{$operation->time_visite_anesth|date_format:$conf.time}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=rques_visite_anesth}}</th>
            <td>{{mb_value object=$operation field=rques_visite_anesth}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=autorisation_anesth}}</th>
            <td>{{mb_value object=$operation field=autorisation_anesth}}</td>
          </tr>
        {{else}}
          <tr>
            <td colspan="2">Non saisie</td>
          </tr>
        {{/if}}
      </table>

    </td>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="2">Horaires</th>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=debut_prepa_preop}}</th>
          <td class="halfPane">{{mb_value object=$operation field=debut_prepa_preop}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=fin_prepa_preop}}</th>
          <td class="halfPane">{{mb_value object=$operation field=fin_prepa_preop}}</td>
        </tr>
        {{assign var=see_pec_anesth value="dPsalleOp timings see_pec_anesth"|gconf}}
        {{assign var=place_pec_anesth value="dPsalleOp timings place_pec_anesth"|gconf}}
        {{if $see_pec_anesth && $place_pec_anesth == "under_entree_bloc"}}
          <tr>
            <th>{{mb_label object=$operation field=pec_anesth}}</th>
            <td class="halfPane">{{mb_value object=$operation field=pec_anesth}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_delivery_surgeon"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=remise_chir}}</th>
            <td class="halfPane">{{mb_value object=$operation field=remise_chir}}</td>
          </tr>
        {{/if}}
        {{if "dPsalleOp timings use_preparation_op"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=preparation_op}}</th>
            <td class="halfPane">{{mb_value object=$operation field=preparation_op}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=entree_salle}}</th>
            <td class="halfPane">{{mb_value object=$operation field=entree_salle}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_debut_installation"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=installation_start}}</th>
            <td class="halfPane">{{mb_value object=$operation field=installation_start}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_fin_installation"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=installation_end}}</th>
            <td class="halfPane">{{mb_value object=$operation field=installation_end}}</td>
          </tr>
        {{/if}}

        {{if $see_pec_anesth && $place_pec_anesth == "end_preparation"}}
          <tr>
            <th>{{mb_label object=$operation field=pec_anesth}}</th>
            <td class="halfPane">{{mb_value object=$operation field=pec_anesth}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings timings_induction"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=induction_debut}}</th>
            <td class="halfPane">{{mb_value object=$operation field=induction_debut}}</td>
          </tr>
        {{/if}}
        {{if "dPsalleOp timings use_alr_ag"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=debut_alr}}</th>
            <td class="halfPane">{{mb_value object=$operation field=debut_alr}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=fin_alr}}</th>
            <td class="halfPane">{{mb_value object=$operation field=fin_alr}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=debut_ag}}</th>
            <td class="halfPane">{{mb_value object=$operation field=debut_ag}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=fin_ag}}</th>
            <td class="halfPane">{{mb_value object=$operation field=fin_ag}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings timings_induction"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=induction_fin}}</th>
            <td class="halfPane">{{mb_value object=$operation field=induction_fin}}</td>
          </tr>
        {{/if}}
        {{if "dPsalleOp timings see_remise_anesth"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=remise_anesth}}</th>
            <td class="halfPane">{{mb_value object=$operation field=remise_anesth}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_garrot"|gconf && !'dPsalleOp COperation garrots_multiples'|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=pose_garrot}}</th>
            <td class="halfPane">{{mb_value object=$operation field=pose_garrot}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_prep_cutanee"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=prep_cutanee}}</th>
            <td class="halfPane">{{mb_value object=$operation field=prep_cutanee}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_end_op"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=debut_op}}</th>
            <td class="halfPane">{{mb_value object=$operation field=debut_op}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_incision"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=incision}}</th>
            <td class="halfPane">{{mb_value object=$operation field=incision}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_end_op"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=fin_op}}</th>
            <td class="halfPane">{{mb_value object=$operation field=fin_op}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_garrot"|gconf && !'dPsalleOp COperation garrots_multiples'|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=retrait_garrot}}</th>
            <td class="halfPane">{{mb_value object=$operation field=retrait_garrot}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings see_patient_stable"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=patient_stable}}</th>
            <td class="halfPane">{{mb_value object=$operation field=patient_stable}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings see_fin_pec_anesth"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=fin_pec_anesth}}</th>
            <td class="halfPane">{{mb_value object=$operation field=fin_pec_anesth}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_entry_exit_room"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=sortie_salle}}</th>
            <td class="halfPane">{{mb_value object=$operation field=sortie_salle}}</td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_cleaning_timings"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=cleaning_start}}</th>
            <td class="halfPane">{{mb_value object=$operation field=cleaning_start}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=cleaning_end}}</th>
            <td class="halfPane">{{mb_value object=$operation field=cleaning_end}}</td>
          </tr>
        {{/if}}

        {{if $operation->entree_reveil != "" || $operation->sortie_reveil_possible != "" || $operation->sortie_reveil_reel != ""}}
          <tr>
            <th>{{mb_label object=$operation field=entree_reveil}}</th>
            <td class="halfPane">{{mb_value object=$operation field=entree_reveil}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=sortie_reveil_reel}}</th>
            <td class="halfPane">{{mb_value object=$operation field=sortie_reveil_reel}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$operation field=sortie_reveil_possible}}</th>
            <td class="halfPane">
              {{mb_value object=$operation field=sortie_reveil_possible}}
              {{if $operation->sortie_locker_id}}
                <br/>
                Validée par {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_sortie_locker}}
              {{/if}}
            </td>
          </tr>
        {{/if}}

        {{if "dPsalleOp timings use_garrot"|gconf && 'dPsalleOp COperation garrots_multiples'|gconf}}
          <tr>
            <th class="category" colspan="2">{{tr}}COperationGarrot|pl{{/tr}}</th>
          </tr>
          {{foreach from=$operation->_ref_garrots item=_garrot name=garrots}}
            <tr>
              <th>{{mb_value object=$_garrot field=cote}}</th>

              <td style="text-align: left;">
                <strong>{{mb_label object=$_garrot field=pression}}</strong> :
                {{mb_value object=$_garrot field=pression}} {{tr}}common-mmhg{{/tr}}
                <br/>
                <strong>{{mb_label object=$_garrot field=datetime_pose}}</strong> :
                {{mb_value object=$_garrot field=datetime_pose}}
                {{if $_garrot->datetime_retrait}}
                  <br/>
                  <strong>{{mb_label object=$_garrot field=datetime_retrait}}</strong>
                  :
                  {{mb_value object=$_garrot field=datetime_retrait}}
                  <br/>
                  <strong>{{mb_label object=$_garrot field=_duree}}</strong>
                  :
                  {{mb_value object=$_garrot field=_duree}} {{tr}}common-minute-court{{/tr}}
                {{/if}}
                {{if !$smarty.foreach.garrots.last }}
                  <hr/>
                {{/if}}
              </td>
            </tr>
            {{foreachelse}}
            <tr>
              <td class="empty" colspan="2" style="text-align: center;">
                {{tr}}COperationGarrot.none{{/tr}}
              </td>
            </tr>
          {{/foreach}}
        {{/if}}
      </table>

      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="2">Durées</th>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=_presence_salle}}</th>
          <td class="halfPane">{{mb_value object=$operation field=_presence_salle}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=_duree_interv}}</th>
          <td class="halfPane">{{mb_value object=$operation field=_duree_interv}}</td>
        </tr>
        {{if $operation->_duree_garrot}}
          <tr>
            <th>{{mb_label object=$operation field=_duree_garrot}}</th>
            <td class="halfPane">{{mb_value object=$operation field=_duree_garrot}}</td>
          </tr>
        {{/if}}
        <tr>
          <th>{{mb_label object=$operation field=_duree_sspi}}</th>
          <td class="halfPane">{{mb_value object=$operation field=_duree_sspi}}</td>
        </tr>

        {{if "dPsalleOp timings use_cleaning_timings"|gconf && $operation->_cleaning_time}}
          <tr>
            <th>{{mb_label object=$operation field=_cleaning_time}}</th>
            <td class="halfPane">{{mb_value object=$operation field=_cleaning_time}}</td>
          </tr>
        {{/if}}

        {{if $operation->_installation_time && "dPsalleOp timings use_debut_installation"|gconf
        && "dPsalleOp timings use_fin_installation"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=_installation_time}}</th>
            <td class="halfPane">{{mb_value object=$operation field=_installation_time}}</td>
          </tr>
        {{/if}}
      </table>
    </td>
  </tr>

  {{if is_array($operation->_ref_actes_ccam) && count($operation->_ref_actes_ccam)}}
    <tr>
      <td colspan="2">
        <table width="100%" style="border-spacing: 0px;font-size: 100%;">
          <tr>
            <th class="category" colspan="5">Actes CCAM</th>
          </tr>
          {{assign var="styleBorder" value="border: solid #aaa 1px;"}}
          <tr>
            <th style="{{$styleBorder}}text-align:left;">Code</th>
            <th style="{{$styleBorder}}text-align:left;">Exécutant</th>
            <th style="{{$styleBorder}}text-align:left;">Activité</th>
            <th style="{{$styleBorder}}text-align:left;">Phase &mdash; Modifs.</th>
            <th style="{{$styleBorder}}text-align:left;">Association</th>
          </tr>
          {{foreach from=$operation->_ref_actes_ccam item=currActe}}
            <tr>
              <td class="text" style="{{$styleBorder}}">
                <strong>{{$currActe->code_acte}}</strong><br/>
                {{$currActe->_ref_code_ccam->libelleLong}}
              </td>
              <td class="text" style="{{$styleBorder}}">{{$currActe->_ref_executant->_view}}</td>
              <td style="{{$styleBorder}}">{{$currActe->code_activite}}</td>
              <td style="{{$styleBorder}}">
                {{$currActe->code_phase}}
                {{if $currActe->modificateurs}}
                  &mdash; {{$currActe->modificateurs}}
                {{/if}}
              </td>
              <td style="{{$styleBorder}}">{{$currActe->code_association}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
  {{/if}}
</table>

<table class="print">
  <tr>
    <th class="category" colspan="4">{{tr}}COperation-Administration and operative event|pl{{/tr}}</th>
  </tr>
  {{foreach from=$perops key=datetime item=_perops_by_datetime}}
    {{foreach from=$_perops_by_datetime item=_perop}}
      <tr>
        <td style="text-align: center;">{{mb_ditto name=date value=$datetime|date_format:$conf.date}}</td>
        <td style="text-align: center;">{{mb_ditto name=time value=$datetime|date_format:$conf.time}}</td>
        {{if is_object($_perop)}}
          {{if $_perop|instanceof:'Ox\Mediboard\SalleOp\CAnesthPerop'}}
            {{assign var=perop_user value=$_perop->_ref_user}}
            <td colspan="2" class="text">
              <strong style="display: inline-block;">
                {{if $_perop->incident}}
                  Incident :
                {{/if}}

                {{$_perop->_view_completed}}
              </strong>

              {{if $_perop->commentaire}}
                : {{$_perop->commentaire}}
              {{/if}}

              {{if $perop_user && $perop_user->_id}}
                &mdash; {{$perop_user->_view}}
              {{/if}}
            </td>
          {{elseif $_perop|instanceof:'Ox\Mediboard\PlanSoins\CAdministration'}}
            {{assign var=unite         value=""}}
            {{assign var=adminstrateur value=$_perop->_ref_administrateur}}
            {{assign var=quantite      value=$_perop->quantite}}

            {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament' || $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMixItem'}}
              {{if $_perop->_unite_massique}}
                {{assign var=unite value=$_perop->_unite_massique}}
                {{assign var=quantite value=$_perop->_quantite_massique}}
              {{else}}
                {{assign var=unite value=$_perop->_ref_object->_unite_reference_libelle}}
              {{/if}}
            {{else}}
              {{assign var=unite value=$_perop->_ref_object->_unite_prise}}
            {{/if}}
            <td colspan="2" class="greedyPane">
              {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
                {{$_perop->_ref_object->_view}}
              {{else}}
                {{$_perop->_ref_object->_ucd_view}}
              {{/if}}
              <strong>{{$quantite}} {{$unite}}</strong>

              {{if $adminstrateur && $adminstrateur->_id}}
                &mdash; {{$adminstrateur->_view}}
              {{/if}}
            </td>
          {{elseif $_perop|instanceof:'Ox\Mediboard\Prescription\CAdministrationDM'}}
            {{assign var=praticien_dm value=$_perop->_ref_praticien}}
            <td>
              {{assign var=product   value=$_perop->_ref_product}}
              {{assign var=reception value=$_perop->_ref_product_order_item_reception}}

              {{$product}} ({{mb_label object=$product field=code}} : {{mb_value object=$product field=code}},
              {{mb_label object=$reception field=code}} : {{mb_value object=$reception field=code}})

              {{if $praticien_dm && $praticien_dm->_id}}
                &mdash; {{$praticien_dm->_view}}
              {{/if}}
            </td>
          {{elseif $_perop|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix'}}
            {{assign var=praticien_line_mix value=$_perop->_ref_praticien}}
            <td>
              {{if $datetime == $_perop->_pose}}
                Pose de la perfusion -
              {{else}}
                Retrait de la perfusion -
              {{/if}}
              {{$_perop->_short_view}}

              {{if $praticien_line_mix && $praticien_line_mix->_id}}
                &mdash; {{$praticien_line_mix->_view}}
              {{/if}}
            </td>
          {{/if}}
        {{else}}
          <td colspan="2" class="greedyPane">
            {{foreach from=$_perop key=type item=_constante}}
              {{if $_constante}}
                <strong>{{tr}}CConstantesMedicales-{{$type}}{{/tr}}:</strong>
                {{$_constante}}
                <br/>
              {{/if}}
            {{/foreach}}
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>

{{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
  {{foreach from=$supervision_data item=_data key=_type}}
    {{if is_array($_data.grid) && count($_data.grid)}}
      {{mb_include module=dPpatients template=inc_observation_results_grid
      in_compte_rendu=false
      print=true
      observation_labels=$_data.labels
      observation_grid=$_data.grid
      observation_list=$_data.list
      type=$_type
      }}
    {{/if}}
  {{/foreach}}

  {{if $surveillance && count($constantes) && count($constantes_names)}}
    {{assign var=constantes_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:'list_constantes'}}
    <table class="main tbl print">
      <tbody>
      {{foreach from=$constantes item=_constant}}
        <tr style="page-break-inside: avoid;">
          <th class="narrow">{{mb_value object=$_constant field=datetime}}</th>
          {{foreach from=$constantes_list key=_constant_name item=_params}}
            {{if in_array($_constant_name, $constantes_names)}}
              <td style="text-align: center;">
                {{if array_key_exists('formfields', $_params) && $_constant->$_constant_name}}
                  {{foreach from=$_params.formfields item=_formfield name=constant_formfields}}
                    {{if !$smarty.foreach.constant_formfields.first}}
                      /
                    {{/if}}
                    {{$_constant->$_formfield}}
                  {{/foreach}}
                {{else}}
                  {{$_constant->$_constant_name}}
                {{/if}}
              </td>
            {{/if}}
          {{/foreach}}
        </tr>
      {{/foreach}}
      </tbody>
      <thead>
      <tr>
        <th class="title">Données importées du concentrateur</th>
      </tr>
      <tr>
        <th style="text-align: center;">{{mb_title class=CConstantesMedicales field=datetime}}</th>
        {{foreach from=$constantes_list key=_constant_name item=_params}}
          {{if in_array($_constant_name, $constantes_names)}}
            <th style="text-align: center;">{{mb_title class=CConstantesMedicales field=$_constant_name}}</th>
          {{/if}}
        {{/foreach}}
      </tr>
      </thead>
    </table>
  {{/if}}
{{/if}}

<div id="constantes"></div>

{{if $operation->_back && array_key_exists("check_lists", $operation->_back) && $operation->_back.check_lists|@count}}
  <div>
    {{mb_include module=salleOp template=inc_vw_check_lists object=$operation}}
  </div>
{{/if}}

<div>
  {{mb_include module=planningOp template=inc_list_materiels_operation print=1 with_title=1 readonly=1}}
</div>

{{if "forms"|module_installed}}
  <table class="print">
    <tr>
      <th class="category">{{tr}}CExClass|pl{{/tr}}</th>
    </tr>
    <tr>
      <td id="ex_objects_list"></td>
    </tr>
  </table>
{{/if}}
