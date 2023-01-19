{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="print">
  <tr>
    <th class="title" colspan="3">
      <span style="float:right;font-size:12px;">
        
      </span>
      <a href="#" onclick="window.print()">Fiche de consultation</a>
    </th>
  </tr>
  <tr>
    <th>{{if $prat->isPraticien()}}Praticien{{else}}Intervenant{{/if}}</th>
    <td>{{if $prat->isPraticien()}}Dr{{/if}} {{$prat->_view}}</td>
  </tr>
  {{if $prat->discipline_id}}
  <tr>
    <th>Spécialité</th>
    <td>{{$prat->_ref_discipline->_view}}</td>
  </tr>
  {{/if}}

  <tr>
    <th class="category" colspan="3">Rendez-vous {{if $consultations|@count}}principal{{/if}}</th>
  </tr>
  <tr>
    <th>Le</th>
    <td>
      {{$consultation->_ref_plageconsult->date|date_format:$conf.longdate}}
      à {{$consultation->heure|date_format:$conf.time}}
    </td>
  </tr>
  <tr>
  <th>Adresse</th>
    <td>
      {{mb_value object=$prat->_ref_function field=adresse}} <br />
      {{mb_value object=$prat->_ref_function field=cp}} {{mb_value object=$prat->_ref_function field=ville}}
    </td>
  </tr>
  <tr>
    <th>Tel</th>
    <td>{{mb_value object=$prat->_ref_function field=tel}}</td>
  </tr>
  <tr>
    <th>Fax</th>
    <td>{{mb_value object=$prat->_ref_function field=fax}}</td>
  </tr>
  {{if $consultation->docs_necessaires}}
  <tr>
    <th>{{mb_label object=$consultation field=docs_necessaires}}</th>
    <td>{{mb_value object=$consultation field=docs_necessaires}}</td>
  </tr>
  {{/if}}
  {{if $consultations|@count}}
    <tr>
      <th class="category" colspan="3">{{$consultations|@count}} Rendez-vous suivants </th>
    </tr>
    {{foreach from=$consultations item=_consult}}
      <tr>
        <th>{{$_consult->_ref_plageconsult->date|date_format:$conf.longdate}}</th>
        <td>{{$_consult->heure}} avec
          {{if $_consult->_ref_plageconsult->chir_id}}
            {{$_consult->_ref_plageconsult->_ref_chir}}
          {{else}}
            {{$_consult->_ref_plageconsult->_ref_remplacant}}
          {{/if}}</td>
      </tr>
    {{/foreach}}
  {{/if}}

  <tr>
    <th class="category" colspan="3">Fiche Patient</th>
  </tr>
  <tr>
    <th>Nom / Prénom</th>
    <td>{{$patient->_view}}</td>
    {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
      <td style="margin-top: 4em;" class="title" rowspan="6">
          {{mb_include module=dPpatients template=vw_datamatrix_ins}}
      </td>
    {{/if}}
  </tr>
  <tr>
    <th>Date de naissance / Sexe</th>
    <td>
      né(e) le {{mb_value object=$patient field="naissance"}}
      de sexe {{mb_value object=$patient field="sexe"}}
    </td>
  </tr>
  <tr>
    <th>Adresse</th>
    <td>
      {{mb_value object=$patient field=adresse}} <br />
      {{mb_value object=$patient field=cp}} {{mb_value object=$patient field=ville}}
    </td>
  </tr>
  <tr>
    <th>Téléphone</th>
    <td>{{mb_value object=$patient field=tel}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=tel2}}</th>
    <td>{{mb_value object=$patient field=tel2}}</td>
  </tr>
  <tr>
    <th>Medecin traitant</th>
    <td>{{$patient->_ref_medecin_traitant->_view}}</td>
  </tr>
  {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
    <tr>
      <th>{{tr}}CINSPatient{{/tr}}</th>
      <td>
          {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
      </td>
    </tr>
  {{/if}}
</table>
