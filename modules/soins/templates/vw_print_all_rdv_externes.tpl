{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $rdv_by_services|@count}}
  <script>
    Main.add(function () {
      window.print();
    });
  </script>
{{/if}}

{{foreach from=$rdv_by_services key=service_id item=rdv_datas name=list_rdv}}
    {{assign var=service_view value=$rdv_by_services.$service_id.affectation->_ref_service->_view}}
    {{assign var=patients     value=$rdv_by_services.$service_id.patients}}
  <table class="main tbl">
      {{if $smarty.foreach.list_rdv.first}}
        <tr>
          <th class="title" colspan="8">
            <button type="button" class="print not-printable me-primary me-float-right" onclick="window.print();" style="float: left;">{{tr}}Print{{/tr}}</button>

            {{tr var1=$date_min|date_format:$conf.date var2=$date_max|date_format:$conf.date }}
              CRDVExterne-List of external appointments for the period %s of %s
            {{/tr}}
          </th>
        </tr>
      {{/if}}
    <tr>
      <th class="button category" colspan="8">
          {{tr var1=$service_view}}CRDVExterne-Service %s{{/tr}}
      </th>
    </tr>
      {{foreach from=$patients key=patient_id item=patient}}
          {{assign var=object_patient value=$patient.patient}}
          {{assign var=patient_rdvs   value=$patient.rdvs}}
        <tr>
          <td colspan="8" style="background-color: white;">

            <strong class="fas fa-user" onmouseover="ObjectTooltip.createEx(this, '{{$object_patient->_guid}}')"
                    style="font-size: larger">
              <span class="CPatient-view">{{$object_patient}}</span>

                {{mb_include module=patients patient=$object_patient template=inc_icon_bmr_bhre}}
            </strong>
            {{mb_include module=patients template=inc_vw_ipp ipp=$object_patient->_IPP}}
            {{$object_patient->_age}}
          </td>
        </tr>
        <tr>
          <th class="narrow">{{mb_title class=CRDVExterne field=sejour_id}}</th>
          <th class="narrow">{{tr}}CChambre{{/tr}}</th>
          <th class="narrow">{{mb_title class=CRDVExterne field=libelle}}</th>
          <th class="text">{{mb_title class=CRDVExterne field=description}}</th>
          <th class="narrow">{{mb_title class=CRDVExterne field=date_debut}}</th>
          <th class="narrow">{{mb_title class=CRDVExterne field=duree}}</th>
          <th class="narrow">{{mb_title class=CRDVExterne field=statut}}</th>
          <th class="text">{{mb_title class=CRDVExterne field=commentaire}}</th>
        </tr>
          {{foreach from=$patient_rdvs item=_rdv}}
              {{assign var=sejour value=$_rdv->_ref_sejour}}
              {{assign var=lit value=$sejour->_ref_last_affectation->_ref_lit}}
            <tr>
              <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
              {{$sejour->_shortview}}
            </span>
              </td>
              <td>{{$lit->_view}}</td>
              <td>{{mb_value object=$_rdv field=libelle}}</td>
              <td>{{mb_value object=$_rdv field=description}}</td>
              <td>{{mb_value object=$_rdv field=date_debut}}</td>
              <td>{{mb_value object=$_rdv field=duree}}</td>
              <td>{{mb_value object=$_rdv field=statut}}</td>
              <td>{{mb_value object=$_rdv field=commentaire}}</td>
            </tr>
          {{/foreach}}
      {{/foreach}}
  </table>
  {{foreachelse}}
    <table class="tbl">
      <tr>
        <th class="title" colspan="8">
          {{tr var1=$date_min|date_format:$conf.date var2=$date_max|date_format:$conf.date }}
            CRDVExterne-List of external appointments for the period %s of %s
          {{/tr}}
        </th>
      </tr>
      <tr>
        <td class="empty">
            {{tr}}CRDVExterne.none{{/tr}}
        </td>
      </tr>
    </table>
{{/foreach}}
