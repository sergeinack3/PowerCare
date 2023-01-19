{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  //Main.add(window.print);

  var lists = {
    sejour:       {
      labels: ["Dernier séjour", "Séjours"],
      all:    true
    },
    consultation: {
      labels: ["Dernière consultation", "Consultations"],
      all:    true
    }
  };

  function toggleList(list, button) {
    var lines = $$('.' + list),
      data = lists[list];

    lines.invoke('toggle');
    lines.first().show();
    data.all = !data.all;
    button.up().select('span')[0].update(data.labels[data.all ? 1 : 0]);
  }
</script>

<button class="print not-printable" onclick="window.print()">{{tr}}Print{{/tr}}</button>

<table class="print">
  <tr>
    <th class="title" colspan="10">{{tr}}CCorrespondantPatient-Patient card{{/tr}} ({{$dnow|date_format:$conf.date}})</th>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field=nom}} - {{mb_label object=$patient field=prenom}}</th>
    <td><strong>{{$patient->_view}}</strong> {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP hide_empty=true}}</td>
    <th rowspan="2">{{mb_label object=$patient field=adresse}}</th>
    <td rowspan="2">{{$patient->adresse|nl2br}} <br /> {{$patient->cp}} {{$patient->ville}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=naissance}} - {{mb_label object=$patient field=sexe}}</th>
    <td>{{tr var1=$patient->naissance|date_format:$conf.date}}CPatient-Born on %s{{/tr}} ({{mb_value object=$patient field=_age}})
      <br />
      {{tr}}CCorrespondantPatient-of sex{{/tr}} {{tr}}{{if $patient->sexe == "m"}}CCorrespondantPatient-male{{else}}CCorrespondantPatient-female{{/if}}{{/tr}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=incapable_majeur}}</th>
    <td>{{mb_value object=$patient field=incapable_majeur}}</td>

    <th rowspan="3">{{mb_label object=$patient field=rques}}</th>
    <td rowspan="3">{{mb_value object=$patient field=rques}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=tel}}</th>
    <td>{{mb_value object=$patient field=tel}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=tel2}}</th>
    <td>{{mb_value object=$patient field=tel2}}</td>
  </tr>
  {{if $patient->tel_autre}}
    <tr>
      <th>{{mb_label object=$patient field=tel_autre}}</th>
      <td>{{mb_value object=$patient field=tel_autre}}</td>
    </tr>
  {{/if}}

  <tr>
    <th class="category" colspan="10">{{tr}}CPatient-part-beneficiaire-soins{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="code_regime"}}</th>
    <td>{{mb_value object=$patient field="code_regime"}}</td>

    <th>{{mb_label object=$patient field="ald"}}</th>
    <td>{{mb_value object=$patient field="ald"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field="caisse_gest"}}</th>
    <td>{{mb_value object=$patient field="caisse_gest"}}</td>

    <th>{{mb_label object=$patient field="incapable_majeur"}}</th>
    <td>{{mb_value object=$patient field="incapable_majeur"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field="centre_gest"}}</th>
    <td>{{mb_value object=$patient field="centre_gest"}}</td>

    <th>{{mb_label object=$patient field="c2s"}}</th>
    <td>{{mb_value object=$patient field="c2s"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field="regime_sante"}}</th>
    <td>{{mb_value object=$patient field="regime_sante"}}</td>

    <th>{{mb_label object=$patient field="ATNC"}}</th>
    <td>{{mb_value object=$patient field="ATNC"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="deb_amo"}}</th>
    <td>{{mb_value object=$patient field="deb_amo"}}</td>

    <th>{{mb_label object=$patient field="fin_validite_vitale"}}</th>
    <td>{{mb_value object=$patient field="fin_validite_vitale"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="fin_amo"}}</th>
    <td>{{mb_value object=$patient field="fin_amo"}}</td>

    <th rowspan="2">{{mb_label object=$patient field="notes_amo"}}</th>
    <td rowspan="2">{{mb_value object=$patient field="notes_amo"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="code_exo"}}</th>
    <td>{{mb_value object=$patient field="code_exo"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="code_sit"}}</th>
    <td>{{mb_value object=$patient field="code_sit"}}</td>

    <th rowspan="2">{{mb_label object=$patient field="libelle_exo"}}</th>
    <td rowspan="2">{{mb_value object=$patient field="libelle_exo"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="regime_am"}}</th>
    <td>{{mb_value object=$patient field="regime_am"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field="notes_amc"}}</th>
    <td>{{mb_value object=$patient field="notes_amc"}}</td>

    <th>{{mb_label object=$patient field=ame}}</th>
    <td>{{mb_value object=$patient field=ame}}</td>
  </tr>

  {{if $patient->_ref_medecin_traitant->medecin_id || $patient->_ref_medecins_correspondants|@count}}
    <tr>
      <th class="category" colspan="10">{{tr}}CPatient-part-correspondants-medicaux{{/tr}}</th>
    </tr>
    <tr>
      {{if $patient->_ref_medecin_traitant->medecin_id}}
        <th>{{tr}}CDossierMedical-medecin_traitant_id{{/tr}}:</th>
        <td>
          {{$patient->_ref_medecin_traitant->_view}}<br />
          {{$patient->_ref_medecin_traitant->adresse|nl2br}}<br />
          {{$patient->_ref_medecin_traitant->cp}} {{$patient->_ref_medecin_traitant->ville}}
        </td>
      {{/if}}

      {{if $patient->_ref_medecins_correspondants|@count}}
        <th>{{tr}}CCorrespondantPatient-Medical correspondents{{/tr}}:</th>
        <td>
          {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp name=corresp}}
            {{$curr_corresp->_ref_medecin->_view}}{{if !$smarty.foreach.corresp.last}}<br />{{/if}}
          {{/foreach}}
        </td>
      {{/if}}
    </tr>
  {{/if}}

  <tr>
    <th class="category" colspan="10">{{tr}}CPatient-part-correspondants-patient{{/tr}}</th>
  </tr>
  <tr>
    <th class="category" colspan="2" style="font-size: 1.0em;">{{tr}}CCorrespondantPatient-Person to prevent{{/tr}}</th>
    <th class="category" colspan="2" style="font-size: 1.0em;">{{tr}}CCorrespondantPatient-employeur-court{{/tr}}</th>
  </tr>
  <tr>
    <td colspan="2" style="width: 50%;">
      {{foreach from=$patient->_ref_cp_by_relation.prevenir item=prevenir name=foreach_prevenir}}
        {{mb_ternary var=field_tel test=$prevenir->tel value="tel" other="mob"}}
        <table class="print" style="font-size: 11px; width: 100%;">
          <tr>
            <th style="width: 30%;">{{mb_label object=$prevenir field=nom}}</th>
            <td>
              {{mb_value object=$prevenir field=nom}}
              {{mb_value object=$prevenir field=prenom}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$prevenir field="adresse"}}</th>
            <td>
              {{mb_value object=$prevenir field="adresse"}}<br />
              {{mb_value object=$prevenir field="cp"}}
              {{mb_value object=$prevenir field="ville"}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$prevenir  field="tel"}}</th>
            <td>{{mb_value object=$prevenir  field=$field_tel}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$prevenir  field="parente"}}</th>
            <td>{{mb_value object=$prevenir  field="parente"}}</td>
          </tr>
        </table>
        {{if !$smarty.foreach.foreach_prevenir.last}}
          <br />
        {{/if}}
      {{/foreach}}
    </td>
    <td colspan="2" style="width: 50%;">
      {{foreach from=$patient->_ref_cp_by_relation.employeur item=employeur name=foreach_employeur}}
        <table class="print" style="font-size: 11px;">
          <tr>
            <th style="width: 30%;">{{mb_label object=$employeur field="nom"}}</th>
            <td>{{mb_value object=$employeur field="nom"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$employeur field="adresse"}}</th>
            <td>
              {{mb_value object=$employeur field="adresse"}}<br />
              {{mb_value object=$employeur field="cp"}}
              {{mb_value object=$employeur field="ville"}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$employeur field="tel"}}</th>
            <td>{{mb_value object=$employeur field="tel"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$employeur field="urssaf"}}</th>
            <td>{{mb_value object=$employeur field="urssaf"}}</td>
          </tr>
        </table>
        {{if !$smarty.foreach.foreach_employeur.last}}
          <br />
        {{/if}}
      {{/foreach}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="10">{{tr}}CPatient-part-assure-social{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field=assure_nom}} / {{mb_label object=$patient field=assure_prenom}}</th>
    <td>
      {{mb_value object=$patient field="assure_civilite"}}
      {{mb_value object=$patient field="assure_nom"}}

      {{mb_value object=$patient field="assure_prenom"}}
      {{mb_value object=$patient field="assure_prenoms"}}
    </td>

    <th rowspan="3">{{mb_label object=$patient field="assure_adresse"}}</th>
    <td rowspan="3">
      {{mb_value object=$patient field="assure_adresse"}}
      {{mb_value object=$patient field="assure_cp"}}
      {{mb_value object=$patient field="assure_ville"}}
      {{mb_value object=$patient field="assure_pays"}}<br />
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="assure_nom_jeune_fille"}}</th>
    <td>{{mb_value object=$patient field="assure_nom_jeune_fille"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="assure_naissance"}}</th>
    <td>
      {{mb_value object=$patient field="assure_naissance"}}
      {{tr}}CCorrespondantPatient-of sex{{/tr}} {{tr}}{{if $patient->assure_sexe == "m"}}CCorrespondantPatient-male{{else}}CCorrespondantPatient-female{{/if}}{{/tr}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="assure_lieu_naissance"}}</th>
    <td>
      {{mb_value object=$patient field="assure_cp_naissance"}}
      {{mb_value object=$patient field="assure_lieu_naissance"}}
      {{mb_value object=$patient field="_assure_pays_naissance_insee"}}

    <th>{{mb_label object=$patient field="assure_tel"}}</th>
    <td>{{mb_value object=$patient field="assure_tel"}}</td>
  </tr>
  <tr>
    <th></th>
    <td></td>

    <th>{{mb_label object=$patient field="assure_tel2"}}</th>
    <td>{{mb_value object=$patient field="assure_tel2"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="assure_profession"}}</th>
    <td>{{mb_value object=$patient field="assure_profession"}}</td>

    <th rowspan="2">{{mb_label object=$patient field="assure_rques"}}</th>
    <td rowspan="2">{{mb_value object=$patient field="assure_rques"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$patient field="assure_matricule"}}</th>
    <td>{{mb_value object=$patient field="assure_matricule"}}</td>
  </tr>

  {{if $patient->_ref_sejours|@count}}
    <tr>
      <th class="category" colspan="10">
        <button class="change not-printable" style="float:right;"
                onclick="toggleList('sejour', this)">{{tr}}CCorrespondantPatient-action-Only the last{{/tr}}</button>
        <span>{{tr}}CSejour|pl{{/tr}}</span>
      </th>
    </tr>
    {{foreach from=$patient->_ref_sejours item=curr_sejour}}
      <tr class="sejour">
        <th>Dr {{$curr_sejour->_ref_praticien}}</th>
        <td colspan="3">
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$curr_sejour}}
          {{tr var1=$curr_sejour->entree_prevue|date_format:$conf.date var2=$curr_sejour->sortie_prevue|date_format:$conf.date}}common-From %s to %s{{/tr}}
          - ({{mb_value object=$curr_sejour field=type}})
          <ul>
            {{foreach from=$curr_sejour->_ref_operations item="curr_op"}}
              <li>
                {{tr}}dPplanningOp-COperation of{{/tr}} {{$curr_op->_datetime|date_format:$conf.date}}
                (Dr {{$curr_op->_ref_chir}})
              </li>
              {{foreachelse}}
              <li class="empty">{{tr}}COperation-back-.empty{{/tr}}</li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}

  {{if $patient->_ref_consultations|@count}}
    <tr>
      <th class="category" colspan="10">
        <button class="change not-printable" style="float:right;"
                onclick="toggleList('consultation', this)">{{tr}}CCorrespondantPatient-action-Only the last|f{{/tr}}
        </button>
        <span>{{tr}}CConsultation|pl{{/tr}}</span>
      </th>
    </tr>
    {{foreach from=$patient->_ref_consultations item=curr_consult}}
      <tr class="consultation">
        <th>Dr {{$curr_consult->_ref_plageconsult->_ref_chir}}</th>
        <td colspan="3">{{tr var1=$curr_consult->_ref_plageconsult->date|date_format:$conf.date}}common-the %s{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
