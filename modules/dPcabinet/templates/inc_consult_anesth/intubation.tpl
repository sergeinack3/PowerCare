{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_is_dentiste value=0}}
{{mb_default var=use_volets_moebius value=0}}
{{mb_script module=cabinet script=intubation}}
{{assign var=intubation_auto value="dPcabinet CConsultAnesth risque_intubation_auto"|gconf}}

<script>
  SchemaDentaire.oListEtats = {{$consult->_list_etat_dents|@json}};

  guessVentilation = function() {
    var url = new Url("cabinet", "ajax_guess_ventilation");
    url.addParam("patient_id", "{{$consult->patient_id}}");
    url.addParam("consult_id", "{{$consult_anesth->_id}}");
    url.requestUpdate("ventilation_area", function() {
      getForm('editFrmIntubation').onsubmit();
    });
  };

  toggleMode = function() {
    $('ouverture_bouche').toggle();
    $('ouverture_bouche_enfant').toggle();
  };

  Main.add(function() {
    var states = ['', 'defaut', 'absence', 'bridge', 'pivot', 'mobile', 'appareil', 'app-partiel', 'implant'];
    SchemaDentaire.initialize("dents-schema", states);
  });

  callbackIntub = function(consult_id, consult) {
    if (!window.tabsConsultAnesth) {
      return;
    }

    var count_tab = 0;
    var fields = [
      "mallampati", "bouche", "bouche_enfant", "distThyro", "mob_cervicale", "etatBucco", "conclusion",
      "plus_de_55_ans", "edentation", "barbe", "imc_sup_26", "ronflements", "piercing"
    ];

    fields.each(function(field) {
      if (consult[field] && consult[field] != "0") {
        count_tab++
      }
    });

    var classesDents = ["defaut", "absence", "bridge", "pivot", "mobile", "appareil", "app-partiel", "implant"];
    var schemaDent = $("dents-schema");
    classesDents.each(function(classe) {
      count_tab += schemaDent.select("div." + classe).length;
    });

    Control.Tabs.setTabCount("Intub", count_tab);
  };

  callbackEtatDent = function() {
    var count_tab = 0;
    var fields = [
      "mallampati", "bouche", "bouche_enfant", "distThyro", "mob_cervicale", "etatBucco", "conclusion",
      "plus_de_55_ans", "edentation", "barbe", "imc_sup_26", "ronflements", "piercing"
    ];
    var form = getForm("editFrmIntubation");

    fields.each(function(field) {
      if ($V(form.elements[field]) && $V(form.elements[field]) != 0) {
        count_tab++
      }
    });

    var classesDents = ["defaut", "absence", "bridge", "pivot", "mobile", "appareil", "app-partiel", "implant"];
    var schemaDent = $("dents-schema");
    classesDents.each(function(classe) {
      count_tab += schemaDent.select("div." + classe).length;
    });

    Control.Tabs.setTabCount("Intub", count_tab);
  };
</script>

<form name="etat-dent-edit" method="post">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_etat_dent_aed" />
  <input type="hidden" name="etat_dent_id" value="" />
  <input type="hidden" name="_patient_id" value="{{$consult->_ref_patient->_id}}" />
  <input type="hidden" name="dent" value="" />
  <input type="hidden" name="etat" value="" />
  <input type="hidden" name="callback" value="callbackEtatDent" />
</form>

{{if !$_is_dentiste}}
<form name="editFrmIntubation" method="post" onsubmit="return onSubmitFormAjax(this)">
<input type="hidden" name="m" value="cabinet" />
<input type="hidden" name="del" value="0" />
<input type="hidden" name="dosql" value="do_consult_anesth_aed" />
<input type="hidden" name="callback" value="callbackIntub" />

{{if $use_volets_moebius}}
  <input type="hidden" name="risque_intub" value="{{$consult_anesth->risque_intub}}" />
{{/if}}

{{mb_key object=$consult_anesth}}
{{mb_field object=$consult_anesth field=intub_difficile hidden=true}}
{{/if}}
<table class="form">
  <tr>
    <td class="narrow me-valign-top">
      <fieldset>
        <legend>{{tr}}CConsultAnesth-etatBucco{{/tr}}</legend>
        <div id="dents-schema" style="position: relative;">
          <img id="dents-schema-image" src="images/pictures/dents.png?build={{app_version_key}}" border="0" usemap="#dents-schema-map" />
          <map id="dents-schema-map" name="dents-schema-map">
            <area shape="circle" coords="127,112, 30" href="#1" id="dent-10" /><!-- Central haut adulte -->
            <area shape="circle" coords="116,33, 11" href="#1" id="dent-11" />
            <area shape="circle" coords="97,44, 11" href="#1" id="dent-12" />
            <area shape="circle" coords="79,55, 12" href="#1" id="dent-13" />
            <area shape="circle" coords="70,74, 12" href="#1" id="dent-14" />
            <area shape="circle" coords="61,93, 13" href="#1" id="dent-15" />
            <area shape="circle" coords="55,118, 17" href="#1" id="dent-16" />
            <area shape="circle" coords="51,146, 16" href="#1" id="dent-17" />
            <area shape="circle" coords="50,174, 15" href="#1" id="dent-18" />
            <area shape="circle" coords="137,33, 11" href="#1" id="dent-21" />
            <area shape="circle" coords="156,44, 11" href="#1" id="dent-22" />
            <area shape="circle" coords="174,55, 12" href="#1" id="dent-23" />
            <area shape="circle" coords="183,74, 12" href="#1" id="dent-24" />
            <area shape="circle" coords="192,94, 13" href="#1" id="dent-25" />
            <area shape="circle" coords="198,118, 17" href="#1" id="dent-26" />
            <area shape="circle" coords="201,146, 16" href="#1" id="dent-27" />
            <area shape="circle" coords="203,174, 15" href="#1" id="dent-28" />
            <area shape="circle" coords="127,272, 30" href="#1" id="dent-30" /><!-- Central bas adulte -->
            <area shape="circle" coords="135,356, 9" href="#1" id="dent-31" />
            <area shape="circle" coords="150,349, 9" href="#1" id="dent-32" />
            <area shape="circle" coords="164,338, 11" href="#1" id="dent-33" />
            <area shape="circle" coords="177,322, 11" href="#1" id="dent-34" />
            <area shape="circle" coords="186,303, 12" href="#1" id="dent-35" />
            <area shape="circle" coords="195,279, 18" href="#1" id="dent-36" />
            <area shape="circle" coords="199,250, 16" href="#1" id="dent-37" />
            <area shape="circle" coords="203,222, 15" href="#1" id="dent-38" />
            <area shape="circle" coords="118,356, 9" href="#1" id="dent-41" />
            <area shape="circle" coords="103,348, 9" href="#1" id="dent-42" />
            <area shape="circle" coords="89,338, 11" href="#1" id="dent-43" />
            <area shape="circle" coords="76,323, 11" href="#1" id="dent-44" />
            <area shape="circle" coords="66,304, 12" href="#1" id="dent-45" />
            <area shape="circle" coords="58,279, 18" href="#1" id="dent-46" />
            <area shape="circle" coords="54,250, 16" href="#1" id="dent-47" />
            <area shape="circle" coords="49,223, 15" href="#1" id="dent-48" />
            <area shape="circle" coords="324,162, 19" href="#1" id="dent-50" /><!-- Central haut enfant -->
            <area shape="circle" coords="318,114, 7" href="#1" id="dent-51" />
            <area shape="circle" coords="307,120, 8" href="#1" id="dent-52" />
            <area shape="circle" coords="298,131, 9" href="#1" id="dent-53" />
            <area shape="circle" coords="290,147, 11" href="#1" id="dent-54" />
            <area shape="circle" coords="285,166, 12" href="#1" id="dent-55" />
            <area shape="circle" coords="331,114, 7" href="#1" id="dent-61" />
            <area shape="circle" coords="342,120, 8" href="#1" id="dent-62" />
            <area shape="circle" coords="351,131, 9" href="#1" id="dent-63" />
            <area shape="circle" coords="357,147, 11" href="#1" id="dent-64" />
            <area shape="circle" coords="363,166, 12" href="#1" id="dent-65" />
            <area shape="circle" coords="324,231, 19" href="#1" id="dent-70" /><!-- Central haut enfant -->
            <area shape="circle" coords="330,271, 6" href="#1" id="dent-71" />
            <area shape="circle" coords="339,265, 7" href="#1" id="dent-72" />
            <area shape="circle" coords="350,255, 8" href="#1" id="dent-73" />
            <area shape="circle" coords="357,243, 8" href="#1" id="dent-74" />
            <area shape="circle" coords="365,227, 10" href="#1" id="dent-75" />
            <area shape="circle" coords="319,271, 6" href="#1" id="dent-81" />
            <area shape="circle" coords="309,265, 7" href="#1" id="dent-82" />
            <area shape="circle" coords="298,255, 8" href="#1" id="dent-83" />
            <area shape="circle" coords="291,242, 8" href="#1" id="dent-84" />
            <area shape="circle" coords="282,228, 10" href="#1" id="dent-85" />
          </map>
        </div>
      </fieldset>
    </td>
    {{if !$_is_dentiste}}
    <td class="me-valign-top">
      <fieldset>
        <legend>{{tr}}CConsultAnesth-legend-Conditions of intubation{{/tr}}</legend>
        <table class="layout main">
          <tr class="not-printable">
            <td colspan="2">
              <button class="history me-tertiary" type="button"
                      onclick="loadOldConsultsIntubation('{{$consult->_ref_patient->_id}}', '{{$consult_anesth->_id}}')">
                {{tr}}CConsultAnesth-action-Previous anesthesia folder|pl{{/tr}}
              </button>
              <button class="cancel me-tertiary me-dark" type="button" style="float: right;" onclick="resetIntubation(this.form)">{{tr}}Reset{{/tr}}</button>
              <button class="tick me-tertiary" type="button" style="float: right;" onclick="Intubation.ras(this.form);">{{tr}}RAS{{/tr}}</button>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <table class="layout main">
                <tr>
                  {{foreach from=$consult_anesth->_specs.mallampati->_locales key=curr_mallampati item=trans_mallampati}}
                  <td class="button">
                    <div id="mallampati_bg_{{$curr_mallampati}}" {{if $consult_anesth->mallampati == $curr_mallampati}}class="mallampati-selected"{{/if}}>
                    <label for="mallampati_{{$curr_mallampati}}"
                           title="{{if $curr_mallampati != "no_eval"}}
                                    {{tr var1=$trans_mallampati}}CConsultAnesth-Mallampati %s{{/tr}}
                                  {{else}}
                                    {{tr}}CConsultAnesth-Mallampati not assessable{{/tr}}
                                  {{/if}}">
                      <img src="images/pictures/{{$curr_mallampati}}.png?build={{app_version_key}}" />
                      <br />
                      <input type="radio" name="mallampati" value="{{$curr_mallampati}}" {{if $consult_anesth->mallampati == $curr_mallampati}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                      {{$trans_mallampati}}
                    </label>
                    </div>
                  </td>
                  {{/foreach}}
                </tr>
              </table>
              <input type="radio" style="display: none;" name="mallampati" value="" {{if !$consult_anesth->mallampati}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
            </td>
          </tr>
          <tr>
            <td class="halfPane">
              <table class="form me-no-box-shadow">
                <tr>
                  <td class="{{if !$intubation_auto}}quarterPane{{/if}} me-padding-0">
                    <fieldset class="me-no-box-shadow">
                      <legend>{{mb_label object=$consult_anesth field="bouche" defaultFor="bouche_m20"}}
                        {{if $consult->_ref_patient->civilite == "enf"}}
                          <input type="checkbox" name="switch_mode" value="0"
                                title="{{tr}}CConsultAnesth-Change the measures of opening a child mouth{{/tr}}"
                                onchange="toggleMode();" />
                        {{/if}}
                      </legend>
                      <table class="form me-no-box-shadow me-no-align">
                        <tr>
                          <td class="me-padding-0" id="ouverture_bouche"
                            {{if $consult->_ref_patient->civilite == "enf"}}style="display: none;"{{/if}}>
                            {{mb_field object=$consult_anesth field=bouche typeEnum=radio separator="<br />" onclick="\$V(this.form.intub_difficile, '');verifIntubDifficileAndSave(this.form, '$intubation_auto')" }}
                            <input type="radio" style="display: none;" name="bouche" value="" {{if !$consult_anesth->bouche}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                          </td>
                          <td class="me-padding-0" id="ouverture_bouche_enfant" {{if $consult->_ref_patient->civilite != "enf"}}style="display: none;"{{/if}}>
                            {{mb_field object=$consult_anesth field="bouche_enfant" typeEnum="radio" separator="<br />" onclick="\$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                            <input type="radio" style="display: none;" name="bouche_enfant" value="" {{if !$consult_anesth->bouche_enfant}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                          </td>
                        </tr>
                      </table>
                    </fieldset>
                  </td>
                  <td class="quarterPane">
                  {{if !($use_volets_moebius || $intubation_auto)}}
                    <fieldset>
                      <legend>{{tr}}CConsultAnesth-Risks of intubation{{/tr}}</legend>
                      {{mb_field object=$consult_anesth field="risque_intub" typeEnum="radio" separator="<br />" onclick="\$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                      <input type="radio" style="display: none;" name="risque_intub" value="" {{if !$consult_anesth->risque_intub}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                    </fieldset>
                    {{/if}}
                  </td>
                </tr>
              </table>
            </td>
            <td class="halfPane">
              <table class="form me-no-box-shadow">
                <tr>
                  <td class="quarterPane me-padding-0">
                    <fieldset class="me-no-box-shadow me-padding-top-0">
                      <legend class="me-pos-relative">{{mb_label object=$consult_anesth field="distThyro" defaultFor="distThyro_m65"}}</legend>
                      {{mb_field object=$consult_anesth field="distThyro" typeEnum="radio" separator="<br />" onclick="\$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                      <input type="radio" style="display: none;" name="distThyro" value="" {{if !$consult_anesth->distThyro}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                    </fieldset>
                  </td>
                  <td class="quarterPane me-padding-0 me-valign-top">
                    <fieldset class="me-no-box-shadow">
                      <legend>{{mb_label object=$consult_anesth field="tourCou"}}</legend>
                      {{mb_field object=$consult_anesth field="tourCou" typeEnum="radio" separator="<br />" onclick="\$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                      <input type="radio" style="display: none;" name="tourCou" value="" {{if !$consult_anesth->tourCou}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
                    </fieldset>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <fieldset>
                <legend>{{tr}}CConsultAnesth-legend-Criteria for ventilation{{/tr}}</legend>
                <div id="ventilation_area">
                  {{mb_include module="cabinet" template="inc_guess_ventilation"}}
                </div>
              </fieldset>
            </td>
            <td>
              <fieldset>
                <legend>
                  {{mb_label object=$consult_anesth field="mob_cervicale"}}
                </legend>
                {{mb_field object=$consult_anesth field="mob_cervicale" typeEnum="radio" separator="<br />" onclick="\$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                <input type="radio" style="display: none;" name="mob_cervicale" value="" {{if !$consult_anesth->mob_cervicale}}checked{{/if}} onclick="$V(this.form.intub_difficile, ''); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" />
              </fieldset>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <fieldset class="me-no-box-shadow">
                <legend>
                  {{tr}}CConsultAnesth-titre-legend-cormack{{/tr}} {{ if $consult_anesth->cormack }} ({{tr}}The{{/tr}} <span onmouseover="ObjectTooltip.createEx(this, '{{$consult_anesth->_ref_operation->_guid}}', null, { view_tarif: true })">
                      <strong>{{$consult_anesth->_ref_operation->date|date_format:$conf.longdate}}</strong></span>) {{/if}}
                  <button type="button" class="edit notext not-printable" onclick="Intubation.editScoreCormack(this);">
                    {{tr}}CConsultAnesth-cormack-edit{{/tr}}
                  </button>
                </legend>
                <table class="form">
                  <tr>
                    {{foreach from=$consult_anesth->_specs.cormack->_locales key=curr_cormack item=trans_cormack}}
                      <td class="button grade_cormack">
                        <div id="cormack_bg_{{$curr_cormack}}"
                             {{if $consult_anesth->cormack == $curr_cormack}}class="cormack-selected"{{/if}}>
                          <label for="cormack_{{$curr_cormack}}" title="Cormack de {{$trans_cormack}}">
                            <input type="radio" name="cormack" value="{{$curr_cormack}}"
                                   {{if $consult_anesth->cormack == $curr_cormack}}checked{{/if}} disabled />
                            {{$trans_cormack}}
                          </label>
                        </div>
                      </td>
                    {{/foreach}}
                  </tr>
                  <tr class="show_com_cormack">
                    <td colspan="4">
                      {{tr}}CConsultAnesth-com_cormack{{/tr}} : {{$consult_anesth->com_cormack}}
                    </td>
                  </tr>
                  <tr class="edit_com_cormack" style="display: none;">
                    <td>
                      {{tr}}CConsultAnesth-com_cormack{{/tr}} 
                    </td>
                    <td colspan="3">
                      {{mb_field object=$consult_anesth field=com_cormack onchange="verifIntubDifficileAndSave(this.form, '$intubation_auto');"}}
                    </td>
                  </tr>
                </table>
                <input type="radio" style="display: none;" name="cormack" value="" {{if !$consult_anesth->cormack}}checked{{/if}} />
              </fieldset>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="me-padding-left-8">
              {{mb_label object=$consult_anesth field="etatBucco"}}
            </td>
          </tr>
          <tr>
            <td colspan="2" class="me-padding-left-4">
              {{mb_field object=$consult_anesth field="etatBucco" onchange="this.form.onsubmit()" form="editFrmIntubation"
                aidesaisie="validateOnBlur: 0"}}
            </td>
          </tr>
          <tr>
            <td colspan="2" class="me-padding-left-8">
              {{mb_label object=$consult_anesth field="conclusion"}}
            </td>
          </tr>
          <tr>
            <td colspan="2" class="me-padding-left-4">
              {{mb_field object=$consult_anesth field="conclusion" onchange="this.form.onsubmit()" form="editFrmIntubation"
                aidesaisie="validateOnBlur: 0"}}
            </td>
          </tr>
          <tr {{if $use_volets_moebius || !$intubation_auto}}style="display: none;"{{/if}}>
            <td colspan="2" class="button">
              <button type="button" id="force_pas_difficile" class="tick not-printable"
                      {{if !$consult_anesth->_intub_difficile}}style="display: none;"{{/if}}
                      onclick="$V(this.form.intub_difficile, '0'); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');" >{{tr}}CConsultAnesth-action-Not difficult{{/tr}}</button>
              <button type="button" id="force_difficile" class="tick not-printable"
                      {{if $consult_anesth->_intub_difficile}}style="display: none;"{{/if}}
                      onclick="$V(this.form.intub_difficile, '1'); verifIntubDifficileAndSave(this.form, '{{$intubation_auto}}');">{{tr}}CConsultAnesth-action-Difficult{{/tr}}</button>
              <div id="divAlertIntubDiff"
                style="color: {{if $consult_anesth->_intub_difficile}}#F00;{{else}}#000;{{/if}}">
                {{if $consult_anesth->_intub_difficile}}
                  <strong>{{tr}}CConsultAnesth-_intub_difficile{{/tr}}</strong>
                {{else}}
                  {{tr}}CConsultAnesth-_intub_pas_difficile{{/tr}}
                {{/if}}
              </div>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    {{/if}}
  </tr>
</table>
</form>
{{if $use_volets_moebius}}
  <div id="moebius_intubation" style="width: 420px;margin-top: -166px; margin-left: 10px;">
    {{mb_include module=moebius template=vw_examen_clinique_intubation}}
  </div>
{{/if}}
