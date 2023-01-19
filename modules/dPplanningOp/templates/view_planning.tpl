{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=show_print_dhe_info value=$conf.dPplanningOp.COperation.show_print_dhe_info}}

<table class="print">
  <tr>
    <th class="title" colspan="3">
      <span style="float:left; font-size:12px;">
        [{{$sejour->_NDA}}]
      </span>
      <span style="float:right;font-size:12px;">
        {{$sejour->_ref_group->text}}
      </span>
      <a href="#" onclick="window.print()">Fiche d'admission</a>
    </th>
  </tr>
  {{if $show_print_dhe_info}}
    <tr>
      <td class="info" colspan="3">
      (Prière de vous munir pour la consultation préanesthésique de la photocopie
       de vos cartes de sécurité sociale, de mutuelle, du résultat de votre
       bilan sanguin et de la liste des médicaments que vous prenez)<br />
       {{if $sejour->_ref_group->tel}}
         Pour tout renseignement, téléphonez au
         {{mb_value object=$sejour->_ref_group field=tel}}
       {{/if}}
      </td>
    </tr>
  {{/if}}
  <tr>
    <th>Date</th>
    <td>{{$today|date_format:"%A %d/%m/%Y"}}</td>
  </tr>
  
  <tr>
    <th>Praticien</th>
    <td>
    {{if $operation->_id}}
      {{if $operation->_ref_chir}}
        Dr {{$operation->_ref_chir->_view}}
      {{/if}}
    {{else}}
      {{if $sejour->_ref_praticien}}
        Dr {{$sejour->_ref_praticien->_view}}
      {{/if}}
    {{/if}}
    </td>
  </tr>
  
  <tr>
    <th class="category" colspan="3">Renseignements concernant le patient</th>
  </tr>

  {{assign var="patient" value=$sejour->_ref_patient}}

  <tr>
    <th>Nom / Prénom</th>
    <td>{{$patient->_view}}</td>
    {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
      <td rowspan="7">{{mb_include module=dPpatients template=vw_datamatrix_ins}}</td>
    {{/if}}
  </tr>
  
  <tr>
    <th>Date de naissance / Sexe</th>
    <td>
      né(e) le {{mb_value object=$patient field="naissance"}}
      de sexe
      {{if $patient->sexe == "m"}}masculin{{else}}féminin{{/if}}
    </td>
  </tr>

  {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
    <tr>
      <th>{{tr}}CINSPatient{{/tr}}</th>
      <td>
          {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
      </td>
    </tr>
  {{/if}}

  {{if $patient->tutelle && $patient->tutelle != 'aucune' && $patient->_ref_tuteur}}
    <tr>
      <th>{{mb_value object=$patient->_ref_tuteur field=parente}}</th>
      <td>{{$patient->_ref_tuteur->nom}} {{$patient->_ref_tuteur->prenom}}</td>
    </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$patient field=incapable_majeur}}</th>
    <td>{{mb_value object=$patient field=incapable_majeur}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field=tel}}</th>
    <td>{{mb_value object=$patient field=tel}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field=tel2}}</th>
    <td>{{mb_value object=$patient field=tel2}}</td>
  </tr>

  <tr>
    <th>Medecin traitant</th>
    <td>
    {{if $patient->_ref_medecin_traitant}}
      {{$patient->_ref_medecin_traitant->_view}}
    {{/if}}
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$patient field=adresse}}</th>
    <td>
      {{mb_value object=$patient field=adresse}}
      {{mb_value object=$patient field=cp}}
      {{mb_value object=$patient field=ville}}
    </td>
  </tr>
  
  <tr>
    <th class="category" colspan="3">Renseignements relatifs à l'hospitalisation</th>
  </tr>

  {{if $sejour->libelle}}
  <tr>
    <th>{{mb_label object=$sejour field=libelle}}</th>
    <td>{{mb_value object=$sejour field=libelle}}</td>
  </tr>
  {{/if}}

  {{if $sejour->_NDA}}
  <tr>
    <th>{{tr}}CSejour-_NDA{{/tr}}</th>
    <td>
      [{{$sejour->_NDA}}]
    </td>
  </tr>
  {{/if}}
  
  <tr>
    <th>Admission</th>
    <td>le {{$sejour->entree_prevue|date_format:"%A %d/%m/%Y à %Hh%M"}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$sejour field=type}}</th>
    <td>{{mb_value object=$sejour field=type}}</td>
  </tr>

  {{if "dPhospi prestations systeme_prestations"|gconf == "standard"}}
  <tr>
    <th>{{mb_label object=$sejour field=chambre_seule}}</th>
    <td>{{mb_value object=$sejour field=chambre_seule}}</td>
  </tr>
  {{/if}}

  {{if "dPplanningOp CSejour fields_display fiche_rques_sej"|gconf && $sejour->rques}}
  <tr>
    <th>{{mb_label object=$sejour field=rques}}</th>
    <td>{{mb_value object=$sejour field=rques}}</td>
  </tr>
  {{/if}}

  {{if "dPplanningOp CSejour fields_display fiche_conval"|gconf && $sejour->convalescence}}
  <tr>
    <th>{{mb_label object=$sejour field=convalescence}}</th>
    <td>{{mb_value object=$sejour field=convalescence}}</td>
  </tr>
  {{/if}}

  {{if $operation->_id}}
  <tr>
    <th>Date d'intervention</th>
    <td>le {{$operation->_datetime|date_format:"%A %d/%m/%Y"}}</td>
  </tr>

  {{if !$simple_DHE}}
  {{if $operation->libelle}}
  <tr>
    <th>{{mb_label object=$operation field=libelle}}</th>
    <td class="text"><em>{{mb_value object=$operation field=libelle}}</em></td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.use_ccam && $operation->codes_ccam}}
  <tr>
    <th>Actes</th>
    <td class="text">
      {{foreach from=$operation->_ext_codes_ccam item=ext_code_ccam}}
      {{if $ext_code_ccam->code != "-"}}
      {{$ext_code_ccam->libelleLong}} ({{$ext_code_ccam->code}})<br />
      {{/if}}
      {{/foreach}}
    </td>
  </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$operation field=cote}}</th>
    <td>{{mb_value object=$operation field=cote}}</td>
  </tr>

  {{if $conf.dPplanningOp.COperation.fiche_examen && $operation->examen}}
  <tr>
    <th>{{mb_label object=$operation field=examen}}</th>
    <td>{{mb_value object=$operation field=examen}}</td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.fiche_materiel && $operation->materiel}}
  <tr>
    <th>{{mb_label object=$operation field=materiel}}</th>
    <td>{{mb_value object=$operation field=materiel}}</td>
  </tr>
  {{/if}}
  {{if $conf.dPplanningOp.COperation.fiche_materiel && $operation->exam_per_op}}
  <tr>
    <th>{{mb_label object=$operation field=exam_per_op}}</th>
    <td>{{mb_value object=$operation field=exam_per_op}}</td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.fiche_rques && $operation->rques}}
  <tr>
    <th>{{mb_label object=$operation field=rques}}</th>
    <td>{{mb_value object=$operation field=rques}}</td>
  </tr>
  {{/if}}

  {{/if}}
  {{/if}}

  {{if "dPplanningOp CSejour fields_display accident"|gconf && $sejour->date_accident}}
  <tr>
    <th>{{mb_label object=$sejour field=date_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=date_accident}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=nature_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=nature_accident}}</td>
  </tr>
  {{/if}}

  {{if "dPplanningOp CSejour fields_display assurances"|gconf && "dPplanningOp CFactureEtablissement use_facture_etab"|gconf}}
    {{assign var="facture" value=$sejour->_ref_facture}}
    {{if $facture->assurance_maladie}}
    <tr>
      <th>{{mb_label object=$facture field=assurance_maladie}}</th>
      <td class="text">{{mb_value object=$facture field=assurance_maladie}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$facture field=rques_assurance_maladie}}</th>
      <td class="text">{{mb_value object=$facture field=rques_assurance_maladie}}</td>
    </tr>
    {{/if}}
  {{/if}}

  {{if $sejour->mode_sortie_id}}
    <tr>
      <th>{{mb_title object=$sejour field=mode_sortie}}</th>
      <td>{{mb_value object=$sejour field=mode_sortie_id}}</td>
    </tr>
  {{elseif $sejour->mode_sortie}}
    <tr>
      <th>{{mb_title object=$sejour field=mode_sortie}}</th>
      <td>{{mb_value object=$sejour field=mode_sortie}}</td>
    </tr>
  {{/if}}
  {{if $sejour->transport_sortie}}
    <tr>
      <th>{{mb_title object=$sejour field=transport_sortie}}</th>
      <td>{{mb_value object=$sejour field=transport_sortie}}</td>
    </tr>
  {{/if}}
  {{if $sejour->rques_transport_sortie}}
    <tr>
      <th>{{mb_title object=$sejour field=rques_transport_sortie}}</th>
      <td>{{mb_value object=$sejour field=rques_transport_sortie}}</td>
    </tr>
  {{/if}}
  {{if $patient->_refs_patient_handicaps}}
    <tr>
      <th>{{tr}}CPatientHandicap{{/tr}}</th>
      <td>
          <ul>
              {{foreach from=$patient->_refs_patient_handicaps item=_handicap}}
                  <li>{{$_handicap}}</li>
              {{/foreach}}
          </ul>
      </td>
    </tr>
  {{/if}}
  {{if $sejour->aide_organisee}}
    <tr>
      <th>{{mb_label object=$sejour field=aide_organisee}}</th>
      <td>{{mb_value object=$sejour field=aide_organisee}}</td>
    </tr>
  {{/if}}

  <tr>
    <th>Durée prévue d'hospitalisation</th>
    <td>{{$sejour->_duree_prevue}} nuits</td>
  </tr>

  <tr>
    <th>Adresse</th>
    <td>
      {{$sejour->_ref_group->text}}<br />
      {{$sejour->_ref_group->adresse}}<br />
      {{$sejour->_ref_group->cp}}
      {{$sejour->_ref_group->ville}}
    </td>
  </tr>

  {{if $operation->_id}}
    {{if $operation->forfait}}
      <tr>
        <th>{{mb_label object=$operation field=forfait}}</th>
        <td>{{mb_value object=$operation field=forfait}}</td>
      </tr>
    {{/if}}
    {{if $operation->fournitures}}
      <tr>
      <th>{{mb_label object=$operation field=fournitures}}</th>
      <td>{{mb_value object=$operation field=fournitures}}</td>
      </tr>
    {{/if}}

    {{if $show_print_dhe_info}}
      <tr>
        <th class="category" colspan="3">Rendez vous d'anesthésie</th>
      </tr>

      <tr>
        <td class="text" colspan="3">
          Veuillez prendre rendez-vous avec le cabinet d'anesthésistes <strong>impérativement</strong>
          avant votre intervention.
         {{if $sejour->_ref_group->tel_anesth}}
           Pour cela, téléphonez au {{mb_value object=$sejour->_ref_group field=tel_anesth}}
         {{/if}}
        </td>
      <tr>
    {{/if}}
  {{/if}}
  {{if $show_print_dhe_info}}
    <tr>
      <td class="info" colspan="3">
        <b>Pour votre hospitalisation, prière de vous munir de :</b>
        <ul>
          <li>Carte d'identité</li>
          <li>
            Carte vitale et attestation de sécurité sociale,
            carte de mutuelle et prise en charge complète auprès de votre mutuelle
            à transmettre au personnel des admissions lors de votre entrée.
          </li>
          <li>Tous examens en votre possession (analyse, radio, carte de groupe sanguin...).</li>
          <li>Prévoir linge et nécessaire de toilette.</li>
          <li>Vos médicaments éventuellement</li>
        </ul>
      </td>
    </tr>
  {{/if}}
</table>
