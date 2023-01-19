{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display value=false}}

<table class="{{$tbl_class}}" style="height: 125px;">
  <tr>
    <td>
      <table width="100%">
        <tr>
          <th class="title" colspan="5">
            {{if $offline && !$display}}
              <button type="button" style="float: right;" class="cancel not-printable" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
            {{/if}}
            {{if $display}}
              Consultation préanesthésique
            {{else}}
              <a href="#" onclick="{{if $offline && !$multi}}$('fiche_anesth_{{$operation->_id}}'){{else}}window{{/if}}.print()">
                Consultation préanesthésique
              </a>
            {{/if}}
          </th>
        </tr>
        <tr>
          <th>{{tr}}Date{{/tr}}</th>
          <td style="font-size: 1.3em;">{{$consult->_ref_plageconsult->date|date_format:$conf.longdate}}</td>
          <th>Anesthésiste </th>
          <td style="font-size: 1.3em;">
            {{if $dossier_anesth->_ref_chir->isPraticien()}}Dr{{/if}} {{$consult->_ref_chir->_view}}
          </td>
          {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
            <td rowspan="3">
                {{mb_include module=dPpatients template=vw_datamatrix_ins}}
            </td>
          {{/if}}
        </tr>
        <tr>
          <th>{{tr}}CPatient{{/tr}}</th>
          <td style="font-size: 1.3em;">
            {{$patient->_view}}
            {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          </td>
          {{if $operation->_id}}
            <th>{{mb_label object=$dossier_anesth->_ref_operation field=chir_id}}</th>
            <td style="font-size: 1.3em;">{{mb_value object=$operation field=chir_id}}</td>
          {{else}}
            <th>{{mb_label object=$dossier_anesth field=chir_id}}</th>
            <td style="font-size: 1.3em;">{{mb_value object=$dossier_anesth field=chir_id}}</td>
          {{/if}}
        </tr>
          {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
            <tr>
              <th>{{tr}}CINSPatient{{/tr}}</th>
              <td>
                  {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
              </td>
            </tr>
          {{/if}}
        <tr>
          <th>{{tr}}COperation{{/tr}}</th>
          <td colspan="3" style="font-size: 1.3em;">
            {{if $operation->_id}}
              le {{$operation->_datetime_best|date_format:"%A %d/%m/%Y"}}
              {{if $operation->libelle}}
                - {{$operation->libelle}}
              {{/if}}
            {{else}}
              le {{$dossier_anesth->date_interv|date_format:"%A %d/%m/%Y"}}
              {{if $dossier_anesth->libelle_interv}}
                - {{$dossier_anesth->libelle_interv}}
              {{/if}}
            {{/if}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
