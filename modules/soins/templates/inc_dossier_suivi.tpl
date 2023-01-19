{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=planSoinsInstalled value="planSoins"|module_active}}

{{if !$sejour->_id}}
  <div class="small-info">
    Veuillez sélectionner un séjour pour pouvoir accéder au suivi de soins.
  </div>
  {{mb_return}}
{{/if}}

<div class="small-error" id="sejour-load-error" style="display: none;">Une erreur s'est produite durant le chargement du dossier, veuillez réessayer.</div>

{{assign var=datetime_echelle value=$dtnow}}
{{if $date}}
  {{assign var=datetime_echelle value="`$date` `$tnow`"}}
{{/if}}

<script>
  // Fusion des transmissions
  onMergeComplete = function() {
    Soins.loadSuivi('{{$sejour->_id}}');
  };

  createConsult = function() {
    {{if $app->_ref_user->isAnesth()}}
    bindOperation('{{$sejour->_id}}');
    {{else}}
    onSubmitFormAjax(getForm('addConsultation'));
    {{/if}}
  };

  printDossierSoins = function(){
    new Url("soins", "print_dossier_soins")
            .addParam("sejour_id", "{{$sejour->_id}}")
            .popup("850", "500", "Dossier complet");
  };

  printPlanSoins = function() {
    var url = new Url("soins", "offline_plan_soins");
    url.addParam("sejours_ids", "{{$sejour->_id}}");
    url.addParam("mode_dupa", 1);
    url.pop(1000, 600);
  };

  Main.add(function() {
    window.tab_dossier_soin = Control.Tabs.create('tab_dossier_soin', true);
    window.tab_dossier_soin.activeLink.up('li').onmousedown();

    {{if $plan_soins_active}}
    PlanSoins.loadTraitement('{{$sejour->_id}}','{{$date}}','','administration', null, null, null, null, null, 1, null, null, null, '{{$datetime_echelle}}');
    {{/if}}
  });
</script>

<ul id="tab_dossier_soin" class="control_tabs me-border-width-1 me-no-border-radius me-border-only-bottom" style="text-align: left; {{if "soins Other vue_condensee_dossier_soins"|gconf}}border-width: 0;{{/if}}">
  {{if "dPprescription"|module_active && "planSoins"|module_active}}
    {{mb_ternary var=prescription test=$prescription value=$prescription other=$sejour->_ref_prescription_sejour}}
    <!-- Plan de soins journée -->
    <li onmousedown="if (window.refreshTabState) { window.refreshTabState(); }" class="jour">
      <a href="#jour">{{tr}}Soin-tabSuivi-tabViewDay{{/tr}}</a>
    </li>

    {{if "dPprescription general show_perop_suivi_soins"|gconf}}
      <li
        onmousedown="PlanSoins.showPeropAdministrationsOperation('{{$prescription->_id}}', '{{$sejour->_id}}', {{$sejour->_ref_last_operation->_id}})">
        <a href="#perop_adm" {{if $count_perop_adm == 0}}class="empty"{{/if}}>
          Perop {{if $count_perop_adm}}(<small>{{$count_perop_adm}}</small>){{/if}}
        </a>
      </li>
    {{/if}}
  {{/if}}

  <!-- Tâches -->
  <li onmousedown="Soins.updateTasks('{{$sejour->_id}}');" {{if "soins Other vue_condensee_dossier_soins"|gconf}}style="display: none;"{{/if}}>
    <a href="#tasks">
      Tâches
      <small>(&ndash; / &ndash;)</small>
      <script>
        Control.Tabs.setTabCount('tasks', {{$sejour->_count_pending_tasks}}, {{$sejour->_count_tasks}});
      </script>
    </a>
  </li>

  <!-- Evènements externe -->
  <li onmousedown="Soins.showRDVExternal('{{$sejour->_id}}');" {{if "soins Other vue_condensee_dossier_soins"|gconf}}style="display: none;"{{/if}}>
    <a href="#rdv_externe" {{if true}}class="empty"{{/if}}>
      {{tr}}CRDVExterne-court{{/tr}}
      <small>(&ndash;)</small>
      <script>
        Control.Tabs.setTabCount('rdv_externe', {{$sejour->_count_rdv_externe}});
      </script>
    </a>
  </li>

  <!-- Transmissions -->
  <li onmousedown="Soins.loadSuivi('{{$sejour->_id}}')" class="me-tabs-flex"
      {{if "soins Other vue_condensee_dossier_soins"|gconf}}style="display: none;"{{/if}}>
    <a href="#dossier_suivi">
      Trans. <small id="nb_trans"></small> / Obs
      {{if !"soins Other vue_condensee_dossier_soins"|gconf && "soins Observations manual_alerts"|gconf}}
        {{mb_include module=system template=inc_icon_alerts object=$sejour tag=observation callback="function() { Soins.compteurAlertesObs(`$sejour->_id`); PlanSoins.loadSuivi('`$sejour->_id`'); }" show_empty=1 show_span=1 event=onmouseover img_ampoule="ampoule_rose"}}
      {{/if}} . / Consult.
      / Const.
    </a>
  </li>


  <li onmousedown="Soins.updateObjectifs('{{$sejour->_id}}');">
    <a href="#objectif_soin" {{if $sejour->_count_objectifs_retard}}class="wrong"{{/if}}>
      {{tr}}Soin-tabSuivi-tabObjectifs{{/tr}}
      <small>(&ndash;)</small>
      <script>
        Control.Tabs.setTabCount('objectif_soin', {{$sejour->_count_objectifs_soins}});
      </script>
    </a>
  </li>

  {{if "psl"|module_active && "psl psl active"|gconf}}
    <!-- Dossier transfusionnel-->
    {{mb_script module=psl script=psl ajax=1}}
    <li onmousedown="Psl.loadDossierPSL('{{$sejour->_id}}');">
      <a href="#dossier_psl">{{tr}}PSL.Dossier{{/tr}}</a>
    </li>
  {{/if}}

  <li style="float: right;" class="me-tabs-buttons">
    {{if "soins Other vue_condensee_dossier_soins"|gconf}}
      <button type="button" class="search" onclick="Soins.showModalTasks('{{$sejour->_id}}');">
        {{tr}}CSejour-back-tasks{{/tr}}
        (<span id="tasks_count">{{$sejour->_count_pending_tasks}}/{{$sejour->_count_tasks}}</span>)
      </button>
      <button type="button" class="search" onclick="Soins.showModalRDVExternes('{{$sejour->_id}}');">
        {{tr}}CRDVExterne-court{{/tr}} (<span id="rdvs_count">{{$sejour->_count_rdv_externe}}</span>)
      </button>
    {{/if}}

    {{if $planSoinsInstalled && "planSoins general show_bouton_plan_soins"|gconf}}
      {{* Impression du plan de soins *}}
      {{me_button icon=print label="mod-soins-tab-offline_plan_soins" onclick="printPlanSoins()"}}
    {{/if}}
    {{* Impression du dossier de soins *}}
    {{me_button icon=print label="module-soins-court" onclick="printDossierSoins()"}}

    {{if $conf.ref_pays != "3"}}
      {{assign var=prescription_id value=""}}
      {{if isset($prescription|smarty:nodefaults)}}
        {{assign var=prescription_id value=$sejour->_ref_prescription_sejour->_id}}
      {{/if}}
      {{me_button icon=print label="CPrescription-action-Prescription"
      onclick="Prescription.printOrdonnance('$prescription_id')"}}
    {{/if}}

    {{me_dropdown_button button_icon=down button_label=Print use_anim=false button_class="me-secondary"
    container_class="me-dropdown-button-right me-float-right"}}
  </li>
</ul>

{{if "soins Other vue_condensee_dossier_soins"|gconf}}
  <hr class="me-no-display"/>
{{/if}}

<div id="jour" class="me-no-border me-bg-transparent" style="display:none"></div>

<div id="semaine" class="me-no-border" style="display:none"></div>

{{if "dPprescription general show_perop_suivi_soins"|gconf}}
  <div id="perop_adm" style="display: none"></div>
{{/if}}
<div id="tasks" style="display:none" class="me-padding-0"></div>
<div id="rdv_externe" style="display:none" class="me-padding-0"></div>
<div id="dossier_suivi" style="display:none" class="me-padding-0"></div>
<div id="objectif_soin" style="display: none;" class="me-padding-0"></div>
{{if "psl"|module_active && "psl psl active"|gconf}}
  <div id="dossier_psl" style="display:none"></div>
{{/if}}
