{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=planningOp   script=prestations register=true}}
{{mb_script module=hospi        script=modele_etiquette register=true}}
{{mb_script module=planningOp   script=planning register=true}}
{{mb_script module=soins        script=soins register=true}}
{{mb_script module=prescription script=prescription register=true}}
{{mb_script module=patients     script=identity_validator register=true}}

{{if "planSoins"|module_active}}
  {{mb_script module=planSoins script=plan_soins register=true}}
{{/if}}

{{if @$modules.dPpmsi->_can->edit}}
  {{mb_script module=pmsi script=PMSI register=true}}
  {{mb_script module=cim10 script=CIM register=true}}
  {{mb_script module=patients script=pat_selector register=true}}
{{/if}}

{{if "addictologie"|module_active}}
  {{mb_script module=addictologie script=dossier_addictologie register=true}}
{{/if}}

{{assign var=sejour       value=$object}}
{{assign var=patient      value=$object->_ref_patient}}
{{assign var=operations   value=$object->_ref_operations}}
{{assign var=affectations value=$object->_ref_affectations}}

<script>
  popEtatSejour = function(sejour_id) {
    var url = new Url('hospi', 'vw_parcours');
    url.addParam('sejour_id', sejour_id);
    url.requestModal(700, 550);
  };

  printDossierSejour = function(sejour_id) {
    var url = new Url('planningOp', "view_planning");
    url.addParam("sejour_id", sejour_id);
    url.popup(700, 800);
  };


  afterValideSortie = function(form) {
    form.up('div').hide().update();
    if (window.refreshMouvements) {
      refreshMouvements(loadNonPlaces);
    }
  };

  Main.add(function () {
    ModeleEtiquette.nb_printers = {{$sejour->_nb_printers|@json}};
    MediboardExt.addTogglableElement($('dropdown-button-sejour_{{$sejour->_id}}'));

    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}
  });
</script>

<table class="tbl tooltip">
  {{mb_include module=dPplanningOp template=inc_sejour_affectation_view}}
</table>

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  {{mb_include module=appFineClient template=count_orders_tooltip}}
{{/if}}

<table class="tbl tooltip">
  <tr>
    <th class="category {{if $sejour->sortie_reelle}}arretee{{/if}}" colspan="4">
      {{tr}}CSejour-_etat.{{$sejour->_etat}}{{/tr}}
      {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
    </th>
  </tr>

  {{if $sejour->annule == 1}}
    <tr>
      <th class="category cancelled" colspan="4">
        {{tr}}CSejour-annule{{/tr}}
        {{if $sejour->recuse == 1}}
          ({{tr}}CSejour.recuse.1{{/tr}})
        {{/if}}
      </th>
    </tr>
  {{/if}}

  <tr>
    <td class="button">
      {{mb_script module=planningOp script=sejour ajax=true}}
      {{mb_script module=dPadmissions script=admissions ajax=true}}

      {{if $object->_can->edit}}
        <button type="button" class="edit" onclick="Sejour.editModal('{{$sejour->_id}}');">
          {{tr}}Modify{{/tr}}
        </button>

        {{if !$sejour->entree_reelle}}
          <button class="tick" type="button"
                  onclick="IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}', Admissions.validerEntree.curry('{{$sejour->_id}}', false));">
            {{tr}}CSejour-action-Validate the entry{{/tr}}
          </button>
        {{else}}
          <button class="cancel" type="button"
                  onclick='Admissions.validerEntree("{{$sejour->_id}}", false);'>
            {{tr}}CSejour-action-Invalidate the entry{{/tr}}
          </button>
        {{/if}}

        {{if !$sejour->sortie_reelle && $sejour->entree_reelle}}
          <button class="tick" type="button" onclick='Admissions.validerSortie("{{$sejour->_id}}");'>
            {{tr}}CSejour-action-Validate the output{{/tr}}
          </button>
        {{elseif $sejour->sortie_reelle && $sejour->entree_reelle}}
          <button class="cancel" type="button"
                  onclick='Admissions.validerSortie("{{$sejour->_id}}");'>
            {{tr}}CSejour-action-Invalidate the output{{/tr}}
          </button>
        {{/if}}

        {{if "dPhospi prestations systeme_prestations"|gconf == "expert"}}
          <button type="button" class="search" onclick="Prestations.edit('{{$sejour->_id}}')">{{tr}}CSejour-back-prestations{{/tr}}</button>
        {{/if}}
      {{/if}}

      {{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && @$modules.dPadmissions->_can->read}}
        <button type="button" class="search" onclick="Sejour.admission('{{$sejour->_date_entree_prevue}}');">
          {{tr}}Admission{{/tr}}
        </button>
      {{/if}}

      {{if @$modules.soins->_can->read}}
        <button type="button" class="search" onclick="Sejour.showDossierSoinsModal('{{$sejour->_id}}')">
          {{tr}}module-soins-court{{/tr}}
        </button>
      {{/if}}

      {{if "transport"|module_active}}
        {{mb_include module=transport template=inc_buttons_transport object=$sejour}}
      {{/if}}

      {{if "speedCall"|module_active && ("speedCall Global contextual"|gconf === "1") && $modules.speedCall->_can->edit}}
        {{mb_include module=speedCall template=inc_buttons_transport object=$sejour}}
      {{/if}}

      {{if "forms"|module_active}}
        {{mb_include module=forms template=inc_widget_ex_class_register object=$sejour event_name=modification cssStyle="display: inline-block;"}}
      {{/if}}

      {{if "softway"|module_active}}
        {{mb_include module=softway template=inc_button_synthese notext="" _sejour=$sejour}}
      {{/if}}

      <div class='me-dropdown-button me-dropdown-button-right'>
        <button id='dropdown-button-sejour_{{$sejour->_id}}' type='button' class='opt me-tertiary notext'>
          {{tr}}Actions{{/tr}}
        </button>
        <div class='me-dropdown-content'>
          {{if @$modules.dPpmsi->_can->edit}}
            <button type="button" class="search" onclick="Sejour.showDossierPmsi('{{$sejour->_id}}', '{{$patient->_id}}');">
              {{tr}}mod-dPpmsi-tab-vw_dossier_pmsi{{/tr}}
            </button>
          {{/if}}

          {{if "addictologie"|module_active}}
            <button type="button" class="search" onclick="DossierAddictologie.showSejourTimeline('{{$sejour->_id}}', 1);">
              {{tr}}CDossierAddictologie{{/tr}}
            </button>
          {{/if}}

          {{if $sejour->type == "ssr" && @$modules.ssr->_can->read}}
            <button type="button" class="search" onclick="Sejour.showSSR('{{$sejour->_id}}');">
              {{tr}}module-{{$sejour->type}}-long{{/tr}}
            </button>
          {{/if}}

          {{if in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && @$modules.dPurgences->_can->read}}
            <button type="button" class="search" onclick="Sejour.showUrgences('{{$sejour->_id}}');">
              {{tr}}module-dPurgences-long{{/tr}}
            </button>
          {{/if}}

          <button type="button" class="search" onclick="Sejour.affectations('{{$sejour->_id}}');">
            {{tr}}CSejour-back-affectations{{/tr}}
          </button>

          <button type="button" class="search"
                  onclick="Prescription.printPrescription('{{$sejour->_ref_prescription_sejour->_id}}', {globale: 1, in_progress: 1, preview: 1, popup: 1});">
            {{tr}}CPrescription-traitements_en_cours{{/tr}}
          </button>

          <div class="separator"></div>

          {{if "planSoins"|module_active}}
            <button type="button" class="print" onclick="PlanSoins.printAdministrations('', '{{$sejour->_id}}')">
              {{if "dPplanningOp CSejour administration_with_timings_op"|gconf}}
                {{tr}}CSejour-administration_with_timings_op{{/tr}}
              {{else}}
                {{tr}}CMediusers-back-administrations{{/tr}}
              {{/if}}
            </button>
          {{/if}}

          <button type="button" class="print" onclick="printDossierSejour('{{$sejour->_id}}');">{{tr}}Print{{/tr}}</button>

          {{if "dPhospi"|module_active && $modules.dPhospi->_can->read}}
            <button type="button" class="print"
              {{if $sejour->_count_modeles_etiq == 1}}
              onclick="ModeleEtiquette.print('{{$sejour->_class}}', '{{$sejour->_id}}');"
              {{else}}
              onclick="ModeleEtiquette.chooseModele('{{$sejour->_class}}', '{{$sejour->_id}}')"
              {{/if}}>
              {{tr}}CModeleEtiquette-court{{/tr}}
            </button>
          {{/if}}

          <div class="separator"></div>

          <button type="button" class="lookup" onclick="popEtatSejour('{{$sejour->_id}}');">{{tr}}CSejour-Condition of stay{{/tr}}</button>
          <button type="button" class="fa fa-calendar" onclick="PlanningSejour.view('{{$sejour->_id}}');">{{tr}}CSejour-action-Planning of stay{{/tr}}</button>

          <div class="separator"></div>

          {{mb_include module=hospi template=inc_button_send_prestations_sejour notext="" _sejour=$sejour}}

          {{if "web100T"|module_active}}
            {{mb_include module=web100T template=inc_button_iframe notext="" _sejour=$sejour}}
          {{/if}}

          {{if "unilabs"|module_active}}
            {{mb_include module=unilabs template=inc_button_unilabs notext="" _sejour=$sejour}}
          {{/if}}

          {{if "novxtelHospitality"|module_active}}
            {{mb_include module=novxtelHospitality template=inc_button_novxtel_hospitality notext="" _sejour=$sejour }}
          {{/if}}

          {{if "cda"|module_active}}
            {{mb_script module=cda script=ccda ajax=true}}
            <button class="fas fa-plus" type="button" onclick="Ccda.generateVSM('{{$sejour->_id}}', '{{$sejour->_class}}');">{{tr}}CDA-msg-generate VSM{{/tr}}</button>
          {{/if}}
        </div>
      </div>

    </td>
  </tr>
</table>

{{if !$app->user_prefs.limit_prise_rdv}}
  <table class="tbl tooltip">
    {{mb_include module=cabinet template=inc_list_actes_ccam subject=$sejour vue=view}}
  </table>
{{/if}}
