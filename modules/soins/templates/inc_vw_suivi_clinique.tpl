{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=relation_type value=''}}

{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=pass_to_confirm value="dPplanningOp CSejour pass_to_confirm"|gconf}}

{{unique_id var=unique_id_soins}}

{{mb_script module=patients script=correspondant ajax=$ajax}}
{{mb_script module=system script=alert ajax=$ajax}}
{{mb_script module=dPadmissions script=admissions ajax=$ajax}}
{{mb_script module=planningOp script=planning ajax=$ajax}}
{{mb_script module=planningOp script=sejour ajax=$ajax}}
{{mb_script module=patients script=identity_validator ajax=$ajax}}

{{if "brancardage"|module_active}}
  {{mb_script module=brancardage script=brancardage ajax=$ajax}}
{{/if}}

<script>
  showTimeline = function (sejour_id) {
    var url = new Url('soins', 'sejour_timeline');
    url.addParam('sejour_id', sejour_id);
    url.requestModal(1100, 800);
  };

  modalViewComplete = function (object_guid, title) {
    var url = new Url("system", "httpreq_vw_complete_object");
    url.addParam("object_guid", object_guid);
    url.requestModal('90%', '98%', {title: title});
  };

  popEtatSejour = function (sejour_id) {
    var url = new Url("hospi", "vw_parcours");
    url.addParam("sejour_id", sejour_id);
    url.requestModal(1000, 700);
  };

  toggleProgressBefore = function () {
    $$(".in_progress_before").invoke("toggleClassName", "show_important");
    $$(".class_progress_before").invoke("toggleClassName", "selected");
  };
  toggleProgressAfter = function () {
    $$(".in_progress_after").invoke("toggleClassName", "show_important");
    $$(".class_progress_after").invoke("toggleClassName", "selected");
  };

  afterEditCorrespondant = function () {
    if (window.Soins) {
      Soins.loadSuiviClinique('{{$sejour->_id}}')
    } else if (window.reloadSynthese) {
      window.reloadSynthese();
    }
  };

  showAdvanceDirectives = function () {
    new Url("patients", "vw_list_directives_anticipees")
      .addParam("patient_id", "{{$patient->_id}}")
      .requestModal(null, null, {
        onClose: function () {
          var form = getForm("edit-sejour-frm");

          if (form) {
            Soins.loadSuiviClinique('{{$sejour->_id}}');
          }
        }
      });
  };

  checkAdvanceDirectives = function (elt) {
    if (elt.value == 1) {
      showAdvanceDirectives();
    }
  };

  toggleAutorisation = function (status) {
    var isPraticien = "{{$app->_ref_user->isPraticien()}}";
    var pass_to_confirm = "{{$pass_to_confirm}}";
    var form = getForm("edit-sejour-frm");

    if (status == 1) {
      if (isPraticien == "1" || pass_to_confirm == "0") {
        $V(form.confirme_user_id, User.id);
      }
      modal("confirmSortieModal", {width: "410px", height: "290px"});
    } else {
      if (isPraticien == "1" || pass_to_confirm == "0") {
        $V(form.confirme, "");
        $V(form.confirme_user_id, "");
        return form.onsubmit();
      } else {
        $V(form._cancel_confirme, 1);
        $("confirme_area").hide();
        modal("confirmSortieModal", {width: "410px", height: "290px"});
      }
    }
  };

  afterConfirmPassword = function () {
    var formFrom = getForm("confirmSortie");
    var formTo = getForm("edit-sejour-frm");
    var cancel_confirme = $V(formTo._cancel_confirme);
    $V(formTo.confirme, cancel_confirme == "1" ? "" : $V(formFrom.confirme));
    $V(formTo.confirme_user_id, cancel_confirme == "1" ? "" : $V(formFrom.user_id));
    formTo.onsubmit();
  };

  toggleDisplayDirectives = function () {
    var form = getForm("editDirectives");
    var directives_anticipees_status = $V(form.directives_anticipees_status);
    var area_directives = $("area_directives");
    if (directives_anticipees_status == 0 || directives_anticipees_status == "unknown") {
      $V(form.directives_anticipees, "", false);
      area_directives.hide();
    } else {
      area_directives.show();
    }
    form.onsubmit();
  };

  toggleDisplayTechniques = function () {
    var form = getForm("editTechniques");
    var technique_reanimation_status = $V(form.technique_reanimation_status);
    var area_techniques = $("area_techniques");
    if (technique_reanimation_status == 0 || technique_reanimation_status == "unknown") {
      $V(form.technique_reanimation, "", false);
      area_techniques.hide();
    } else {
      area_techniques.show();
    }
    form.onsubmit();
  };

  syntheseMed = function (prescription_id) {
    new Url("prescription", "ajax_synthese_med")
      .addParam("prescription_id", prescription_id)
      .requestModal("40%");
  };

  Main.add(function () {
    {{if "forms"|module_active}}
    ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "list-ex_objects", 0.5);
    {{/if}}

    var form = getForm("confirmSortie");
    {{if !$app->_ref_user->isPraticien() && $pass_to_confirm}}
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("input_field", form._user_view.name);
    url.autoComplete(form._user_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      width: '200px',
      afterUpdateElement: function (field, selected) {
        $V(form._user_view, selected.down('.view').innerHTML);
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.user_id, id);
      }
    });
    {{/if}}
    Calendar.regField(form.confirme);

    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}
  });
</script>

<div id="confirmSortieModal" style="display: none;">
  <form name="confirmSortie" method="post" action="?m=system&a=ajax_password_action"
        onsubmit="return onSubmitFormAjax(this, {useFormAction: true})">
    <input type="hidden" name="callback" value="afterConfirmPassword"/>
    <input type="hidden" name="user_id" class="notNull" value="{{$app->_ref_user->_id}}"/>
    <table class="form">
      <tr>
        <th class="title" colspan="2">
          {{tr}}CSejour-autorisation_sortie{{/tr}}
        </th>
      </tr>
      <tbody id="confirme_area">
      <tr>
        <th>
          {{tr}}CSejour-date_sortie_autorisee{{/tr}}
        </th>
        <td>
          <input name="confirme" type="hidden" class="dateTime" value="{{$sejour->sortie}}"/>
        </td>
      </tr>
      </tbody>
      {{if !$app->_ref_user->isPraticien() && $pass_to_confirm}}
        <tr>
          <th>{{tr}}common-User{{/tr}}</th>
          <td>
            <input type="text" name="_user_view" class="autocomplete" value="{{$app->_ref_user}}"/>
          </td>
        </tr>
        <tr>
          <th>
            <label for="user_password">{{tr}}password{{/tr}}</label>
          </th>
          <td>
            <input type="password" name="user_password" class="notNull password str"/>
          </td>
        </tr>
      {{/if}}
      <tr>
        <td colspan="2" class="button">
          {{if $app->_ref_user->isPraticien()}}
            <button type="button" class="tick singleclick"
                    onclick="afterConfirmPassword()">{{tr}}Validate{{/tr}}</button>
          {{else}}
            <button class="tick singleclick">{{tr}}Validate{{/tr}}</button>
          {{/if}}
          <button type="button" class="cancel singleclick"
                  onclick="Control.Modal.close(); {{if !$sejour->confirme}}getForm('edit-sejour-frm')._confirme.checked = false;{{/if}}">{{tr}}Cancel{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<table class="main" style="text-align: left; width: 100%">
  <tr>
    <!-- Informations sur le patient -->
    <td style="width: 50%; vertical-align: top">
      <table class="tbl me-table-card" style="table-layout: fixed;">
        <tr>
          <th colspan="2" class="category me-text-align-left">
      <span style="float: right" class="me-float-right">
        <button type="button" class="search me-tertiary me-dark"
                onclick="modalViewComplete('{{$patient->_guid}}', 'Détail du patient')">{{tr}}CPatient{{/tr}}</button>
      </span>
            <span style="float: left;" class="me-float-right">
        <button class="lookup notext me-tertiary me-dark" style="margin: 0;"
                onclick="popEtatSejour('{{$sejour->_id}}');">{{tr}}CSejour-_etat{{/tr}}</button>
      </span>
            {{tr}}CSejour-_coordonnees-court{{/tr}}
          </th>
        </tr>
        <tr>
          <td colspan="2">
            {{if $patient->nom_jeune_fille}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="nom_jeune_fille"}}</strong>
                {{mb_value object=$patient field="nom_jeune_fille"}}
              </div>
            {{/if}}
            {{if $patient->tel}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="tel"}}</strong>
                {{mb_value object=$patient field="tel"}}
              </div>
            {{/if}}
            {{if $patient->tel2}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="tel2"}}</strong>
                {{mb_value object=$patient field="tel2"}}
              </div>
            {{/if}}
            {{if $patient->tel_autre}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="tel_autre"}}</strong>
                {{mb_value object=$patient field="tel_autre"}}
              </div>
            {{/if}}
            {{if $patient->email}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="email"}}</strong>
                {{mb_value object=$patient field="email"}}
              </div>
            {{/if}}
            {{if $patient->profession}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="profession"}}</strong>
                {{mb_value object=$patient field="profession"}}
              </div>
            {{/if}}
            {{if $patient->rques}}
              <div class="cellule_patient">
                <strong>{{mb_label object=$patient field="rques"}}</strong>
                {{mb_value object=$patient field="rques"}}
              </div>
            {{/if}}
          </td>
        </tr>
      </table>

      <!-- Correspondance -->
      <table class="tbl me-table-card-list">
        <tr>
          <th class="section" colspan="5">{{tr}}CCorrespondantPatient|pl{{/tr}}</th>
        </tr>
        <tr>
          <th class="narrow">
            <button type="button" class="add notext me-secondary" style="float: left;"
                    onclick="Correspondant.edit(0, '{{$patient->_id}}', afterEditCorrespondant);"></button>
          </th>
          <th class="narrow">{{tr}}CCorrespondantPatient-relation{{/tr}}</th>
          <th class="">
            {{tr}}CCorrespondantPatient-nom{{/tr}} / {{tr}}CCorrespondantPatient-prenom{{/tr}}
          </th>
          <th class="" style="width: 105px;">
            {{tr}}CCorrespondantPatient-mob{{/tr}}
          </th>
          <th class="" style="width: 165px;">
            {{tr}}CCorrespondantPatient-tel{{/tr}}
          </th>
        </tr>
        {{foreach from=$patient->_ref_cp_by_relation item=_correspondants}}
          {{foreach from=$_correspondants item=_correspondant}}
            <tr>
              <td>
                <button class="copy notext me-tertiary me-low-emphasis"
                        onclick="Correspondant.edit('{{$_correspondant->_id}}', null, afterEditCorrespondant, 1)">{{tr}}CCorrespondantPatient-copy{{/tr}}</button>
              </td>
              <td>
                {{if $_correspondant->relation == "autre" && $_correspondant->parente}}
                  {{assign var=relation_type value=$_correspondant->parente}}
                {{elseif $_correspondant->relation == "autre" && $_correspondant->relation_autre}}
                  {{assign var=relation_type value=$_correspondant->relation_autre}}
                {{else}}
                  {{assign var=relation_type value=''}}
                {{/if}}
                <strong>{{mb_value object=$_correspondant field=relation}} {{if $relation_type}}({{$relation_type}}){{/if}}</strong>
              </td>
              <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_correspondant->_guid}}')">
            {{mb_value object=$_correspondant field="nom"}}
            {{mb_value object=$_correspondant field="prenom"}}
          </span>
              </td>
              <td>{{mb_value object=$_correspondant field="mob"}}</td>
              <td>{{mb_value object=$_correspondant field="tel"}}</td>
            </tr>
          {{/foreach}}
        {{/foreach}}
        {{if !$patient->_ref_correspondants_patient|@count}}
          <tr>
            <td colspan="5" class="empty">
              {{tr}}CCorrespondantPatient.none{{/tr}}
            </td>
          </tr>
        {{/if}}
        {{if $patient->allow_pers_confiance == 0}}
            <tr>
                <td colspan="5" class="empty">
                    <div class="small-info">{{tr}}AppFineClient-msg-Patient does not want confiance person{{/tr}}</div>
                </td>
            </tr>
        {{/if}}
        {{if $patient->allow_pers_prevenir == 0}}
            <tr>
                <td colspan="5" class="empty">
                    <div class="small-info">{{tr}}AppFineClient-msg-Patient does not want prevent person{{/tr}}</div>
                </td>
            </tr>
        {{/if}}
      </table>

      <!-- Correspondants médicaux -->
      <table class="tbl me-table-card-list">
        <tr>
          <th class="section" colspan="4">{{tr}}CMedecin|pl{{/tr}}</th>
        </tr>
        <tr>
          <th class="narrow">
            <button type="button" class="add notext me-secondary" style="float: left;"
                    title="Ajouter un correspondant médical"
                    onclick="Patient.editModal('{{$patient->_id}}', null, null, afterEditCorrespondant, 'medecins');"></button>
          </th>
          <th class="">
            {{mb_label class=CMedecin field=nom}} / {{mb_label class=CMedecin field=prenom}}
          </th>
          <th class="" style="width: 105px;">{{mb_label class=CMedecin field=portable}}</th>
          <th class="" style="width: 165px;">{{mb_label class=CMedecin field=tel}}</th>
        </tr>
        {{if $patient->medecin_traitant}}
          <tr>
            <td></td>
            <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_medecin_traitant->_guid}}')">
        {{mb_value object=$patient->_ref_medecin_traitant field=nom}}
        {{mb_value object=$patient->_ref_medecin_traitant field=prenom}}
        ({{tr}}CMediusers-back-medecin{{/tr}})
      </span>
            </td>
            <td>{{mb_value object=$patient->_ref_medecin_traitant field=portable}}</td>
            <td>{{mb_value object=$patient->_ref_medecin_traitant field=tel}}</td>
          </tr>
        {{/if}}
        {{if $patient->pharmacie_id}}
          <tr>
            <td></td>
            <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_pharmacie->_guid}}')">
        {{mb_value object=$patient->_ref_pharmacie field=nom}}
        {{mb_value object=$patient->_ref_pharmacie field=prenom}}
      </span>
            </td>
            <td>{{mb_value object=$patient->_ref_pharmacie field=portable}}</td>
            <td>{{mb_value object=$patient->_ref_pharmacie field=tel}}</td>
          </tr>
        {{/if}}
        {{foreach from=$patient->_ref_medecins_correspondants item=_correspondant_medical}}
          <tr>
            <td></td>
            <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_correspondant_medical->_guid}}')">
        {{mb_value object=$_correspondant_medical->_ref_medecin field=nom}}
        {{mb_value object=$_correspondant_medical->_ref_medecin field=prenom}}
      </span>
            </td>
            <td>{{mb_value object=$_correspondant_medical->_ref_medecin field=portable}}</td>
            <td>{{mb_value object=$_correspondant_medical->_ref_medecin field=tel}}</td>
          </tr>
        {{/foreach}}

        {{if $patient->medecin_traitant_declare === "0"}}
          <tr>
            <td colspan="5">{{tr}}CPatient-Patient doesnt have a GP{{/tr}}</td>
          </tr>
        {{/if}}

        {{if !$patient->_ref_medecins_correspondants|@count && $patient->medecin_traitant_declare !== "0" && !$patient->medecin_traitant}}
          <tr>
            <td colspan="5" class="empty">{{tr}}CCorrespondant.none medical{{/tr}}</td>
          </tr>
        {{/if}}
      </table>

      <!--  Informations sur le séjour -->
      <form name="edit-sejour-frm" method="post" action="?"
            onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); Soins.loadSuiviClinique('{{$sejour->_id}}'); })">
        <input type="hidden" name="m" value="planningOp"/>
        <input type="hidden" name="dosql" value="do_sejour_aed"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="_cancel_confirme" value="0"/>
        {{mb_field object=$sejour field=confirme hidden=true}}
        {{mb_field object=$sejour field=confirme_user_id hidden=true}}
        {{mb_field object=$sejour field=entree_prevue hidden=true}}
        {{mb_key object=$sejour}}
        <table class="tbl me-table-card">
          <tr>
            <th class="category me-text-align-left" colspan="2">
              {{me_button label="CSejour-action-Planning of stay" icon="fa fa-calendar" onclick="PlanningSejour.view('`$sejour->_id`')"}}
              <span style="float: right;margin-left:-50px;">
          {{me_button label="Chronologie séjour" icon="fa fa-list-ul" onclick="showTimeline('`$sejour->_id`')"}}
                {{me_button label=Details icon=search onclick="modalViewComplete('`$sejour->_guid`')"}}

                {{me_dropdown_button button_label=Options button_icon=opt button_class="notext me-tertiary me-dark"
                container_class="me-dropdown-button-right"}}
        </span>
              {{tr}}CSejour{{/tr}}
            </th>
          </tr>
          <tr>
            <td style="width: 50%;">
              <strong>{{mb_label object=$sejour field="libelle"}}</strong>
              {{mb_value object=$sejour field="libelle"}}
            </td>
            <td>
              <strong>{{mb_label object=$sejour field="praticien_id"}}</strong>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
            </td>
          </tr>
          <tr>
            <td>
              <strong>{{mb_label object=$sejour field="entree"}}{{if $sejour->entree_reelle}} ({{tr}}common-performed{{/tr}}){{/if}}</strong>
              {{mb_value object=$sejour field="entree"}}
            </td>
            <td>
        <span onmouseover="ObjectTooltip.createDOM(this, 'confirme_sortie_{{$sejour->_id}}');">
          <strong>{{mb_label object=$sejour field="sortie"}}
            {{if $sejour->sortie_reelle}} (effectuée){{/if}}</strong>
          {{mb_value object=$sejour field="sortie"}}
          <span {{if $sejour->confirme}}class="ok"{{/if}}>
            {{if $sejour->confirme}}
              <br/>
              <strong>Sortie autorisée</strong>
               pour le {{mb_value object=$sejour field="confirme"}}
              <br/>
              Par {{$sejour->_ref_confirme_user->_view}}
            {{/if}}
          </span>
          {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
            <br/>
            <strong>{{mb_label object=$sejour field="etablissement_sortie_id"}}</strong>
            {{mb_value object=$sejour field="etablissement_sortie_id"}}
          {{/if}}
        </span>
              <div id="confirme_sortie_{{$sejour->_id}}" style="display: none">
                {{mb_include module=planningOp template=inc_vw_sortie_sejour}}
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <strong>{{mb_label object=$sejour field="type"}}</strong>
              {{mb_value object=$sejour field="type"}}
            </td>
            <td>
              <button type="button" class="edit me-primary"
                      onclick='Admissions.validerSortie("{{$sejour->_id}}", {{if $sejour->type == "ambu" && "dPplanningOp CSejour sortie_reelle_ambu"|gconf}}false{{else}}true{{/if}}, window.Soins && Soins.loadSuiviClinique.curry("{{$sejour->_id}}"));'>
                {{tr}}CSejour-autorisation_sortie{{/tr}}
              </button>
              {{if "transport"|module_active}}
                {{mb_include module=transport template=inc_buttons_transport object=$sejour text=false}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <td>
              {{if $sejour->entree_reelle}}
                <strong>{{mb_label object=$sejour field=entree_reelle}}</strong>
                {{mb_value object=$sejour field=entree_reelle}}
              {{else}}
                <button type="button" class="tick me-tertiary"
                        onclick="IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}', Admissions.validerEntree.curry('{{$sejour->_id}}', null, afterEditCorrespondant));">
                  {{tr}}CSejour-action-Validate the entry{{/tr}}
                </button>
              {{/if}}
            </td>
            <td>
              {{if $sejour->pec_service}}
                <strong>{{mb_label object=$sejour field=pec_service}}</strong>
                {{mb_value object=$sejour field=pec_service}}
              {{else}}
                {{mb_field object=$sejour field=pec_service hidden=true}}
                <button type="button" class="tick me-tertiary"
                        onclick="$V(this.form.pec_service, 'now');this.form.onsubmit();">{{tr}}CSejour-pec_service-court{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
          <tr>
            <td>
              <strong>{{mb_label object=$sejour field=rques text="CSejour-rques-court"}}</strong>
              <span id="CSejour-rques" class="text compact" style="white-space: normal;">{{$sejour->rques}}</span>
            </td>
              {{if "brancardage"|module_active}}
                <td>
                  {{**** DEMANDE DE BRANCARDAGE POUR UN SEJOUR ****}}
                  <div id="brancardage-{{$sejour->_guid}}">
                    {{mb_include module=brancardage template=inc_exist_brancard colonne="patientPret"
                    object=$sejour brancardage_to_load="infini"}}
                  </div>
                </td>
              {{/if}}
          </tr>
          <tr>
            <td colspan="2">
              <strong>{{mb_label object=$sejour field=prestation_id}}</strong>

              {{if "dPhospi prestations systeme_prestations"|conf:"CGroups-`$sejour->group_id`" === "standard"}}
                {{mb_value object=$sejour field=prestation_id}}
              {{else}}
                {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <td>
              <button type="button" class="search" onclick="Sejour.editAutorisationsPermission('{{$sejour->_id}}');">
                {{tr}}CAutorisationPermission{{/tr}}
              </button>
            </td>
            <td class="halfPane">
              {{if $sejour->_ref_autorisations_permission|@count}}
                <table>
                  {{foreach from=$sejour->_ref_autorisations_permission item=_auto_permission}}
                    <tr>
                      <td>
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_auto_permission->_guid}}');">
                    <span class="warning">
                      {{$_auto_permission}}
                      <br/>
                      {{tr}}common-By{{/tr}} {{mb_value object=$_auto_permission field=praticien_id}}
                    </span>
                  </span>
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>

      {{if "soins synthese show_directives"|gconf}}
        {{assign var=permission_directive value=true}}
        {{if "soins synthese can_edit_directives"|gconf == "praticien" && !$app->_ref_user->isPraticien() && !$app->_ref_user->isAdmin()}}
          {{assign var=permission_directive value=false}}
        {{/if}}
        {{if $sejour->directives_anticipees_status == 1}}
          <form name="editDirectives" method="post" onsubmit="return onSubmitFormAjax(this);">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_sejour_aed"/>
            {{mb_key object=$sejour}}
            <table class="tbl">
              <tr>
                <td colspan="2">
                  <strong>{{tr}}CSejour-directives_anticipees_status-desc{{/tr}}</strong>
                  {{if !$permission_directive && $sejour->directives_anticipees_status == "1" && $sejour->directives_anticipees}}
                    {{mb_field object=$sejour field=directives_anticipees_status typeEnum="radio" readonly=true}}
                  {{else}}
                    {{mb_field object=$sejour field=directives_anticipees_status typeEnum="radio" onchange="toggleDisplayDirectives();"}}
                  {{/if}}
                  <div id="area_directives"
                       {{if $sejour->directives_anticipees_status != 1}}style="display: none;"{{/if}}>
            <textarea name="directives_anticipees" onchange="this.form.onsubmit();"
                      {{if $permission_directive}}readonly{{/if}}>{{$sejour->directives_anticipees}}</textarea>
                    <script>
                      Main.add(function () {
                        new AideSaisie.AutoComplete(getForm("editDirectives").elements["directives_anticipees"], {objectClass: "CSejour"});
                      });
                    </script>
                  </div>
                </td>
              </tr>
            </table>
          </form>
        {{/if}}
        {{*Directives anticipées pour le patient*}}
        {{assign var=directive_patient value=$patient->_ref_last_directive_anticipee}}
        <table class="tbl">
          <tr>
            <td colspan="2">
              <form name="editDirectivesPatient" method="post"
                    onsubmit="return onSubmitFormAjax(this, {onComplete: function() { checkAdvanceDirectives(getForm('editDirectivesPatient').directives_anticipees); }});">
                {{mb_class object=$patient}}
                {{mb_key object=$patient}}
                <strong>{{tr}}CPatient-directives_anticipees{{/tr}}</strong>
                {{if !$permission_directive && $patient->directives_anticipees == "1" && $directive_patient->_id}}
                  {{mb_field object=$patient field=directives_anticipees typeEnum="radio" readonly=true}}
                {{else}}
                  {{mb_field object=$patient field=directives_anticipees typeEnum="radio" onchange="this.form.onsubmit();"}}
                {{/if}}
              </form>
              {{if $patient->directives_anticipees == 1}}
                <button type="button" class="search me-tertiary"
                        title="{{tr}}CDirectiveAnticipee-action-See advance directive|pl{{/tr}}"
                        onclick="showAdvanceDirectives();">{{tr}}CDirectiveAnticipee-action-Patient directive|pl{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
          {{if $directive_patient->_id}}
            <tr>
              <th class="section" colspan="5">{{tr}}CDirectiveAnticipee-action-Patient last directives{{/tr}}</th>
            </tr>
            <tr>
              <th>{{mb_label class=CDirectiveAnticipee field=date_recueil}}</th>
              <th>{{mb_label class=CDirectiveAnticipee field=date_validite}}</th>
              <th class="text">{{mb_label class=CDirectiveAnticipee field=description}}</th>
              <th class="narrow">{{mb_label class=CDirectiveAnticipee field=detenteur_id}}</th>
            </tr>
            <tr>
              <td>{{mb_value object=$directive_patient field=date_recueil}}</td>
              <td>{{mb_value object=$directive_patient field=date_validite}}</td>
              <td>{{mb_value object=$directive_patient field=description}}</td>
              <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$directive_patient->_ref_detenteur->_guid}}')">
            {{$directive_patient->_ref_detenteur->_longview}}
          </span>
              </td>
            </tr>
          {{/if}}
        </table>
      {{/if}}

      {{if "soins synthese show_technique_rea"|gconf}}
        <form name="editTechniques" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="planningOp"/>
          <input type="hidden" name="dosql" value="do_sejour_aed"/>
          {{mb_key object=$sejour}}
          <table class="tbl">
            <tr>
              <td colspan="2">
                <strong>{{tr}}CSejour-technique_reanimation_status-desc{{/tr}}</strong>
                {{assign var=permission_techniques value=true}}
                {{if "soins synthese can_edit_technique_rea"|conf:"CGroups-$g" == "praticien" && !$app->_ref_user->isPraticien() && !$app->_ref_user->isAdmin()}}
                  {{assign var=permission_techniques value=false}}
                {{/if}}
                {{if !$permission_techniques && $sejour->technique_reanimation_status == "1" && $sejour->technique_reanimation}}
                  {{mb_field object=$sejour field=technique_reanimation_status typeEnum="radio" readonly=true}}
                {{else}}
                  {{mb_field object=$sejour field=technique_reanimation_status typeEnum="radio" onchange="toggleDisplayTechniques();"}}
                {{/if}}

                <div id="area_techniques"
                     {{if $sejour->technique_reanimation_status != 1}}style="display: none;"{{/if}}>
            <textarea name="technique_reanimation" onchange="this.form.onsubmit();"
                      {{if !$permission_techniques}}readonly{{/if}}>{{$sejour->technique_reanimation}}</textarea>
                  <script>
                    Main.add(function () {
                      new AideSaisie.AutoComplete(getForm("editTechniques").elements["technique_reanimation"], {objectClass: "CSejour"});
                    });
                  </script>
                </div>
              </td>
            </tr>
          </table>
        </form>
      {{/if}}

      {{mb_include module=planningOp template=inc_infos_operation alert=1}}
      {{mb_include module=cabinet template=inc_infos_consultation}}

      {{if $sejour->_ref_transmissions|@count}}
        <table class="tbl me-table-card-list">
          <tr>
            <th class="title" colspan="6">Transmissions de synthèse</th>
          </tr>
          <tr>
            <th class="narrow">{{tr}}Date{{/tr}}</th>
            <th class="narrow">Cible</th>
            <th>Donnée</th>
            <th>Action</th>
            <th>Résultat</th>
          </tr>
          {{foreach from=$sejour->_ref_transmissions item=_suivi}}
            <tr class="{{if $_suivi[0]->degre == "high"}}transmission_haute{{/if}}
                 {{if $_suivi[0]->locked && $_suivi[0]->cible_id}}hatching{{/if}}">
              {{mb_include module=hospi template=inc_line_suivi show_type=false show_patient=false readonly=true show_link=false}}
            </tr>
          {{/foreach}}
        </table>
      {{/if}}

      {{if $sejour->_ref_observations|@count}}
        <table class="tbl">
          <tr>
            <th colspan="7" class="title">{{tr}}CSejour-back-observations{{/tr}}</th>
          </tr>
          <tr>
            <th class="narrow">{{tr}}Date{{/tr}}</th>
            <th class="narrow">{{tr}}Hour{{/tr}}</th>
            <th>{{mb_title class=CObservationMedicale field=text}}</th>
          </tr>
          {{foreach from=$sejour->_ref_observations item=_obs}}
            <tr>
              <td style="text-align: center; height: 22px;">
                {{mb_ditto name=date_obs value=$_obs->date|date_format:$conf.date}}
              </td>
              <td style="text-align: center;">
                {{$_obs->date|date_format:$conf.time}}
              </td>
              <td class="text libelle_trans">{{mb_value object=$_obs field=text}}</td>
            </tr>
          {{/foreach}}
        </table>
      {{/if}}

      {{if $sejour->_refs_rdv_externes|@count}}
        <table class="tbl">
          <tr>
            <th class="title">{{tr}}CRDVExterne-court{{/tr}} <small>({{$sejour->_refs_rdv_externes|@count}})</small>
            </th>
          </tr>
        </table>
        {{mb_include module=soins template=inc_vw_rdv_externe readonly=1 header=0}}
      {{/if}}

      {{if $sejour->_ref_tasks|@count}}
        <table class="tbl">
          <tr>
            <th class="title">{{tr}}CSejour-ongoing-tasks{{/tr}} <small>({{$sejour->_ref_tasks|@count}})</small></th>
          </tr>
        </table>
        {{mb_include module=soins template=inc_vw_tasks_sejour mode_realisation=0 readonly=1 header=0}}
      {{/if}}
    </td>

    <td style="vertical-align: top;" rowspan="2">

      {{if "dPprescription"|module_active && $show_prescription}}
        {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
        <table class="tbl me-table-card">
          <tr>
            <th class="title">
              <button type="button" style="float: right;" class="search class_progress_after me-tertiary"
                      onclick="toggleProgressAfter();" title="{{tr}}CPrescription.in_progress_after{{/tr}}">
                +{{$days_config}}J
              </button>
              <button type="button" style="float: right;" class="search class_progress_before me-tertiary"
                      onclick="toggleProgressBefore();" title="{{tr}}CPrescription.in_progress_before{{/tr}}">
                -{{$days_config}}J
              </button>
              <button type="button" style="float: left;" class="search me-tertiary me-float-right"
                      onclick="modalPrescriptionLegend = Modal.open($('modal-prescription-legend-{{$unique_id_soins}}'), {height: '140px', width: '350px'});"
                      title="{{tr}}Legend{{/tr}}">{{tr}}Legend{{/tr}}</button>
              {{tr}}CPrescription.in_progress{{/tr}}
            </th>
            {{if $prescription->_ref_lines_med_comments.med|@count || $prescription->_ref_lines_med_comments.comment|@count ||
            $prescription->_ref_prescription_line_mixes|@count || $prescription->_ref_lines_elements_comments|@count}}
            {{if $prescription->_ref_lines_med_comments.med|@count}}
          <tr>
            <th>
              <button type="button" style="float: left;" class="search me-tertiary me-float-right"
                      onclick="syntheseMed('{{$prescription->_id}}');">
                {{tr}}prescription-synthese_med{{/tr}}
              </button>
              {{tr}}CPrescription._chapitres.med{{/tr}}
            </th>
          </tr>
          <!-- passé -->
          <tr class="hatching in_progress_before opacity-60">
            <td class="text">
              {{assign var=is_first value=1}}
              {{assign var=total value=0}}
              {{foreach from=$prescription->_ref_lines_med_comments.med item=_line}}
                {{if $_line->_fin_reelle && $_line->_fin_reelle < $date && $_line->_fin_reelle >= $date_before}}
                  {{if !$is_first}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first value=0}}
                  {{math equation="x+1" x=$total assign=total}}
                  {{$_line->_ref_produit->ucd_view}}
                {{/if}}
              {{/foreach}}
              {{if $total == 0}}
                <div class="empty">Aucune ligne de médicament passée</div>
              {{/if}}
            </td>
          </tr>
          <!-- Maintenant -->
          <tr>
            <td class="text">
              {{assign var=is_first value=1}}
              {{assign var=total value=0}}
              {{foreach from=$prescription->_ref_lines_med_comments.med item=_line}}
                {{if ($_line->_fin_reelle && $_line->_fin_reelle >= $date) &&
                $_line->_debut_reel && $_line->_debut_reel <= $date}}
                  {{if !$is_first}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first value=0}}
                  {{math equation="x+1" x=$total assign=total}}
                  {{if $_line->_fin_relative}}
                    {{math assign=prolongation_time_day equation="x / 24" x="dPprescription general prolongation_time"|gconf}}
                    {{math assign=prescription_end_real equation="x - y" x=$_line->_fin_relative y=$prolongation_time_day|intval}}
                  {{/if}}
                  <span
                    {{if $_line->_fin_reelle|iso_date <= $date_after|iso_date}}style="border-bottom: 2px solid orange"{{/if}}
                    {{if ($_line->warning_day &&
                    (($_line->duree && $_line->_fin_relative <= $_line->warning_day) ||
                    (!$_line->duree && $prescription_end_real <= $_line->warning_day))) ||
                    ($_line->warning_day_after && $_line->_debut_relatif && $_line->_debut_relatif >= $_line->warning_day_after)}}
                      style="border-bottom: 2px solid orangered"
                    {{/if}}
                class="{{if $_line->highlight}}highlight_red{{/if}}"
                    onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}')">
                        {{$_line->_ref_produit->ucd_view}}
                    {{if $_line->_alerte_antibio}}
                      <label title="Réévaluation antibiothérapie"
                             style="font-weight: bold; color: red;">(Reeval ATB)</label>
                    {{/if}}
                      </span>
                {{/if}}
              {{/foreach}}
              {{if $total == 0}}
                <div class="empty">Aucune ligne de médicament en cours</div>
              {{/if}}
            </td>
          </tr>
          <!-- A venir -->
          <tr class="text in_progress_after opacity-60">
            <td>
              {{assign var=is_first value=1}}
              {{assign var=total value=0}}
              {{foreach from=$prescription->_ref_lines_med_comments.med item=_line}}
                {{if $_line->_debut_reel && $_line->_debut_reel > $date && $_line->_debut_reel <= $date_after}}
                  {{if !$is_first}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first value=0}}
                  {{math equation="x+1" x=$total assign=total}}
                  {{$_line->_ref_produit->ucd_view}}
                {{/if}}
              {{/foreach}}
              {{if $total == 0}}
                <div class="empty">Aucune ligne de médicament à venir</div>
              {{/if}}
            </td>
          </tr>
          {{/if}}

          {{if $prescription->_ref_prescription_line_mixes_by_type|@count}}
            {{foreach from=$prescription->_ref_prescription_line_mixes_by_type item=_lines_by_type key=chap}}
              <tr>
                <th>{{tr}}CPrescription._chapitres.{{$chap}}{{/tr}}</th>
              </tr>
              <tr class="hatching in_progress_before opacity-60">
                <td class="text">
                  {{assign var=total value=0}}
                  {{assign var=is_first value=1}}
                  {{foreach from=$_lines_by_type item=_line}}
                    {{if $_line->_fin && $_line->_fin < $date && $_line->_fin >= $date_before}}
                      {{if !$is_first}}
                        &ndash;
                      {{/if}}
                      {{assign var=is_first value=0}}
                      {{math equation="x+1" x=$total assign=total}}
                      {{$_line->_libelle_voie}}
                      ({{$_line->_compact_view}})
                    {{/if}}
                  {{/foreach}}
                  {{if $total == 0}}
                    <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$chap}}{{/tr}} passée</div>
                  {{/if}}
                </td>
              </tr>
              <tr>
                <td class="text">
                  {{assign var=total value=0}}
                  {{assign var=is_first value=1}}
                  {{foreach from=$_lines_by_type item=_line}}
                    {{if ($_line->_fin && $_line->_fin >= $date) && ($_line->_debut && $_line->_debut <= $date)}}
                      {{if !$is_first}}
                        &ndash;
                      {{/if}}
                      {{assign var=is_first value=0}}
                      {{math equation="x+1" x=$total assign=total}}
                      <span
                        {{if $_line->_fin|iso_date <= $date_after|iso_date}}style="border-bottom: 2px solid orange"{{/if}}
                        {{if ($_line->warning_day && $_line->_fin_relative == $_line->warning_day) ||
                        ($_line->warning_day_after && $_line->_debut_relatif == $_line->warning_day_after)}}
                          style="border-bottom: 2px solid orangered"
                        {{/if}}
                    class="{{if $_line->highlight}}highlight_red{{/if}}"
                        onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}')">
                          {{$_line->_libelle_voie}}
                ({{$_line->_compact_view}})
                {{if $_line->_alerte_antibio}}
                  <label title="Réévaluation antibiothérapie"
                         style="font-weight: bold; color: red;">(Reeval ATB)</label>
                {{/if}}
                        </span>
                    {{/if}}
                  {{/foreach}}
                  {{if $total == 0}}
                    <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$chap}}{{/tr}} en cours</div>
                  {{/if}}
                </td>
              </tr>
              <tr class="in_progress_after opacity-60">
                <td class="text">
                  {{assign var=total value=0}}
                  {{assign var=is_first value=1}}
                  {{foreach from=$_lines_by_type item=_line}}
                    {{if $_line->_debut && $_line->_debut > $date && $_line->_debut <= $date_after}}
                      {{if !$is_first}}
                        &ndash;
                      {{/if}}
                      {{assign var=is_first value=0}}
                      {{math equation="x+1" x=$total assign=total}}
                      {{$_line->_libelle_voie}}
                      ({{$_line->_compact_view}})
                    {{/if}}
                  {{/foreach}}
                  {{if $total == 0}}
                    <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$chap}}{{/tr}} à venir</div>
                  {{/if}}
                </td>
              </tr>
            {{/foreach}}
          {{/if}}

          {{assign var=display_cat_for_elt value="dPprescription general display_cat_for_elt"|gconf}}

          {{foreach from=$prescription->_ref_lines_elements_comments item=chap_element key=_chap_name}}
            <tr>
              <th>{{tr}}CPrescription._chapitres.{{$_chap_name}}{{/tr}}</th>
            </tr>
            <tr class="hatching in_progress_before opacity-60">
              <td class="text">
                {{assign var=total value=0}}
                {{assign var=is_first_chap value=1}}
                {{assign var=count_elts value=0}}
                {{foreach from=$chap_element item=cat_element}}
                  {{if !$is_first_chap && $count_elts && $cat_element.element|@count > 0}}
                    {{assign var=count_elts value=0}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first_chap value=0}}
                  {{assign var=is_first_cat value=1}}
                  {{foreach from=$cat_element.element item=element}}
                    {{if $element->_fin_reelle && $element->_fin_reelle < $date && $element->_fin_reelle >= $date_before}}
                      {{if !$is_first_cat}}
                        &ndash;
                      {{elseif $display_cat_for_elt}}
                        <strong>{{$element->_ref_element_prescription->_ref_category_prescription->nom}} : </strong>
                      {{/if}}
                      {{assign var=is_first_cat value=0}}
                      {{math equation="x+1" x=$count_elts assign=count_elts}}
                      {{math equation="x+1" x=$total assign=total}}
                      {{$element->_view}}
                    {{/if}}
                  {{/foreach}}
                {{/foreach}}
                {{if $total == 0}}
                  <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$_chap_name}}{{/tr}} passée</div>
                {{/if}}
              </td>
            </tr>
            <tr>
              <td class="text">
                {{assign var=total value=0}}
                {{assign var=is_first_chap value=1}}
                {{assign var=count_elts value=0}}
                {{foreach from=$chap_element item=cat_element}}
                  {{if !$is_first_chap && $count_elts && $cat_element.element|@count > 0}}
                    {{assign var=count_elts value=0}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first_chap value=0}}
                  {{assign var=is_first_cat value=1}}
                  {{foreach from=$cat_element.element item=element}}
                    {{if ($element->_fin_reelle && $element->_fin_reelle >= $date) && ($element->_debut_reel && $element->_debut_reel <= $date)}}
                      {{if !$is_first_cat}}
                        &ndash;
                      {{elseif $display_cat_for_elt}}
                        <strong>{{$element->_ref_element_prescription->_ref_category_prescription->nom}} : </strong>
                      {{/if}}
                      {{assign var=is_first_cat value=0}}
                      {{math equation="x+1" x=$total assign=total}}
                      {{math equation="x+1" x=$count_elts assign=count_elts}}
                      {{if $element->_fin_relative}}
                        {{math assign=prolongation_time_day equation="x / 24" x="dPprescription general prolongation_time"|gconf}}
                        {{math assign=prescription_end_real equation="x - y" x=$element->_fin_relative y=$prolongation_time_day|intval}}
                      {{/if}}
                      <span
                        {{if $element->_fin_reelle|iso_date <= $date_after|iso_date}}style="border-bottom: 2px solid orange"{{/if}}
                        {{if ($element->warning_day && (($element->duree && $element->_fin_relative == $element->warning_day) ||
                        (!$element->duree && $prescription_end_real == $element->warning_day))) ||
                        ($element->warning_day_after && $element->_debut_relatif == $element->warning_day_after)}}
                          style="border-bottom: 2px solid orangered"
                        {{/if}}
                    class="{{if $element->highlight}}highlight_red{{/if}}"
                        onmouseover="ObjectTooltip.createEx(this, '{{$element->_guid}}')">
                          {{$element->_view}}
                        </span>
                    {{/if}}
                  {{/foreach}}
                {{/foreach}}
                {{if $total == 0}}
                  <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$_chap_name}}{{/tr}} en cours
                  </div>
                {{/if}}
              </td>
            </tr>
            <tr class="in_progress_after opacity-60">
              <td class="text">
                {{assign var=total value=0}}
                {{assign var=is_first_chap value=1}}
                {{assign var=count_elts value=0}}
                {{foreach from=$chap_element item=cat_element}}
                  {{if !$is_first_chap && $count_elts && $cat_element.element|@count > 0}}
                    {{assign var=count_elts value=0}}
                    &ndash;
                  {{/if}}
                  {{assign var=is_first_chap value=0}}
                  {{assign var=is_first_cat value=1}}
                  {{foreach from=$cat_element.element item=element}}
                    {{if $element->_debut_reel && $element->_debut_reel > $date && $element->_debut_reel <= $date_after}}
                      {{if !$is_first_cat}}
                        &ndash;
                      {{elseif $display_cat_for_elt}}
                        <strong>{{$element->_ref_element_prescription->_ref_category_prescription->nom}} : </strong>
                      {{/if}}
                      {{assign var=is_first_cat value=0}}
                      {{math equation="x+1" x=$total assign=total}}
                      {{math equation="x+1" x=$count_elts assign=count_elts}}
                      {{$element->_view}}
                    {{/if}}
                  {{/foreach}}
                {{/foreach}}
                {{if $total == 0}}
                  <div class="empty">Aucune ligne de {{tr}}CPrescription._chapitres.{{$_chap_name}}{{/tr}} à venir</div>
                {{/if}}
              </td>
            </tr>
          {{/foreach}}
          {{else}}
          <tr>
            <td>
              Aucune ligne en cours
            </td>
          </tr>
          {{/if}}
        </table>
      {{/if}}


      <table class="tbl me-table-card-list">
        <tr>
          <th class="title" colspan="2">{{tr}}CObjectifSoin{{/tr}}</th>
        </tr>
        <tr>
          <th>
            {{mb_label class=CObjectifSoin field="libelle"}}
          </th>
          <th class="narrow">
            {{mb_label class=CObjectifSoin field="statut"}}
          </th>
        </tr>
        {{foreach from=$sejour->_ref_objectifs_soins item=_objectif}}
          <tr {{if $_objectif->statut != "ouvert"}}style="opacity: 0.8" class="hatching"{{/if}}>
            <td>{{mb_value object=$_objectif field=libelle}}</td>
            <td>{{mb_value object=$_objectif field=statut}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty" colspan="2">{{tr}}CObjectifSoin.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>

      {{if "forms"|module_active}}
        <table class="tbl me-table-card">
          <tr>
            <th class="me-bg-white">{{tr}}CExClass|pl{{/tr}}</th>
          </tr>
        </table>
        <div id="list-ex_objects" class="me-align-auto me-margin-top--2"></div>
      {{/if}}

      {{if "dPpmsi"|module_active && "atih"|module_active && "atih CGroupage use_fg"|gconf && !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id)}}
        <table class="tbl me-table-card">
          <tr>
            <th class="title" colspan="2">
              {{tr}}CGroupage-Stay grouping{{/tr}}
            </th>
          </tr>
          <tr>
            <td colspan="2">
              <button type="button" class="search me-primary"
                      onclick="PMSI.loadRSS('{{$sejour->_id}}', 1, function() { PMSI.loadGroupageReadOnly('{{$sejour->_id}}', '0', 'section')});"
                      style="float: left;">
                {{tr}}CRSS-action-See the RSS and its RUM{{/tr}}
              </button>
              <button type="button" class="search me-primary"
                      onclick="PMSI.loadGroupageReadOnly('{{$sejour->_id}}', '0', 'section');"
              ">
              {{tr}}CGroupage-action-Show FG result{{/tr}}
              </button>
            </td>
          </tr>
        </table>
        <div id="groupage_pmsi_0" class="me-align-auto me-margin-top--2"></div>
      {{/if}}
    </td>
  </tr>
</table>

<div id="modal-prescription-legend-{{$unique_id_soins}}" style="display: none;">
  <table class="tbl">
    <tr>
      <th class="title" colspan="2">
        <button class="cancel notext" onclick="modalPrescriptionLegend.close();" style="float: right"></button>
        {{tr}}Legend{{/tr}}
      </th>
    </tr>
    <tr>
      <td class="narrow">
        <span class="color-view"
              style="display: inline-block; vertical-align: top; padding: 0; margin: 0; width: 16px; height: 16px; border-bottom: 2px solid orange"></span>
      </td>
      <td>
        Fin de la prescription le jour même ou le lendemain
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <span class="color-view"
              style="display: inline-block; vertical-align: top; padding: 0; margin: 0; width: 16px; height: 16px; border-bottom: 2px solid orangered"></span>
      </td>
      <td>
        Alerte paramétrée sur la fin de la prescription
      </td>
    </tr>
    <tr>
      <td>
        <span class="color-view highlight_red"
              style="display: inline-block; vertical-align: top; padding: 0; border: none; margin: 0;width: 16px; height: 16px;"></span>
      </td>
      <td>
        Ligne de prescription mise en évidence
      </td>
    </tr>
  </table>
</div>
