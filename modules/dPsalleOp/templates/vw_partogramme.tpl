{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include style=mediboard_ext template=open_printable}}

{{assign var=sejour         value=$operation->_ref_sejour}}
{{assign var=patient        value=$sejour->_ref_patient}}
{{assign var=consult_anesth value=$operation->_ref_consult_anesth}}

{{mb_script module=monitoringPatient script=surveillance_perop}}
{{mb_script module=monitoringPatient script=surveillance_timeline      ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline_item ajax=1}}
{{mb_script module=monitoringPatient script=supervision_graph_defaults}}

{{if "patientMonitoring"|module_active}}
  {{mb_include module=patientMonitoring template=inc_concentrator_js    ajax=true}}
  {{mb_include module=patientMonitoring template=inc_concentrator_js_v2 ajax=true}}
{{/if}}

<script>
  resizeGraphs = (print = true) => {
    $$('div.graph-placeholder, div.timeline-item').each((div) => {
      div.setStyle({width: print ? '1000px' : '100%'});
    });
  };

  printSurveillance = () => {
    resizeGraphs();
    (() => { window.print(); resizeGraphs(false); }).delay(0.8);
  };
</script>

<style>
  @media print {
    @page {
      size: landscape;
    }
  }
</style>

<table class="print">
  <tr>
    <th class="title">
      <a href="#" onclick="printSurveillance();" style="font-size: 1.3em;">
        {{tr}}CPatient.surveillance{{/tr}}
      </a>
      <button class="print not-printable" style="float: right;" onclick="printSurveillance();">{{tr}}Print{{/tr}}</button>
    </th>
  </tr>
</table>

<table class="print">
  <tr>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="3">
            {{tr}}CPatient-Patient information{{if $patient->sexe == "f"}}|f{{/if}}{{/tr}}
          </th>
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
          <th class="category" colspan="2">{{tr}}COperation{{/tr}}</th>
        </tr>

        <tr>
          <th>{{mb_label object=$operation->_ref_plageop field=date}}</th>
          <td class="greedyPane">
            {{mb_value object=$operation->_ref_plageop field=date}}
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

        <tr>
          <th>{{mb_label object=$operation field=type_anesth}}</th>
          <td class="text">
            {{if $operation->type_anesth}}
              {{mb_value object=$operation field=type_anesth}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CPersonnel{{/tr}}</th>
          <td class="text">
            {{tr}}common-Not available{{/tr}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}COperation-materiel-court{{/tr}}</th>
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
          <th>{{tr}}common-Notice{{/tr}}</th>
          <td class="text">
            {{if $operation->rques}}
              {{mb_value object=$operation field=rques}}
            {{else}}
              &mdash;
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category" colspan="2">{{tr}}CSejourTimeline-title-VisiteAnesth{{/tr}}</th>
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
            <td colspan="2">{{tr}}common-Not entered{{/tr}}</td>
          </tr>
        {{/if}}
      </table>

    </td>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="2">{{tr}}common-Hourly{{/tr}}</th>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=debut_prepa_preop}}</th>
          <td class="halfPane">{{mb_value object=$operation field=debut_prepa_preop}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=fin_prepa_preop}}</th>
          <td class="halfPane">{{mb_value object=$operation field=fin_prepa_preop}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$operation field=entree_salle}}</th>
          <td class="halfPane">{{mb_value object=$operation field=entree_salle}}</td>
        </tr>

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

        <tr>
          <th>{{mb_label object=$operation field=induction_debut}}</th>
          <td class="halfPane">{{mb_value object=$operation field=induction_debut}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$operation field=induction_fin}}</th>
          <td class="halfPane">{{mb_value object=$operation field=induction_fin}}</td>
        </tr>

        {{if "dPsalleOp timings use_garrot"|gconf && !'dPsalleOp COperation garrots_multiples'|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=pose_garrot}}</th>
            <td class="halfPane">{{mb_value object=$operation field=pose_garrot}}</td>
          </tr>
        {{/if}}

        <tr>
          <th>{{mb_label object=$operation field=debut_op}}</th>
          <td class="halfPane">{{mb_value object=$operation field=debut_op}}</td>
        </tr>

        {{if "dPsalleOp timings use_incision"|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=incision}}</th>
            <td class="halfPane">{{mb_value object=$operation field=incision}}</td>
          </tr>
        {{/if}}

        <tr>
          <th>{{mb_label object=$operation field=fin_op}}</th>
          <td class="halfPane">{{mb_value object=$operation field=fin_op}}</td>
        </tr>

        {{if "dPsalleOp timings use_garrot"|gconf && !'dPsalleOp COperation garrots_multiples'|gconf}}
          <tr>
            <th>{{mb_label object=$operation field=retrait_garrot}}</th>
            <td class="halfPane">{{mb_value object=$operation field=retrait_garrot}}</td>
          </tr>
        {{/if}}

        <tr>
          <th>{{mb_label object=$operation field=sortie_salle}}</th>
          <td class="halfPane">{{mb_value object=$operation field=sortie_salle}}</td>
        </tr>

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

        <tr>
          <th>{{mb_label object=$operation field=entree_reveil}}</th>
          <td class="halfPane">{{mb_value object=$operation field=entree_reveil}}</td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$operation field=sortie_reveil_possible}}
          </th>
          <td class="halfPane">
            {{mb_ternary var=time_sortie_reveil test=$operation->sortie_reveil_reel value=$operation->sortie_reveil_reel other=$operation->sortie_reveil_possible}}
            {{$time_sortie_reveil|date_format:$conf.time}}
          </td>
        </tr>

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
          <th class="category" colspan="2">{{tr}}common-Duration|pl{{/tr}}</th>
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

  <tr>
    <td colspan="2">
      <table width="100%" style="border-spacing: 0px;font-size: 100%;">
        <tr>
          <th class="category" colspan="5">{{tr}}CActeCCAM{{/tr}}</th>
        </tr>
        {{assign var="styleBorder" value="border: solid #aaa 1px;"}}
        <tr>
          <th style="{{$styleBorder}}text-align:left;">{{tr}}CFavoriCCAM-favoris_code{{/tr}}</th>
          <th style="{{$styleBorder}}text-align:left;">{{tr}}CActeCCAM-executant_id{{/tr}}</th>
          <th style="{{$styleBorder}}text-align:left;">{{tr}}CCodageCCAM-activite_anesth{{/tr}}</th>
          <th style="{{$styleBorder}}text-align:left;">{{tr}}CCodageCCAM-Phase and modify-court{{/tr}}.</th>
          <th style="{{$styleBorder}}text-align:left;">{{tr}}CCodageCCAM-Association{{/tr}}</th>
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
            <td style="{{$styleBorder}}">{{$currActe->_guess_association}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>

  {{if $sejour->grossesse_id}}
    <tr>
      <th colspan="2" class="category">
        {{tr}}CGrossesse{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour->_ref_grossesse field=terme_prevu}}</th>
      <td>{{mb_value object=$sejour->_ref_grossesse field=terme_prevu}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour->_ref_grossesse field=rques}}</th>
      <td>{{mb_value object=$sejour->_ref_grossesse field=rques}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour->_ref_grossesse field=datetime_debut_travail}}</th>
      <td>{{mb_value object=$sejour->_ref_grossesse field=datetime_debut_travail}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour->_ref_grossesse field=datetime_accouchement}}</th>
      <td>{{mb_value object=$sejour->_ref_grossesse field=datetime_accouchement}}</td>
    </tr>
  {{/if}}
</table>

<table class="print">
  <tr>
    <th class="title">
        {{tr}}CSupervisionGraph|pl{{/tr}}
    </th>
  </tr>
</table>

<div id="container_supervision">
  {{mb_include module=salleOp template=vw_print_supervision}}
</div>

{{mb_include style=mediboard_ext template=close_printable}}
