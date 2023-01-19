{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

Main.add(function(){
  var url = new Url("patients", "httpreq_vw_constantes_medicales");
  url.addParam("patient_id", {{$operation->_ref_sejour->patient_id}});
  url.addParam("context_guid", "{{$operation->_ref_sejour->_guid}}");
  url.addParam("selection[]", ["pouls", "ta_gauche", "frequence_respiratoire", "score_sedation", "spo2", "diurese"]);
  url.addParam("date_min", "{{$operation->_datetime_reel}}");
  url.addParam("date_max", "{{$operation->_datetime_reel_fin}}");
  url.addParam("print", 1);
  url.requestUpdate("constantes");
});

</script>

{{assign var=sejour value=$operation->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=consult_anesth value=$operation->_ref_consult_anesth}}

<table class="tbl">
  <tr>
    <th class="title" onclick="window.print();" colspan="2">Fiche d'intervention anesthésie - {{$operation->_ref_sejour->_ref_patient->_view}}</th>
  </tr>
  <tr>
    <td><strong>Date de l'intervention</strong> {{mb_value object=$operation field=_datetime}}</td>
    <td><strong>Interventon réalisée</strong> {{mb_include module=planningOp template=inc_vw_operation _operation=$operation nodebug=true}}</td>
  </tr>
  <tr>
    <td><strong>{{mb_label object=$operation field=anesth_id}}</strong> {{mb_value object=$operation field=anesth_id}}</td>
    <td><strong>{{mb_label object=$operation field=chir_id}}</strong> {{mb_value object=$operation field=chir_id}}</td>
  </tr>
  <tr>
    <td><strong>{{mb_label object=$operation field=position_id}}</strong> {{mb_value object=$operation field=position_id}}</td>
    <td><strong>{{mb_label object=$operation field=type_anesth}}</strong> {{mb_value object=$operation field=type_anesth}}</td>
  </tr>
</table>

<table class="tbl">	
  <tr>
    <th colspan="4">Evenements per-opératoire</th>
  </tr>		
  {{foreach from=$perops key=datetime item=_perops_by_datetime}}
    {{foreach from=$_perops_by_datetime item=_perop}}
      <tr>
          <td style="text-align: center;" class="narrow">{{mb_ditto name=date value=$datetime|date_format:$conf.date}}</td>
          <td style="text-align: center;" class="narrow">{{mb_ditto name=time value=$datetime|date_format:$conf.time}}</td>
        {{if $_perop|instanceof:'Ox\Mediboard\SalleOp\CAnesthPerop'}}

          <td style="font-weight: bold;" colspan="2">{{$_perop->libelle}}</td>
        {{elseif $_perop|instanceof:'Ox\Mediboard\PlanSoins\CAdministration'}}
          {{assign var=unite value=""}}
          {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament' || $_perop->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMixItem'}}
            {{assign var=unite value=$_perop->_ref_object->_unite_reference_libelle}}
          {{/if}}

          <td colspan="2">
            {{if $_perop->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
              {{$_perop->_ref_object->_view}}
            {{else}}
              {{$_perop->_ref_object->_ucd_view}}
            {{/if}}
            <strong>{{$_perop->quantite}} {{$unite}} </strong>
          </td>
        {{else}}
          <td>
          {{foreach from=$_perop key=toto item=_constante}}
            {{if $_constante}}
            <strong>{{tr}}CConstantesMedicales-{{$toto}}{{/tr}}:</strong> {{$_constante}}<br />
            {{/if}}
          {{/foreach}}
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>

<div id="constantes"></div>
