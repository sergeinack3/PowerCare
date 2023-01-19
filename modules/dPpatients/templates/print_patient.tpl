{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  //Main.add(window.print);

  var lists = {
    sejour:             {
      labels: ["Dernier séjour", "Séjours"],
      all:    true
    },
    consultation:       {
      labels: ["Dernière consultation", "Consultations"],
      all:    true
    },
    sejour_futur:       {see: true},
    consultation_futur: {see: true}
  };

  function toggleList(list, button) {
    var lines = $$('.' + list),
      data = lists[list];

    lines.invoke('toggle');
    lines.first().show();
    data.all = !data.all;
    button.up().select('span')[0].update(data.labels[data.all ? 1 : 0]);
  }

  function viewFutur(list) {
    var lines = $$('.' + list);
    lines.invoke("show");
    var futur = lists[list + '_futur'];
    if (futur.see == true) {
      lines.each(function (e) {
        if (!e.hasClassName(list + '_futur')) {
          e.hide();
        }
      });
      futur.see = false;
    } else {
      futur.see = true;
    }
  }
</script>

<table class="print">
  <tr>
    <th class="title" colspan="5"><a href="#" onclick="window.print()">Fiche Patient &mdash; le {{$dnow|date_format:$conf.date}}</a>
    </th>
  </tr>
  <tr>
    <td colspan="2" style="width: 50%;"></td>
    <td colspan="2" style="width: 50%;"></td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=nom}} / {{mb_label object=$patient field=prenom}}</th>
    <td colspan="3">
      <strong>{{$patient->_view}}</strong> {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP hide_empty=true}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=naissance}} / {{mb_label object=$patient field=sexe}}</th>
    <td>
      né(e) le {{mb_value object=$patient field=naissance}} ({{mb_value object=$patient field=_age}}) <br />
      de sexe {{if $patient->sexe == "m"}} masculin {{else}} féminin {{/if}}
    </td>
    <th>{{mb_label object=$patient field=lieu_naissance}}</th>
    <td>
      {{mb_value object=$patient field=cp_naissance}}
      {{mb_value object=$patient field=lieu_naissance}} <br />
      {{mb_value object=$patient field=_pays_naissance_insee}}
    </td>
      {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
        <td style="margin-top: 4em;" class="title" rowspan="7">
            {{mb_include module=dPpatients template=vw_datamatrix_ins}}
        </td>
      {{/if}}
  </tr>
  <tr>
    {{if $conf.ref_pays == 1}}
      {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
        <th>{{tr}}CINSPatient{{/tr}}</th>
        <td>{{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})</td>
      {{else}}
        <th>{{mb_label object=$patient field=matricule}}</th>
        <td>{{mb_value object=$patient field=matricule}}</td>
      {{/if}}
    {{else}}
      <th>{{mb_label object=$patient field=avs}}</th>
      <td>{{mb_value object=$patient field=avs}}</td>
    {{/if}}
    <th>{{mb_label object=$patient field=profession}}</th>
    <td>{{mb_value object=$patient field=profession}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=tel}}</th>
    <td>{{mb_value object=$patient field=tel}}</td>
    <th>{{mb_label object=$patient field=tel2}}</th>
    <td>{{mb_value object=$patient field=tel2}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=email}}</th>
    <td>{{mb_value object=$patient field=email}}</td>
  </tr>
  <tr>
  </tr>
  {{if $patient->tel_autre}}
    <tr>
      <th>{{mb_label object=$patient field=tel_autre}}</th>
      <td>{{mb_value object=$patient field=tel_autre}}</td>
    </tr>
    </tr>
  {{/if}}
  <tr>
    <th>{{mb_label object=$patient field=adresse}}</th>
    <td>
      {{$patient->adresse|nl2br}} <br />
      {{$patient->cp}} {{$patient->ville}}
    </td>
    <th>{{mb_label object=$patient field=incapable_majeur}}</th>
    <td>{{mb_value object=$patient field=incapable_majeur}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=rques}}</th>
    <td colspan="3">{{$patient->rques|nl2br}}</td>
  </tr>
  <tr>
    <th class="category" colspan="5">Correspondants</th>
  </tr>
  <tr>
    <td colspan="2" class="halfPane">
      <table>
        <tr>
          <th>Médecin traitant</th>
          <td>
            {{if $patient->_ref_medecin_traitant->_id}}
              {{$patient->_ref_medecin_traitant->_view}}
              <br />
              {{$patient->_ref_medecin_traitant->adresse|nl2br}}
              <br />
              {{$patient->_ref_medecin_traitant->cp}} {{$patient->_ref_medecin_traitant->ville}}
            {{else}}
              Non renseigné
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>Corresp. médicaux</th>
          <td>
            {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp}}
              <div style="float: left; margin-right: 1em; margin-bottom: 0.5em; margin-top: 0.4em; width: 15em;">
                {{$curr_corresp->_ref_medecin->_view}}<br />
                {{$curr_corresp->_ref_medecin->adresse|nl2br}}<br />
                {{$curr_corresp->_ref_medecin->cp}} {{$curr_corresp->_ref_medecin->ville}}
              </div>
              {{foreachelse}}
              Non renseigné
            {{/foreach}}
          </td>
        </tr>
      </table>
    </td>
    <td colspan="2">
      <table>
        {{foreach from=$patient->_ref_correspondants_patient item=curr_corresp}}
          <tr>
            <th>{{mb_value object=$curr_corresp field=relation}}</th>
            <td>
              {{$curr_corresp->nom}} {{$curr_corresp->prenom}}<br />
              {{$curr_corresp->adresse|nl2br}}<br />
              {{$curr_corresp->cp}} {{$curr_corresp->ville}}
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
  {{if $patient->_ref_sejours|@count}}
    <tr>
      <th class="category" colspan="5">
        <button class="change not-printable" style="float:right;" onclick="toggleList('sejour', this)">Seulement le dernier</button>
        <button class="change not-printable" style="float:right;" onclick="viewFutur('sejour')">Seulement à venir</button>
        <span>Séjours</span>
      </th>
    </tr>
    {{foreach from=$patient->_ref_sejours item=curr_sejour}}
      <tr class="sejour {{if $curr_sejour->entree_prevue > $dtnow}}sejour_futur{{/if}}">
        <th class="text">{{$curr_sejour->_ref_praticien->_view}}</th>
        <td colspan="3">
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$curr_sejour}}
          Du {{$curr_sejour->entree_prevue|date_format:$conf.date}}
          au {{$curr_sejour->sortie_prevue|date_format:$conf.date}}
          - ({{mb_value object=$curr_sejour field=type}})
          <ul>
            {{foreach from=$curr_sejour->_ref_operations item="curr_op"}}
              <li>
                {{tr}}dPplanningOp-COperation of{{/tr}} {{$curr_op->_datetime|date_format:$conf.date}}
                ({{$curr_op->_ref_chir->_view}})
              </li>
              {{foreachelse}}
              <li class="empty">Pas d'interventions</li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
  {{if $patient->_ref_consultations|@count}}
    <tr>
      <th class="category" colspan="5">
        <button class="change not-printable" style="float:right;" onclick="toggleList('consultation', this)">Seulement la dernière
        </button>
        <button class="change not-printable" style="float:right;" onclick="viewFutur('consultation')">Seulement à venir</button>
        <span>Consultations</span>
      </th>
    </tr>
    {{foreach from=$patient->_ref_consultations item=curr_consult}}
      <tr class="consultation {{if $curr_consult->_date > $dnow}}consultation_futur{{/if}}">
        <th class="text">{{$curr_consult->_ref_plageconsult->_ref_chir->_view}}</th>
        <td colspan="3">le {{mb_value object=$curr_consult->_ref_plageconsult field=date}}
          à {{mb_value object=$curr_consult field=heure}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
