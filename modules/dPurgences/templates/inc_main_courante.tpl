{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=create_sejour_hospit value=$conf.dPurgences.create_sejour_hospit}}

<script>
  Main.add(function() {
    Veille.refresh();
    Missing.refresh();
    
    {{if $type == "MainCourante"}}
      $$("a[href=#holder_main_courante] small")[0].update("({{$listSejours|@count}})");
    {{elseif $type == "UHCD"}}
      var tab = $$("a[href=#holder_uhcd]")[0];
      tab.down("small").update("({{$listSejours|@count}})");
      {{if $listSejours|@count == '0'}}
        tab.addClassName('empty');
      {{else}}
        tab.removeClassName('empty');
      {{/if}}
    {{elseif $type == "imagerie"}}
      var tab = $$("a[href=#holder_imagerie]")[0];
      tab.down("small").update("({{$listSejours|@count}})");
      {{if $listSejours|@count == '0'}}
      tab.addClassName('empty');
      {{else}}
      tab.removeClassName('empty');
      {{/if}}
    {{/if}}
    
    {{if $isImedsInstalled}}
      ImedsResultsWatcher.loadResults();
    {{/if}}
  });

  fillRetour = function(form) {
    $V(form.retour, "current");
    form.onsubmit();
  };

  fillDiag = function(rpu_id) {
    {{if $type == "MainCourante"}}
      MainCourante.stop();
    {{elseif $type == "UHCD"}}
      UHCD.stop();
    {{elseif $type == "imagerie"}}
    Imagerie.stop();
    {{/if}}
    var url = new Url("dPurgences", "ajax_edit_diag");
    url.addParam("rpu_id", rpu_id);
    url.requestModal(500, 200);
    url.modalObject.observe("afterClose", function(){
      {{if $type == "MainCourante"}}
        MainCourante.start();
      {{elseif $type == "UHCD"}}
        UHCD.start();
      {{elseif $type == "imagerie"}}
        Imagerie.start();
      {{/if}}
    });
  };
</script>

<div class="small-info" style="display: none;" id="filter-indicator">
  <strong>{{tr}}CRPU-Filtered results{{/tr}}</strong>.
  <br />
  {{tr}}CRPU-msg-The results are filtered and refresh is disabled{{/tr}} 
  {{if $type == "MainCourante"}}
    <button class="change" onclick="MainCourante.start()">{{tr}}CRPU-action-Relaunch{{/tr}}</button>
  {{elseif $type == "UHCD"}}
    <button class="change" onclick="UHCD.start()">{{tr}}CRPU-action-Relaunch{{/tr}}</button>
  {{elseif $type == "imagerie"}}
    <button class="change" onclick="Imagerie.start()">{{tr}}CRPU-action-Relaunch{{/tr}}</button>
  {{/if}}
</div>

<table class="tbl">
  {{if "dPurgences CRPU type_sejour"|gconf === "urg_consult" && $type == "MainCourante"}}
    <tr>
      <th class="title" colspan="10">{{tr}}CRPU-reconvoc|pl{{/tr}}</th>
    </tr>
    <tr>
      <th style="width: 16em;" colspan="2">
        {{mb_colonne class=CRPU field="_patient_id" order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu&tri_reconvocation=1"}}
      </th>
    
      <th class="narrow">
        <input type="text" size="6" onkeyup="MainCourante.filter(this, 'filter-indicator')" id="filter-patient-name-{{$type}}" />
      </th>
    
      <th style="width: 10em;">
        {{mb_colonne class=CConsultation field="heure" order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu&tri_reconvocation=1"}}
      </th>
      <th class="narrow">{{mb_title class=CRPU field="_responsable_id"}}</th>
      <th style="width: 10em;">{{mb_title class=CConsultation field=arrivee}}</th>
      <th style="width: 16em;">{{mb_title class=CConsultation field=motif}}</th>
      <th style="width: 0;" colspan="3">{{tr}}CRPU.pec{{/tr}}</th>
    </tr>

    {{foreach from=$consultations item=_consultation}}
      {{assign var=patient   value=$_consultation->_ref_patient}}
      {{assign var=praticien value=$_consultation->_ref_praticien}}
      <tr style="{{if $_consultation->chrono == $_consultation|const:'PATIENT_ARRIVE'}}background:#9F0;{{elseif $_consultation->_datetime > $dtnow}}opacity: 0.2;{{/if}}" >
        <td colspan="3">
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
            <span class="CPatient-view">{{$patient}}</span>
          </strong>

          {{mb_include module=patients template=inc_icon_bmr_bhre}}
          {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          ({{$patient->sexe|upper}})
          {{if $conf.dPurgences.age_patient_rpu_view}}{{$patient->_age}}{{/if}}
        </td>
        <td>
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}');">
            {{$_consultation->heure|date_format:$conf.time}}
          </strong>
        </td>
        <td>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
        </td>
        <td>
          {{mb_value object=$_consultation field=arrivee}}
        </td>
        <td>
          {{mb_value object=$_consultation field=motif}}
        </td>
        <td colspan="3">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
          {{if $can->edit}}
          <a class="button search" title="{{tr}}CRPU-event-pec{{/tr}}" 
             href="?m=urgences&tab=edit_consultation&selConsult={{$_consultation->_id}}">
            {{tr}}CRPU-see_pec{{/tr}}
          </a>
          {{/if}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CPatient-back-consultations.empty{{/tr}}</td>
      </tr>
    {{/foreach}}

    <tr>
      <th class="title" colspan="10">{{tr}}Main_courante{{/tr}}</th>
    </tr>
  {{/if}}

  {{*Main courante*}}
  <tr>
    <th style="width: 8em;">
      {{if "dPurgences Display display_order"|gconf == "ccmu" || !"dPurgences Display display_cimu"|gconf}}
        {{mb_colonne class=CRPU field="ccmu" order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu"}}
      {{elseif "dPurgences Display display_order"|gconf == "french_triage" || "dPurgences Display french_triage"|gconf}}
        {{mb_colonne class=CRPU field=french_triage order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu"}}
      {{else}}
        {{mb_colonne class=CRPU field="cimu" order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu"}}
      {{/if}}
    </th>
    <th style="width: 16em;">
      {{mb_colonne class=CRPU field="_patient_id" order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu"}}
    </th>
    
    <th class="narrow">
      {{if $type == "MainCourante"}}
        <input type="text" size="6" onkeyup="MainCourante.filter(this, 'filter-indicator')" id="filter-patient-name-{{$type}}" />
      {{elseif $type == "UHCD"}}
        <input type="text" size="6" onkeyup="UHCD.filter(this, 'filter-indicator')" id="filter-patient-name-{{$type}}" />
      {{elseif $type == "imagerie"}}
        <input type="text" size="6" onkeyup="Imagerie.filter(this, 'filter-indicator')" id="filter-patient-name-{{$type}}" />
      {{/if}}
    </th>
    
    <th style="width: 10em;">
      {{mb_colonne class=CRPU field="_entree"     order_col=$order_col order_way=$order_way url="?m=$m&tab=vw_idx_rpu"}}
    </th>
    {{if $conf.dPurgences.responsable_rpu_view}}
    <th class="narrow">{{mb_title class=CRPU field="_responsable_id"}}</th>
    {{/if}}
    <th style="width: 10em;">{{mb_title class=CRPU field=_attente}} / {{mb_title class=CRPU field=_presence}}</th>
    {{if $medicalView}}
      <th style="width: 16em;">
      {{if "dPurgences CRPU diag_prat_view"|gconf}}
        {{tr}}CRPU-diag_infirmier-court{{/tr}} / {{tr}}Medical{{/tr}}
      {{else}}
        {{tr}}CRPU-diag_infirmier-court{{/tr}}
      {{/if}}
      </th>
    {{/if}}
    <th class="narrow">
      <label title="{{tr}}CRPU-pec_ioa-desc{{/tr}}">
        {{tr}}CRPU-Supported by the IOA{{/tr}}
      </label>
    </th>
    {{if "dPurgences Display check_date_pec_inf"|gconf}}
      <th class="narrow">
        <label title="{{tr}}CRPU-pec_inf-desc{{/tr}}">
          {{tr}}CRPU-Supported by the nurse{{/tr}}
        </label>
      </th>
    {{/if}}
    <th style="width: 0;">{{tr}}CRPU.pec{{/tr}}</th>
  </tr>

  {{foreach from=$listSejours item=_sejour key=sejour_id}}
    {{assign var=rpu value=$_sejour->_ref_rpu}}
    {{assign var=rpu_id value=$rpu->_id}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    {{assign var=consult value=$rpu->_ref_consult}}

    {{assign var=background value=none}}
    {{if $consult && $consult->_id}}
      {{if $IS_MEDIBOARD_EXT_DARK}}
        {{assign var=background value="#555564"}}
      {{else}}
        {{assign var=background value="#ccf"}}
      {{/if}}
    {{/if}}

    {{* Param to create/edit a RPU *}}
    {{assign var=rpu_link value="Urgences.pecInf('$sejour_id', '$rpu_id')"}}

    <tr class="
     {{if !$_sejour->sortie_reelle && $_sejour->_veille}}veille{{/if}}
     {{if !$rpu_id}}missing{{/if}}
    ">
      {{if $_sejour->annule}}
        <td class="cancelled">
          {{tr}}Cancelled{{/tr}}
        </td>
      {{else}}
        {{assign var=last_reeval_pec value=$rpu->_ref_rpu_last_reevaluation_pec}}

          {{if "dPurgences Display display_order"|gconf == "french_triage" && "dPurgences CRPU french_triage"|gconf}}
            <td class="text"
                style="{{if $_sejour->sortie_reelle || ($rpu->mutation_sejour_id && $create_sejour_hospit)}}border-right: 5px solid #2f71b0;{{/if}}
                {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->french_triage}}
                  background-color: #{{"dPurgences Display color_french_triage_`$last_reeval_pec->french_triage`"|gconf}}{{if $IS_MEDIBOARD_EXT_DARK}}40{{/if}};
                {{elseif $rpu->french_triage}}
                  background-color: #{{"dPurgences Display color_french_triage_`$rpu->french_triage`"|gconf}}{{if $IS_MEDIBOARD_EXT_DARK}}40{{/if}};
                {{/if}}">
                <a href="#1" onclick="{{$rpu_link}}">
                    {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->french_triage}}
                        {{mb_value object=$last_reeval_pec field=french_triage}}
                    {{elseif $rpu->french_triage}}
                        {{mb_value object=$rpu field=french_triage}}
                    {{/if}}
                </a>
          {{elseif "dPurgences Display display_order"|gconf == "ccmu" || !"dPurgences Display display_cimu"|gconf}}
              <td class="text"
                style="{{if $_sejour->sortie_reelle || ($rpu->mutation_sejour_id && $create_sejour_hospit)}}border-right: 5px solid black;{{/if}}
                           {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->ccmu}}
                           background-color: #{{"dPurgences Display color_ccmu_`$last_reeval_pec->ccmu`"|gconf}}{{if $IS_MEDIBOARD_EXT_DARK}}40{{/if}};
                           {{elseif $rpu->ccmu}}
                           background-color: #{{"dPurgences Display color_ccmu_`$rpu->ccmu`"|gconf}}{{if $IS_MEDIBOARD_EXT_DARK}}40{{/if}};
                           {{/if}}">
                <a href="#1" onclick="{{$rpu_link}}">
                  {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->ccmu}}
                    {{mb_value object=$last_reeval_pec field=ccmu}}
                  {{elseif $rpu->ccmu}}
                    {{mb_value object=$rpu field=ccmu}}
                  {{/if}}
                </a>
          {{else}}
              <td class="text"
                  style="{{if $_sejour->sortie_reelle || ($rpu->mutation_sejour_id && $create_sejour_hospit)}}border-right: 5px solid black;{{/if}}
                  {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->cimu}}
                    background-color: {{$last_reeval_pec->_color_cimu_reeval_pec}}{{if $IS_MEDIBOARD_EXT_DARK}}45{{/if}};
                  {{elseif $rpu->cimu}}
                    background-color: {{$rpu->_color_cimu}}{{if $IS_MEDIBOARD_EXT_DARK}}45{{/if}};
                  {{/if}}">
                <a href="#1" onclick="{{$rpu_link}}">
                  {{if $last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->cimu}}
                    {{mb_value object=$last_reeval_pec field=cimu}}
                  {{elseif $rpu->cimu}}
                    {{mb_value object=$rpu field=cimu}}
                  {{/if}}
                </a>
          {{/if}}

          {{if $rpu->box_id}}
            {{assign var=rpu_box_id value=$rpu->box_id}}
            {{if array_key_exists($rpu_box_id, $boxes)}}
              <strong>{{$boxes.$rpu_box_id->_view}}</strong>
            {{/if}}
          {{/if}}
        </td>
      {{/if}}
  
      {{if $_sejour->annule}}
      <td colspan="2" class="text cancelled">
      {{else}}
      <td colspan="2" class="text" style="background-color: {{$background}};">
      {{/if}}
        <button type="button" class="search notext me-tertiary" title="{{tr}}CRPU.synthese{{/tr}}"
                onclick="showSynthese('{{$_sejour->_id}}');" style="float: right">
          {{tr}}CRPU.synthese{{/tr}}
        </button>

        {{if $patient->_ref_IPP}}
          <form name="editIPP{{$patient->_id}}" method="post" class="prepared">
            <input type="hidden" class="notNull" name="id400" value="{{$patient->_ref_IPP->id400}}" />
            <input type="hidden" class="notNull" name="object_id" value="{{$patient->_id}}" />
            <input type="hidden" class="notNull" name="object_class" value="CPatient" />
          </form>
        {{/if}}

        {{if $_sejour->_ref_NDA}}
          <form name="editNumdos{{$_sejour->_id}}" method="post" class="prepared">
            <input type="hidden" class="notNull" name="id400" value="{{$_sejour->_ref_NDA->id400}}"/>
            <input type="hidden" class="notNull" name="object_id" value="{{$_sejour->_id}}" />
            <input type="hidden" class="notNull" name="object_class" value="CSejour" />
          </form>
        {{/if}}

        {{if "dPsante400"|module_active}}
          {{mb_include module=dPsante400 template=inc_manually_ipp_nda sejour=$_sejour patient=$patient callback="MainCourante.start.bind(MainCourante)"}}
        {{/if}}
        {{mb_include template=inc_rpu_patient sejour=$_sejour}}
        {{if $_sejour->presence_confidentielle}}
          {{mb_include module=planningOp template=inc_badge_sejour_conf}}
        {{/if}}
      </td>
  
      {{if $_sejour->annule}}
      <td class="cancelled" colspan="{{if $conf.dPurgences.responsable_rpu_view}}4{{else}}3{{/if}}">
        {{tr}}Cancelled{{/tr}}
      </td>
      <td class="cancelled">
        {{if $rpu->_ref_consult && $rpu->_ref_consult->_id}}
          {{mb_include template="inc_pec_praticien"}}
        {{/if}}
      </td>
  
      {{else}}

      <td class="text" style="background-color: {{$background}}; text-align: center;">
        {{if $rpu->_ref_ide_responsable && $rpu->_ref_ide_responsable->_id}}
          <strong>{{mb_label class="CRPU" field="ide_responsable_id"}}</strong> :
          <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_ref_ide_responsable->_guid}}')">
            {{$rpu->_ref_ide_responsable->_view}}
          </span>
        {{/if}}
        {{mb_include module=system template=inc_object_notes object=$_sejour mode=view float=right}}

        {{if $isImedsInstalled}}
          {{mb_include module=Imeds template=inc_sejour_labo sejour=$_sejour onclick="Urgences.pecInf('$sejour_id', '$rpu_id', 'Imeds')"}}
        {{/if}}
  
        <a href="#1" onclick="{{$rpu_link}}">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
            {{mb_value object=$_sejour field=entree date=$date}}
           {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}
          </span>
        </a>

          <div style="clear: both; font-weight: bold; padding-top: 3px;">
            {{if "unilabs"|module_active}}
              {{mb_include module=unilabs template=inc_button_unilabs}}
            {{/if}}
            {{mb_include module=urgences template=inc_form_attente_main_courante type_attente="radio"}}
            {{mb_include module=urgences template=inc_form_attente_main_courante type_attente="bio"}}
            {{mb_include module=urgences template=inc_form_attente_main_courante type_attente="specialiste"}}

            {{if $_sejour->_nb_files_docs > 0}}
              <a href="#1" onclick="Urgences.pecInf('{{$sejour_id}}', '{{$rpu_id}}', 'doc-items')" style="display: inline">
                <img src="images/icons/docitem.png"
                  title="{{$_sejour->_nb_files|default:0}} {{tr}}CMbObject-back-files{{/tr}} / {{$_sejour->_nb_docs|default:0}} {{tr}}CMbObject-back-documents{{/tr}}"/></a>
            {{else}}
              <img src="images/icons/placeholder.png" />
            {{/if}}

            {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
            {{if $prescription->_id}}
              <a href="#1" onclick="Urgences.pecInf('{{$sejour_id}}', '{{$rpu_id}}', 'suivisoins')" style="display: inline;">
                {{if $prescription->_count_fast_recent_modif}}
                  <img src="images/icons/ampoule.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')" />
                {{else}}
                  <img src="images/icons/ampoule_grey.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')" />
                {{/if}}
              </a>
              {{if $prescription->_count_urgence|@array_sum}}
                <img src="images/icons/ampoule_urgence.png" />
              {{/if}}
            {{else}}
              <img src="images/icons/placeholder.png" />
            {{/if}}

            {{if $_sejour->UHCD}}
              <span class="encart encart-uhcd">{{tr}}CRPU-_UHCD{{/tr}}</span>
            {{/if}}

            {{if $_sejour->_ref_curr_affectation &&
                 $_sejour->_ref_curr_affectation->_ref_service &&
                 $_sejour->_ref_curr_affectation->_ref_service->radiologie}}
              <span class="encart encart-imagerie">{{tr}}CRPU-Image-court{{/tr}}</span>
            {{/if}}

            {{if $rpu->mutation_sejour_id}}
              <span class="texticon texticon-mutation">{{tr}}CRPU-Mutation-court{{/tr}}</span>
            {{/if}}
          </div>
      </td>
      
      {{if $conf.dPurgences.responsable_rpu_view}}
        <td style="background-color: {{$background}};">
          {{assign var=remplacant value=$_sejour->_ref_praticien->_ref_remplacant}}
          {{if $remplacant}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$remplacant}}
            <br/> <span class="compact">(remplaçant de
          {{/if}}
            <a href="#1" onclick="{{$rpu_link}}" style="display:inline">
              {{assign var=chir_tooltip value=$_sejour->_ref_praticien}}
              {{if "dPurgences CRPU prat_affectation"|gconf && $_sejour->_ref_curr_affectation && $_sejour->_ref_curr_affectation->_ref_praticien && $_sejour->_ref_curr_affectation->_ref_praticien->_id}}
                {{assign var=chir_tooltip value=$_sejour->_ref_curr_affectation->_ref_praticien}}
              {{/if}}
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir_tooltip}}
            </a>
          {{if $remplacant}})</span>{{/if}}
        </td>
      {{/if}}
  
      {{if $rpu->_id}}
        {{if $rpu->mutation_sejour_id}}
          {{mb_include template=inc_dossier_mutation colspan=1}}
        {{else}} 
          <td style="background-color: {{$background}}; text-align: center">
            {{if $rpu->_class_sfmu}}
              <i class="{{$rpu->_class_sfmu}}" style="font-size: 16pt; float: right;" title="{{mb_value object=$rpu->_ref_motif_sfmu field=libelle}}"></i>
            {{/if}}
            {{if $consult && $consult->_id}}
              {{if !$_sejour->sortie_reelle}}
                {{mb_include template=inc_icone_attente}}
              {{/if}}
              <a href="?m=urgences&tab=edit_consultation&selConsult={{$consult->_id}}">
                Consult. {{$consult->heure|date_format:$conf.time}}
                {{if $date != $consult->_ref_plageconsult->date}}
                <br/>le {{$consult->_ref_plageconsult->date|date_format:$conf.date}}
                {{/if}}
              </a>
              {{if !$_sejour->sortie_reelle}}
                ({{mb_value object=$rpu field=_attente}} / {{mb_value object=$rpu field=_presence}})
                {{if $rpu->sortie_autorisee}}
                  <div class="compact">{{mb_label object=$rpu field=date_sortie_aut}}: {{mb_value object=$rpu field=date_sortie_aut}}</div>
                {{/if}}
              {{elseif $_sejour->sortie_reelle}}
                {{if $_sejour->mode_sortie != "normal"}}
                  ({{mb_value object=$_sejour field=mode_sortie}}
                {{else}}
                  (sortie
                {{/if}}
                à {{$_sejour->sortie_reelle|date_format:$conf.time}})
              {{/if}}
            {{else}}
              {{mb_include template="inc_attente" sejour=$_sejour}}
            {{/if}}
          </td>
        {{/if}} 
      
        {{if $medicalView}}
          <td class="text compact" style="background-color: {{$background}};">
            {{if $admin_urgences}}
              <button class="edit notext me-tertiary" style="float: right;" title="{{tr}}CRPU-modif_diag_infirmier{{/tr}}"
                      onclick="fillDiag('{{$rpu->_id}}')"></button>
            {{/if}}
            {{if $rpu->date_at}}
              <span class="texticon texticon-at">{{tr}}CConsultation-AT{{/tr}}</span>
            {{/if}}
            {{if $rpu->motif && "dPurgences CRPU diag_prat_view"|gconf}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">
                <strong>{{mb_title class=$rpu field=motif}}</strong> : {{$rpu->motif|nl2br}}
              </span>
            {{else}}
             {{if $rpu->diag_infirmier}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">
                {{$rpu->diag_infirmier|nl2br}}
              </span>
              {{else}}
                {{if $rpu->motif_entree}}
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');" class="compact">
                    {{mb_label object=$rpu field="motif_entree"}} : {{$rpu->motif_entree|nl2br}}
                  </span> 
                {{/if}}
              {{/if}}
            {{/if}}
          </td>
        {{/if}}

        <td class="text" style="background-color: {{$background}};">
          {{mb_value object=$rpu field=pec_ioa}}
        </td>

        {{if "dPurgences Display check_date_pec_inf"|gconf}}
          <td class="text" style="background-color: {{$background}};">
              {{mb_value object=$rpu field=pec_inf}}
          </td>
        {{/if}}

        <td class="narrow button {{if !in_array($_sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$_sejour->praticien_id) && !$_sejour->UHCD}}arretee{{/if}}" style="background-color: {{$background}};">
          {{mb_include template="inc_pec_praticien" type=$type}}
        </td>
  
      {{else}}
        <!-- Pas de RPU pour ce séjour d'urgence -->
        <td colspan="{{$medicalView|ternary:3:2}}">
          <div class="small-warning">
            {{tr}}CRPU.no_assoc{{/tr}}
            <br />
            {{tr}}CRPU.no_assoc_clic{{/tr}}
            <a class="button action new" href="#1" onclick="{{$rpu_link}}">{{tr}}CRPU-title-create{{/tr}}</a>
          </div>
        </td>
      {{/if}}
      {{/if}}
    </tr>
  {{foreachelse}}
    <tr><td colspan="10" class="empty">{{tr}}CSejour.none_main_courante{{/tr}}</td></tr>
  {{/foreach}}
</table>
