{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(
    function() {
      Facture.TdbCotation.controlCount();
    }
  );
</script>
<table class="tbl">
  <thead>
    <tr>
      <th class="narrow">
        <input id="tdb_cotation_all_check" type="checkbox" onclick="Facture.TdbCotation.allCheck(this)"/>
      </th>
      <th class="narrow"></th>
      <th>
        {{mb_label class=CConsultation field=_date}}
      </th>
      <th>
        {{mb_label class=CConsultation field=heure}}
      </th>
      <th>
        {{mb_label class=CPlageconsult field=chir_id}}
      </th>
      <th>
        {{mb_label class=CConsultation field=patient_id}}
      </th>
      <th>
        {{mb_label class=CPatient field=adresse}}
      </th>
    </tr>
  </thead>
  {{foreach from=$consultations item=_consultation name=tdb_cotation_consults}}
    {{if $smarty.foreach.tdb_cotation_consults.first}}
      <tr>
        <td colspan="4" id="tdb_cotation_multiple_cloture">
          {{mb_include module=facturation template="tdb_cotation/tdb_cotation_multiple_cloture"}}
        </td>
        <td colspan="3">
          {{mb_include module=system template=inc_pagination total=$consultations_count current=$page step=$consultations_par_page
                       change_page="Facture.TdbCotation.refreshList.curry(null).bind(Facture.TdbCotation)"}}
        </td>
      </tr>
    {{/if}}
    {{assign var=patient value=$_consultation->_ref_patient}}
    {{assign var=praticien value=$_consultation->_ref_praticien}}
    <tr>
      <td>
        <input class="tdb-cotation-check" type="checkbox" onclick="Facture.TdbCotation.checkLine(this)"
               data-consultation-id="{{$_consultation->_id}}"/>
      </td>
      <td>
        <button class="notext search me-secondary" onclick="Consultation.editModal('{{$_consultation->_id}}', 'facturation', '')">
          {{tr}}Show{{/tr}}
        </button>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}')">
          {{mb_value object=$_consultation field=_date}}
        </span>
      </td>
      <td>
        {{mb_value object=$_consultation field=heure}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}')">
          {{$praticien}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
          {{$_consultation->_ref_patient}}
          {{mb_value object=$patient field="naissance"}}
        </span>
      </td>
      <td class="text compact">
        <span style="white-space: nowrap;">{{$patient->adresse|spancate:30}}</span>
        <span style="white-space: nowrap;">{{$patient->cp}} {{$patient->ville|spancate:20}}</span>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">
        {{tr}}CConsultation.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
