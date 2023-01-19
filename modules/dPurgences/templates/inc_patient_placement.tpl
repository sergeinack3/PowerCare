{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_div value=1}}

{{assign var=patient   value=$_sejour->_ref_patient}}
{{assign var=rpu   value=$_sejour->_ref_rpu}}
{{assign var=rpu_id   value=$rpu->_id}}
{{assign var=sejour_id   value=$_sejour->_id}}
{{assign var=reservation value=$rpu->_ref_reservation}}
{{assign var=chambre_resa value=false}}
{{if $reservation->_id && $reservation->lit_id != $rpu->box_id && $reservation->_ref_lit->chambre_id == $_zone->chambre_id}}
  {{assign var=chambre_resa value=true}}
{{/if}}

{{assign var=background_color_ccmu value="dPurgences Placement display_background_color_ccmu"|gconf}}

{{if !$with_div && $isImedsInstalled}}
  <script>
    Main.add(function() {
      // Lorsque l'étiquette patient est rechargée, on revérifie les résultats labo du séjour
      ImedsResultsWatcher.loadResults();
    });
  </script>
{{/if}}

{{if $with_div}}

{{assign var=classification_color value=$rpu->_color_cimu}}
{{assign var=hashtag value=""}}

{{if "dPurgences CRPU french_triage"|gconf}}
  {{assign var=classification_color value=""}}
  {{if $rpu->french_triage}}
    {{assign var=classification_color value="dPurgences Display color_french_triage_`$rpu->french_triage`"|gconf}}
  {{/if}}
  {{assign var=hashtag value="#"}}
{{elseif !"dPurgences Display display_cimu"|gconf}}
  {{assign var=classification_color value=""}}
  {{if $rpu->ccmu}}
    {{assign var=classification_color value="dPurgences Display color_ccmu_`$rpu->ccmu`"|gconf}}
  {{/if}}
  {{assign var=hashtag value="#"}}
{{/if}}

<div id="placement_{{$sejour_id}}"
     class="patient {{if !$chambre_resa}}draggable{{else}}hatching{{/if}}"
     data-form_name="{{$rpu->_guid}}_{{$name_grille}}"
     data-patient-id="{{$_sejour->patient_id}}"
     data-rpu-id="{{$rpu->_id}}"
     data-name_grille="{{$name_grille}}"
     data-zone_id="{{$_zone->chambre_id}}"
     style="{{if $background_color_ccmu}}
                padding-left: 5px;background-color:
              {{else}}
                border-left: 5px solid
              {{/if}}
              {{$hashtag}}{{$classification_color}};
            {{if $rpu->color}}background-color: #{{$rpu->color}};{{/if}}">
{{/if}}
  <div style="margin-bottom: 3px;">
    {{if $chambre_resa}}
      <div class="info">{{tr}}CLit-back-reservation_box{{/tr}}</div>
    {{/if}}
    <form name="{{$rpu->_guid}}_{{$name_grille}}" action="" method="post">
      {{mb_class object=$rpu}}
      {{mb_key   object=$rpu}}
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="_bind_sejour" value="1" />
      <input type="hidden" name="_service_id" value="{{$_zone->service_id}}" />
      <input type="hidden" name="box_id"      value="{{$rpu->box_id}}"/>
    </form>

    <form name="CRPUReservationBox_{{$rpu->_guid}}_{{$name_grille}}" action="" method="post">
      {{mb_class object=$reservation}}
      {{mb_key   object=$reservation}}
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="rpu_id" value="{{$rpu->_id}}" />
      <input type="hidden" name="lit_id" value="" />
    </form>

    {{if $rpu->_class_sfmu && "dPurgences Placement display_icon_sfmu"|gconf}}
      <i class="{{$rpu->_class_sfmu}}" style="font-size: 16pt; float: right;" title="{{mb_value object=$rpu->_ref_motif_sfmu field=libelle}}"></i>
    {{/if}}

    <a href="#1" onclick="
      {{if $app->_ref_user->isPraticien() && $app->_ref_user->isUrgentiste() && $_sejour->_ref_consult_atu && $_sejour->_ref_consult_atu->_id}}
        Urgences.pecMed('{{$_sejour->_ref_consult_atu->_id}}', 'rpuConsult');
      {{else}}
        Urgences.pecInf('{{$sejour_id}}', '{{$rpu_id}}');
      {{/if}}"
      >
      {{assign var=couleur_symbole value="gray"}}
      {{if ($_sejour->_ref_consult_atu && $_sejour->_ref_consult_atu->_id) || $rpu->mutation_sejour_id}}
        {{assign var=couleur_symbole value="blue"}}
        {{assign var=fond_couleur_symbole value="rgba(255,255,255,0.8)"}}
      {{elseif $rpu->motif_sfmu || $rpu->ccmu}}
        {{assign var=couleur_symbole value="Gold"}}
        {{assign var=fond_couleur_symbole value="rgba(0,0,0,0.8)"}}
      {{/if}}

      {{assign var=silhouette value="fa-male"}}
      {{if $patient->civilite == "enf"}}
        {{assign var=silhouette value="fa-child"}}
        {{if $patient->sexe == "m"}}
          {{assign var=silhouette value="fa-mars"}}
        {{else}}
          {{assign var=silhouette value="fa-venus"}}
        {{/if}}
      {{elseif $patient->sexe != "m"}}
        {{assign var=silhouette value="fa-female"}}
      {{/if}}

      <i class="fa {{$silhouette}}" style="font-size: 11pt; color: {{$couleur_symbole}};
      {{if in_array($rpu->ccmu, array("4", "5"))}}border-left:5px solid red;padding-left:1px;{{/if}}
        {{if $rpu->ccmu && $background_color_ccmu}}background-color: {{$fond_couleur_symbole}};{{/if}}"></i>
      {{if "dPurgences Placement placement_anonyme"|gconf}}
        {{$patient->nom|spancate:3:""}} {{$patient->prenom|spancate:3:""}}
      {{else}}
        {{$patient->nom}} {{$patient->prenom}}
      {{/if}}

      {{mb_include module=patients template=inc_icon_bmr_bhre}}
      {{if $_sejour->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
      {{/if}}
    </a>
    {{if $rpu->_ref_notes|@count}}
      {{mb_include module=system float=left template=inc_object_notes object=$rpu}}
    {{/if}}
    {{mb_include template=inc_icone_attente rpu=$_sejour->_ref_rpu width=24}}
    <div class="libelle compact me-display-flex me-justify-content-space-between" {{if $background_color_ccmu}}style="color:#000;"{{/if}}>
      <div onmouseover="ObjectTooltip.createEx(this, '{{if $rpu->_id}}{{$rpu->_guid}}{{else}}{{$_sejour->_guid}}{{/if}}');">
        {{$rpu->motif|truncate:30|lower}}
        <div>({{$patient->_age}})<br/>Arrivée: {{mb_value object=$_sejour field=entree date=$date format=$conf.time}}
        </div>
        <div>{{$rpu->diag_infirmier|spancate:60:"..."|lower|smarty:nodefaults}}</div>
        {{if "dPurgences Placement display_reason_sfmu"|gconf && ($rpu->motif_sfmu && $rpu->diag_infirmier != $rpu->_ref_motif_sfmu->libelle)}}
          <div>{{$rpu->_ref_motif_sfmu->libelle}}</div>
        {{/if}}
      </div>
      <div class="me-display-flex me-flex-column">
        {{assign var=chir_tooltip value=$_sejour->_ref_praticien}}
        {{if "dPurgences CRPU prat_affectation"|gconf && $_sejour->_ref_curr_affectation && $_sejour->_ref_curr_affectation->_ref_praticien && $_sejour->_ref_curr_affectation->_ref_praticien->_id}}
          {{assign var=chir_tooltip value=$_sejour->_ref_curr_affectation->_ref_praticien}}
        {{/if}}
        {{mb_include module=mediusers template=inc_vw_mediuser initials=border mediuser=$chir_tooltip}}

        {{if $rpu->ide_responsable_id && $rpu->ide_responsable_id != $_sejour->_ref_praticien->_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser initials=border mediuser=$rpu->_ref_ide_responsable}}
        {{/if}}
      </div>
    </div>
  </div>

  {{assign var=attente_radio value=$rpu->_ref_last_attentes.radio}}
  {{if $attente_radio->depart || $attente_radio->demande}}
    {{assign var=timing_radio value="demande"}}
    {{if $attente_radio->retour}}
      {{assign var=timing_radio value="retour"}}
    {{elseif $attente_radio->depart}}
      {{assign var=timing_radio value="depart"}}
    {{/if}}
    <img src="modules/soins/images/radio{{if !$attente_radio->retour}}_grey{{/if}}.png"
         {{if $timing_radio == "demande"}}style="opacity:0.7"{{/if}}
         title="{{tr}}CRPUAttente-radio-{{$timing_radio}}{{/tr}}: {{$attente_radio->$timing_radio|date_format:$conf.time}}"/>
  {{/if}}

  {{assign var=attente_bio value=$rpu->_ref_last_attentes.bio}}
  {{if $attente_bio->depart || $attente_bio->demande}}
    {{assign var=timing_bio value="demande"}}
    {{if $attente_bio->retour}}
      {{assign var=timing_bio value="retour"}}
    {{elseif $attente_bio->depart}}
      {{assign var=timing_bio value="depart"}}
    {{/if}}
    <img src="images/icons/labo{{if !$attente_bio->retour}}_grey{{/if}}.png"
         {{if $timing_bio == "demande"}}style="opacity:0.7"{{/if}}
         title="{{tr}}CRPUAttente-bio-{{$timing_bio}}{{/tr}}: {{$attente_bio->$timing_bio|date_format:$conf.time}}"/>
  {{/if}}

  {{assign var=attente_specialiste value=$rpu->_ref_last_attentes.specialiste}}
  {{if $attente_specialiste->depart}}
    {{assign var=timing_spec value="depart"}}
    {{if $attente_specialiste->retour}}
      {{assign var=timing_spec value="retour"}}
    {{/if}}
    <img src="modules/soins/images/stethoscope{{if !$attente_specialiste->retour}}_grey{{/if}}.png"
         title="{{tr}}CRPUAttente-specialiste-{{$timing_spec}}{{/tr}}: {{$attente_specialiste->$timing_spec|date_format:$conf.time}}"/>
  {{/if}}

  {{if $isImedsInstalled}}
    {{mb_include module=Imeds template=inc_sejour_labo sejour=$_sejour onclick="Urgences.pecInf('$sejour_id', '$rpu_id', 'Imeds')"}}
  {{/if}}

  {{if $_sejour->_nb_files_docs > 0}}
    <img src="images/icons/docitem.png"
         title="{{$_sejour->_nb_files|default:0}} {{tr}}CMbObject-back-files{{/tr}} / {{$_sejour->_nb_docs|default:0}} {{tr}}CMbObject-back-documents{{/tr}}" />
  {{/if}}

  {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
  {{if $prescription->_id}}
    {{if $prescription->_count_fast_recent_modif}}
      <img src="images/icons/ampoule.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
    {{else}}
      <img src="images/icons/ampoule_grey.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
    {{/if}}
    {{if $prescription->_count_urgence|@array_sum}}
      <img src="images/icons/ampoule_urgence.png" />
    {{/if}}
  {{/if}}

  {{if $_sejour->UHCD}}
    <span class="encart encart-uhcd">UHCD</span>
  {{/if}}

  {{if $_sejour->_ref_curr_affectation &&
       $_sejour->_ref_curr_affectation->_ref_service &&
       $_sejour->_ref_curr_affectation->_ref_service->radiologie}}
    <span class="encart encart-imagerie">IMG</span>
  {{/if}}

  {{if $rpu->mutation_sejour_id}}
    <span class="texticon texticon-mutation">Muta</span>
  {{/if}}
  {{if $rpu->sortie_autorisee}}
    <i class="fas fa-check" style="color:#080" onmouseover="ObjectTooltip.createDOM(this, 'confirme-hover-{{$_sejour->_id}}')">
      {{tr}}CSejour-confirme{{/tr}}
    </i>
    {{if $rpu->date_sortie_aut || $_sejour->destination}}
      <table id="confirme-hover-{{$_sejour->_id}}" style="display:none" class="tbl">
        {{if $rpu->date_sortie_aut}}
          <tr>
            <th class="category">{{mb_label class=CRPU field=date_sortie_aut}}</th>
            <td>{{mb_value object=$rpu field=date_sortie_aut}}</td>
          </tr>
        {{/if}}
        {{if $_sejour->destination}}
          <tr>
            <th class="category">{{mb_label class=CSejour field=destination}}</th>
            <td>{{mb_value object=$_sejour field=destination}}</td>
          </tr>
        {{/if}}
      </table>
    {{/if}}
  {{/if}}

  {{if $_sejour->_ref_prescription_sejour}}
    <span style="font-size: 12pt;">
      {{mb_include module=prescription template=vw_line_important lines=$_sejour->_ref_prescription_sejour->_ref_lines_important}}
    </span>
  {{/if}}

  {{mb_include module=urgences template=inc_icons_categories}}
{{if $with_div}}
</div>
{{/if}}
