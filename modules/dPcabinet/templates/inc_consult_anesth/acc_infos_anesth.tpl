{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var="view_prescription" value=1}}
{{assign var=use_volets_moebius value=0}}
{{if "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}
{{assign var=use_volets_moebius value=1}}
{{/if}}

<script>
  submitTech = function (oForm) {
    onSubmitFormAjax(oForm, reloadListTech);
    if ($V(oForm.elements.del)) {
      oForm.reset();
    }
    return false;
  };

  Main.add(function () {
    guessScoreApfel();
  });

  reloadListTech = function () {
    var UrllistTech = new Url("dPcabinet", "httpreq_vw_list_techniques_comp");
    UrllistTech.addParam("selConsult", "{{$consult->_id}}");
    UrllistTech.addParam("dossier_anesth_id", "{{$consult_anesth->_id}}");
    UrllistTech.requestUpdate('listTech', callbackInfoAnesth);
  };

  guessScoreApfel = function () {
    if (!$('score_apfel_area')) {
      return;
    }
    var url = new Url("cabinet", "ajax_guess_score_apfel");
    url.addParam("patient_id", "{{$consult->patient_id}}");
    url.addParam("consult_id", "{{$consult_anesth->_id}}");
    url.requestUpdate("score_apfel_area", function () {
      return getForm('editScoreApfel').onsubmit();
    });
  };

  afterStoreScore = function (id, obj) {
    $("score_apfel").update(obj._score_apfel);
    callbackInfoAnesth();
  };

  toggleUSCPO = function (status) {
    var form = getForm("editOpAnesthFrm");
    if (status == 1) {
      $$(".uscpo_area").invoke('show');
      $$('.add_colspan').invoke('writeAttribute', 'colspan', 3);
    } else {
      $$(".uscpo_area").invoke('hide');
      $$('.add_colspan').invoke('removeAttribute', 'colspan');
    }
    // Permet de valuer à 1 automatiquement la durée uscpo,
    // ou bien 0 si le passage uscpo est repassé à non.
    $V(form.duree_uscpo, status);
  };

  checkUSCPO = function () {
    var form = getForm("editOpAnesthFrm");
    if ($V(form._passage_uscpo) == 1 && $V(form.duree_uscpo) == "") {
      alert("Veuillez saisir une durée USCPO");
      return false;
    }

    return true;
  };

  callbackInfoAnesth = function () {
    if (!window.tabsConsultAnesth) {
      return;
    }

    var count = 0;

    var form = getForm("editOpAnesthFrm");
    var fields = ["rques", "passage_uscpo", "type_anesth", "ASA", "position_id"];

    fields.each(function (field) {
      if ($V(form.elements[field])) {
        count++;
      }
    });

    if ($V(getForm("editInfosAnesthFrm").prepa_preop)) {
      count++;
    }

    count += $("listTech").select("button.trash").length;

    if ($V(getForm("editRquesConsultFrm").rques)) {
      count++;
    }

    var form = getForm("editScoreApfel");
    if (form) {
      form.select("input[type=checkbox").each(function (input) {
        if (input.checked) {
          count++;
        }
      });
    }

    Control.Tabs.setTabCount("InfoAnesth", count);
  };
</script>

{{assign var=operation value=$consult_anesth->_ref_operation}}

<table class="form me-no-box-shadow">
  <tr>
    <td colspan="2" class="me-padding-0">
      <fieldset class="me-no-box-shadow">
        <legend>Intervention</legend>
          {{mb_ternary var=object test=$operation->_id value=$operation other=$consult_anesth}}
          {{mb_ternary var=dosql test=$operation->_id value='do_planning_aed' other='do_consult_anesth_aed'}}
          {{mb_ternary var=module test=$operation->_id value='planningOp' other='cabinet'}}
        <form name="editOpAnesthFrm" method="post"
              onsubmit="{{if $conf.dPplanningOp.COperation.show_duree_preop == 2}}if (checkUSCPO()) {{/if}}{return onSubmitFormAjax(this);
                  }"
        ">
        <input type="hidden" name="m" value="{{$module}}"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="dosql" value="{{$dosql}}"/>
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
          {{mb_key object=$object}}

        <div style="width: 50%; float: left;" class="me-margin-left-4">
            {{mb_label object=$object field="rques"}}
            {{mb_field object=$object field="rques" rows="4" onchange="this.form.onsubmit()" form="editOpAnesthFrm"
            aidesaisie="validateOnBlur: 0"}}
        </div>
        <div style="width: 49%; float: right;">
          <table class="form me-padding-0 me-no-align me-no-box-shadow">
              {{if $conf.dPplanningOp.COperation.show_duree_uscpo >= 1}}
            <tr>
              <th>{{mb_label object=$object field=passage_uscpo}}</th>
              <td>{{mb_field object=$object field=passage_uscpo onclick="toggleUSCPO(this.value); this.form.onsubmit();"}}</td>
              <th class="uscpo_area" {{if !$object->passage_uscpo}}style="display: none;"{{/if}}>
                  {{mb_label object=$object field=duree_uscpo style="padding-left: 1.4em;" id="uscpo_label"}}
              </th>
              <td class="uscpo_area" {{if !$object->passage_uscpo}}style="display: none;"{{/if}}>
                  {{mb_field object=$object field=duree_uscpo form=editOpAnesthFrm increment=true onchange="this.form.onsubmit()"}}
                nuit(s)
              </td>
            </tr>
              {{/if}}
            <tr>
              <th><span style="font-weight: bold;">{{mb_label object=$object field=type_anesth}}</span></th>
              <td class="add_colspan" {{if $object->passage_uscpo}}colspan="3"{{/if}}>
                <select name="type_anesth" onchange="this.form.onsubmit()">
                  <option value="">&mdash; Anesthésie</option>
                    {{foreach from=$anesth item=curr_anesth}}
                    {{if $curr_anesth->actif || $object->type_anesth == $curr_anesth->type_anesth_id}}
                  <option
                    value="{{$curr_anesth->type_anesth_id}}" {{if $object->type_anesth == $curr_anesth->type_anesth_id}}
                    selected="selected" {{/if}}>
                      {{$curr_anesth->name}} {{if !$curr_anesth->actif && $object->type_anesth == $curr_anesth->type_anesth_id}}(Obsolète){{/if}}
                  </option>
                    {{/if}}
                    {{/foreach}}
                </select>
              </td>
            </tr>
              {{if !$use_volets_moebius}}
            <tr>
              <th>{{mb_label object=$object field="ASA" style="padding-left: 6em;"}}</th>
              <td class="add_colspan" {{if $object->passage_uscpo}}colspan="3"{{/if}}>
                  {{mb_field object=$object field="ASA" emptyLabel="Choose" style="width: 12em;" onchange="this.form.onsubmit()"}}
              </td>
            </tr>
              {{else}}
            <tr>
              <td>{{mb_field object=$object field=ASA hidden=true}}</td>
            </tr>
              {{/if}}
            <tr>
              <th>{{mb_label object=$object field=position_id style="padding-left: 4.5em;"}}</th>
              <td class="add_colspan" {{if $object->passage_uscpo}}colspan="3"{{/if}}>
                <script>
                  Main.add(function () {
                    var formPosition = getForm("editOpAnesthFrm");
                    new Url("planningOp", "position_autocomplete")
                      .addParam('group_id', {{$g}})
                      .autoComplete(
                        formPosition.position_id_view,
                        null,
                        {
                          minChars:           0,
                          method:             "get",
                          select:             "view",
                          dropdown:           true,
                          afterUpdateElement: function (field, selected) {
                            $V(field.form["position_id"], selected.getAttribute("id").split("-")[2]);
                          }
                        }
                      );
                  });
                </script>
                  {{mb_field object=$object field=position_id hidden=1 onchange="this.form.onsubmit()"}}
                  {{assign var=position_object value=$object->loadRefPosition()}}
                <input type="text" name="position_id_view" value="{{$position_object->_view}}" style="width: 12em;"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.position_id, ''); $V(this.form.position_id_view, '')"></button>
              </td>
            </tr>
          </table>
        </div>
        </form>
      </fieldset>
      <fieldset class="me-no-box-shadow">
        <legend>Pré-opératoire</legend>
        <form name="editInfosAnesthFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="dPcabinet"/>
          <input type="hidden" name="del" value="0"/>
          <input type="hidden" name="dosql" value="do_consult_anesth_aed"/>
          <input type="hidden" name="callback" value="callbackInfoAnesth"/>
            {{mb_key object=$consult_anesth}}
          <table class="layout main form me-no-box-shadow">
            <tr>
              <td class="halfPane me-padding-0">
                  {{mb_label object=$consult_anesth field="prepa_preop"}}
                  {{mb_field object=$consult_anesth field="prepa_preop" rows="4" onchange="this.form.onsubmit()" form="editInfosAnesthFrm"
                  aidesaisie="validateOnBlur: 0"}}
              </td>
              <td class="halfPane me-padding-top-0 me-padding-bottom-0">
                  {{mb_label object=$consult_anesth field=accord_patient_debout_aller}}
                  {{mb_field object=$consult_anesth field=accord_patient_debout_aller onchange="this.form.onsubmit()"}}
                <br/>
                  {{if !$isPrescriptionInstalled || ("dPcabinet CConsultAnesth view_premedication"|gconf && $app->user_prefs.displayPremedConsult)}}
                  {{mb_label object=$consult_anesth field="premedication"}}
                  {{mb_field object=$consult_anesth field="premedication" rows="4" onchange="this.form.onsubmit()" form="editInfosAnesthFrm"
                  aidesaisie="validateOnBlur: 0"}}
                  {{else}}
                  {{if "dPcabinet CPrescription view_prescription"|gconf}}
                  {{if $view_prescription}}
                    <button class="tick not-printable me-tertiary" type="button" onclick="tabsConsultAnesth.setActiveTab('prescription_sejour');
                            tabsConsultAnesth.activeLink.up('li').onmousedown()">Accéder à la prescription
                    </button>
                  {{/if}}
                  {{else}}
                <div class="small-info">
                  La saisie de la prémédication n'est actuellement pas active
                </div>
                  {{/if}}
                  {{/if}}
              </td>
            </tr>
          </table>
        </form>
      </fieldset>

      <fieldset class="me-no-box-shadow">
        <legend>{{mb_label object=$techniquesComp field="technique"}}</legend>
        <table class="layout main">
          <tr>
            <td class="halfPane">
              <form name="addEditTechCompFrm" action="?m=dPcabinet" method="post" onsubmit="return submitTech(this)">
                  {{mb_class object=$techniquesComp}}
                  {{mb_key object=$techniquesComp}}
                <input type="hidden" name="del" value="0"/>
                  {{mb_field object=$consult_anesth field=consultation_anesth_id hidden=1}}
                  {{mb_field object=$techniquesComp field=technique rows="4" form="addEditTechCompFrm" aidesaisie="validateOnBlur: 0"}}
                <button class="add not-printable" type="button" onclick="if ($V(this.form.technique)) { this.form.onsubmit() }">
                    {{tr}}Add{{/tr}}
                </button>
              </form>
            </td>
            <td class="halfPane text" id="listTech">
                {{mb_include module=cabinet template=inc_consult_anesth/techniques_comp}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td style="width: 50%;" class="me-valign-top">
      <form name="editRquesConsultFrm" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="cabinet"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="dosql" value="do_consultation_aed"/>
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
          {{mb_key object=$consult}}
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$consult field="rques"}}</legend>
            {{mb_field object=$consult field="rques" rows="9" onchange="this.form.onsubmit()" form="editRquesConsultFrm"
            aidesaisie="validateOnBlur: 0"}}
        </fieldset>
      </form>
    </td>
    <td class="me-valign-top">
      {{if !$use_volets_moebius}}
        <div class="scores_anesth">
          <div class="score score-apfel">
            <div class="score-titre me-h6">
                {{tr}}CConsultAnesth-APFEL score{{/tr}}
            </div>
            <div id="score_apfel_area" class="score-content">
                {{mb_include module=cabinet template=inc_guess_score_apfel}}
            </div>
          </div>
          <div class="score score-lee">
            <div class="score-titre me-h6">
                {{tr}}CExamLee{{/tr}}
            </div>
            <div id="score_lee" class="score-content">
                {{assign var=score_lee value=$consult_anesth->_ref_score_lee}}
                {{mb_include module=cabinet template=inc_vw_score_lee}}
            </div>
          </div>
          <div class="score score-met">
            <div class="score-titre me-h6">
                {{tr}}CExamMet{{/tr}}
            </div>
            <div id="score_met" class="score-content">
                {{assign var=score_met value=$consult_anesth->_ref_score_met}}
                {{mb_include module=cabinet template=inc_vw_score_met}}
            </div>
          </div>
          <div class="score score-hemostase">
            <div class="score-titre me-h6">
                {{tr}}CExamHemostase{{/tr}}
            </div>
            <div id="score_hemostase" class="score-content">
                {{mb_include module=cabinet template=inc_vw_score_hemostase score_hemostase=$consult_anesth->_ref_score_hemostase}}
            </div>
          </div>
        </div>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td style="width: 50%;" class="me-valign-top">
      <form name="editAuTotalConsultFrm" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$consult_anesth}}
          {{mb_key   object=$consult_anesth}}
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$consult_anesth field="au_total"}}</legend>
            {{mb_field object=$consult_anesth field="au_total" rows="4" onchange="this.form.onsubmit()" form="editAuTotalConsultFrm"
            aidesaisie="validateOnBlur: 0"}}
        </fieldset>
      </form>
    </td>
    <td style="width: 50%;" class="me-valign-top">
      <form name="editStratAntibioConsultFrm" method="post" onsubmit="return onSubmitFormAjax(this);"
            {{if "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}style="display: none;"{{/if}}>
          {{mb_class object=$consult_anesth}}
          {{mb_key   object=$consult_anesth}}
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$consult_anesth field="strategie_antibio"}}</legend>
            {{mb_field object=$consult_anesth field="strategie_antibio" rows="4" onchange="this.form.onsubmit()" form="editStratAntibioConsultFrm"
            aidesaisie="validateOnBlur: 0"}}
        </fieldset>
      </form>
    </td>
  </tr>
  <tr>
    <td style="width: 50%;" class="me-valign-top">
        {{if 'dPcabinet CConsultAnesth see_strategie_prevention'|gconf}}
      <form name="editStratPreventionConsultFrm" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$consult_anesth}}
          {{mb_key   object=$consult_anesth}}
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$consult_anesth field="strategie_prevention"}}</legend>
            {{mb_field object=$consult_anesth field="strategie_prevention" rows="4" onchange="this.form.onsubmit()" form="editStratPreventionConsultFrm"
            aidesaisie="validateOnBlur: 0"}}
        </fieldset>
      </form>
        {{/if}}
    </td>
    <td class="me-valign-top">
        {{if $consult->_ref_patient->tutelle != 'aucune' || $consult->_ref_patient->_annees < 18}}
      <form name="editAutorisationAnesthFrm" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$consult_anesth}}
          {{mb_key   object=$consult_anesth}}
        <input type="hidden" name="callback" value="callbackInfoAnesth"/>
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$consult_anesth field=autorisation}}</legend>
          <input id="editAutorisationAnesthFrm_autorisation_0" type="radio" onchange="this.form.onsubmit()" value="0"
                 name="autorisation"{{if $consult_anesth->autorisation == '0'}} checked="checked"{{/if}}>
          <label for="editAutorisationAnesthFrm_autorisation_0">{{tr}}CConsultAnesth.autorisation.0{{/tr}}</label>
          <input id="editAutorisationAnesthFrm_autorisation_1" class="enum list|undefined|0|1 default|undefined" type="radio"
                 onchange="this.form.onsubmit()" value="1"
                 name="autorisation"{{if $consult_anesth->autorisation == '1'}} checked="checked"{{/if}}>
          <label for="editAutorisationAnesthFrm_autorisation_1">{{tr}}CConsultAnesth.autorisation.1{{/tr}}</label>
        </fieldset>
      </form>
        {{/if}}
    </td>
  </tr>
  <tr>
    <td style="width: 50%;">
        {{if $consult_anesth->_refs_info_checklist|@count}}
        {{mb_include module=cabinet template=inc_select_info_checklist consult_ref=$consult_anesth}}
        {{/if}}
    </td>
  </tr>
</table>
