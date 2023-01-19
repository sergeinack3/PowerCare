{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  @media print {
    div.modal_view {
      display: block !important;
      height: auto !important;
      width: 100% !important;
      font-size: 8pt !important;
      left: auto !important;
      top: auto !important;
      position: static !important;
    }

    table {
      width: 100% !important;
      font-size: inherit !important;
    }
  }
</style>

<script>
  // La div du dossier qui a été passé dans la fonction Modal.open()
  // a du style supplémentaire, qu'il faut écraser lors de l'impression
  // d'un dossier seul.
  printOneDossierLite = function(patient_id) {
    Element.print($("content_"+patient_id).childElements());
  };

  showLegend = function() {
    Modal.open("legend_plan_soins");
  };
</script>

{{assign var=print_gemsa value="dPurgences Print gemsa"|gconf}}

<table class="tbl">
  <thead>
    <tr>
      <th class="title" colspan="{{if $service_id == "urgence"}}8{{else}}7{{/if}}">
        <button type="button" class="not-printable print" style="float: right;" onclick="window.print()">{{tr}}Print{{/tr}}</button>
        {{if $plan_soins_active}}
          <button type="button" class="not-printable search" style="float: right;" onclick="showLegend();">{{tr}}Legend{{/tr}}</button>
        {{/if}}
        {{$date|date_format:$conf.date}} - {{if $service->_id}}{{$service}}{{else}}Non placés{{/if}} - {{$patients_offline|@count}} patient(s) - Imprimé le {{'Ox\Core\CMbDT::datetime'|static_call:null|date_format:$conf.datetime}}
      </th>
    </tr>

    {{if $service_id == "urgence"}}
      {{mb_include module=urgences template=inc_print_header_main_courante}}
    {{else}}
      <tr>
        <th>Patient</th>
        <th>Lit</th>
        <th>Prat.</th>
        <th>Motif</th>
        <th>Entrée prévue</th>
        <th>Sortie prévue</th>
        <th>J. opératoire <br /> Intervention</th>
      </tr>
    {{/if}}
  </thead>

  {{foreach from=$patients_offline item=_patient_data}}
    {{if $service_id == "urgence"}}
      {{assign var=sejour value=$_patient_data.sejour}}
      {{mb_include module=urgences template=inc_print_main_courante offline=0 offline_lite=1}}
    {{else}}
      {{assign var=sejour value=$_patient_data.sejour}}
      {{assign var=curr_aff value=$sejour->_ref_curr_affectation}}
      {{assign var=patient value=$sejour->_ref_patient}}
      {{assign var=curr_prat value=$sejour->_ref_praticien}}
      {{assign var=statut value=""}}

      {{if !$sejour->entree_reelle || ($sejour->_ref_prev_affectation->_id && $sejour->_ref_prev_affectation->effectue == 0)}}
        {{assign var=statut value="attente"}}
      {{/if}}

      {{if $sejour->sortie_reelle || $curr_aff->effectue == 1}}
        {{assign var=statut value="sorti"}}
      {{/if}}

      <tr>
        <td class="text" style="page-break-inside: avoid;">
          <button type="button" class="print compact notext not-printable" onclick="printOneDossierLite('{{$patient->_guid}}')">{{tr}}Print{{/tr}}</button>
          <button type="button" class="search compact notext not-printable" onclick="Modal.open('content_{{$patient->_guid}}', {showClose: true});">Voir le dossier</button>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')"
                class="{{if $statut == "attente"}}patient-not-arrived{{/if}}"
                style="{{if $statut == "sorti"}}background-image:url(images/icons/ray.gif); background-repeat:repeat;{{/if}}
                       {{if $statut == "attente"}}font-style: italic;{{/if}}">
            {{$patient}}
          </span>
        </td>
        <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_aff->_guid}}')">
            {{$curr_aff->_ref_lit->nom}}
          </span>
        </td>
        <td class="text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_prat}}</td>
        <td class="text">{{mb_value object=$sejour field=libelle}}</td>
        <td class="text">{{mb_value object=$sejour field=entree}}</td>
        <td class="text">{{mb_value object=$sejour field=sortie}}</td>
        <td class="text">
          {{assign var=nb_days_hide_op value="soins dossier_soins nb_days_hide_op"|gconf}}
          {{foreach from=$sejour->_jour_op item=_jour_op key=op_id name=jour_op}}
            {{if $nb_days_hide_op == 0 || $nb_days_hide_op > $_jour_op.jour_op}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_jour_op.operation_guid}}')">{{$sejour->_ref_operations.$op_id->libelle}} (J{{$_jour_op.jour_op}})</span>
              {{if !$smarty.foreach.jour_op.last}}&mdash;{{/if}}
            {{/if}}
          {{/foreach}}
        </td>
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr>
      <td colspan="{{if $service_id == "urgence"}}8{{else}}7{{/if}}" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{if $plan_soins_active}}
  {{mb_include module=planSoins template=inc_legend_offline_plan_soins}}
{{/if}}

{{foreach from=$patients_offline item=_patient_data key=patient_guid name=patients}}
  {{assign var=sejour value=$_patient_data.sejour}}
  {{assign var=patient value=$sejour->_ref_patient}}

  <div id="content_{{$patient_guid}}" style="display: none;" class="modal_view">
    {{* Plan de soins *}}
    {{if $plan_soins_active}}
      {{$_patient_data.plan_soins|smarty:nodefaults}}
    {{/if}}

    {{* Transmissions *}}
    {{if $_patient_data.transmissions|@count}}
      <table class="tbl">
        <thead>
          <tr>
            <th class="title" colspan="9">
              Transmissions - {{$patient}}
            </th>
          </tr>
          <tr>
            <th rowspan="2">{{tr}}Type{{/tr}}</th>
            <th rowspan="2">{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
            <th rowspan="2">{{mb_title class=CTransmissionMedicale field=object_class}}</th>
            <th colspan="3" style="width: 50%">{{mb_title class=CTransmissionMedicale field=text}}</th>
          </tr>
          <tr>
            <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.data{{/tr}}</th>
            <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.action{{/tr}}</th>
            <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.result{{/tr}}</th>
          </tr>
        </thead>
        {{foreach from=$_patient_data.transmissions item=_suivi}}
          <tr>
            {{mb_include module=hospi template=inc_line_suivi readonly=1}}
          </tr>
        {{/foreach}}
      </table>
    {{/if}}

    {{* Observations *}}
    {{if $_patient_data.observations|@count}}
      <br />
      <table class="tbl">
        <thead>
          <tr>
            <th class="title" colspan="7">
              Observations - {{$patient}}
            </th>
          </tr>
          <tr>
            <th>{{tr}}Type{{/tr}}</th>
            <th>{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
            <th>{{mb_title class=CTransmissionMedicale field=object_class}}</th>
            <th colspan="4" style="width: 50%">{{mb_title class=CTransmissionMedicale field=text}}</th>
          </tr>
        </thead>

        {{foreach from=$_patient_data.observations item=_suivi}}
          <tr>
            {{mb_include module=hospi template=inc_line_suivi readonly=1}}
          </tr>
        {{/foreach}}
      </table>
    {{/if}}

    {{* Consultations *}}
    {{if $_patient_data.consultations|@count}}
      <br />
      <table class="tbl">
        <thead>
          <tr>
            <th class="title" colspan="7">
              Consultations - {{$patient}}
            </th>
          </tr>
          <tr>
            <th>{{tr}}Type{{/tr}}</th>
            <th>{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
            <th>{{mb_title class=CTransmissionMedicale field=object_class}}</th>
            <th colspan="4" style="width: 50%">{{mb_title class=CTransmissionMedicale field=text}}</th>
          </tr>
        </thead>
        {{foreach from=$_patient_data.consultations item=_suivi}}
          <tr>
            {{mb_include module=hospi template=inc_line_suivi readonly=1}}
          </tr>
        {{/foreach}}
      </table>
    {{/if}}

    {{* Constantes *}}
    {{if $_patient_data.constantes}}
      <br />
      {{$_patient_data.constantes|smarty:nodefaults}}
    {{/if}}
  </div>

  {{if !$smarty.foreach.patients.last}}
    <hr style="border: 0; page-break-after: always;" />
  {{/if}}
{{/foreach}}
