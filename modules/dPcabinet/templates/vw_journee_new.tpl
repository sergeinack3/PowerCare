{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=cabinet script=plage_consultation}}
{{mb_script module=dPhospi script=prestation ajax=1}}

{{assign var=refresh_journee value='dPcabinet Planning auto_refresh_planning_frequency'|gconf}}

{{mb_default var=refresh_onload value=1}}
<script>
  assignDate = function(button) {
    button = $(button);
    var date = button.get("date");

    if (date) {
      var form = getForm("filter_day");
      $V(form.date, date, true);
      $V(form.date_da, DateFormat.format(Date.fromDATE(date), "dd/MM/yyyy"), true);
    }
  };

  refreshPlanningNew = function() {
    var form = getForm("filter_day");
    var week_containers = $$(".week-container");
    if (week_containers.length > 0) {
      $V(form.scroll_top, week_containers[0].scrollTop);
    }
    form.onsubmit();
  };

  // Clic sur une consultation
  setClose = function(heure, plage_id, date, chir_id, consult_id, element) {
    if (window.action_in_progress) {
      window.action_in_progress = false;
      return;
    }

    if (consult_id) {
      modalPriseRDVNew(consult_id);
    }
    else {
      // ugly method to get time
      var time = "";
      $w(element.className).each(function(elt) {
        if (elt.indexOf(":") != -1) {
          time = elt;
        }
      });
      modalPriseRDVNew(0, Date.fromLocaleDate(date.split(" ")[1]).toDATE(), time, plage_id);
    }
  };

  modalPriseRDVNew = function(consult_id, date, heure, plage_id) {
    var form = getForm("filter_day");

    var url = new Url("cabinet", "edit_planning");
    url.addParam("dialog"         , 1);
    url.addParam("consultation_id", consult_id);
    url.addParam("date_planning"  , date);
    url.addParam("heure"          , heure);
    url.addParam("plageconsult_id", plage_id);

    if (!consult_id && form && $V(form.highlight) === "1") {
      url.addParam("multi_ressources", 1);
      url.addParam("prats_ids[]", form.select("input.prats:[checked=true]").pluck("value"), true);
      url.addParam("prats_unselected_ids[]", form.select("input.prats:[checked=false]").pluck("value"), true);
      url.addParam("ressources_ids[]", form.select("input.ressources:checked").pluck("value"), true);
    }

    url.modal({
      width: "100%",
      height: "100%",
      afterClose: refreshPlanningNew
    });
  };

  if (!window.refreshPlanning) {
    refreshPlanning = refreshPlanningNew;
  }

  refreshItems = function(type, function_id) {
    new Url("cabinet", "ajax_filter_items")
      .addParam("function_id", function_id)
      .addParam("type", type)
      .requestUpdate("filter_" + type);
  };

  Main.add(function() {
    ViewPort.SetAvlHeight("planning", 1);
    Calendar.regField(getForm("filter_day").date);
    {{if $refresh_onload}}
      refreshPlanningNew();
    {{/if}}

    {{if $refresh_journee}}
      //Raffraichissement périodique de l'agenda
      setInterval(refreshPlanningNew, parseInt('{{$refresh_journee}}')*1000);
    {{/if}}

    {{if $function_id}}
      PlageConsultation.showPratsByFunction('{{$function_id}}');
    {{/if}}
  });
</script>

<form name="chronoPatient" method="post">
  <input type="hidden" name="m" value="dPcabinet"/>
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="chrono" />
  <input type="hidden" name="arrivee" />
</form>

<form name="editConsult" method="post">
  <input type="hidden" name="m" value="dPcabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="plageconsult_id" />
  <input type="hidden" name="heure" />
</form>

<form name="restoreConsult" method="post">
  <input type="hidden" name="m" value="dPcabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="annule" value="0" />
</form>

<form method="get" name="filter_day" onsubmit="return onSubmitFormAjax(this, {}, 'planning')">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="a" value="ajax_vw_journee_new" />
  <input type="hidden" name="scroll_top" value="" />
  {{* Tell if we come from a form or not to know if we should be using cache in the ajax *}}
  <input type="hidden" name="request_form" value="0">

  <fieldset id="jfilters" class="me-padding-4 me-padding-top-8">
    {{me_form_field label="CPlageconsult-_function_id-court" field_class="me-form-group-inline"}}
      <select name="function_id"
              onchange="PlageConsultation.showPratsByFunction(this.value);
              Prestation.refreshPlanningNew();
              Prestation.refreshItems('prats', this.value);
              Prestation.refreshItems('ressources', this.value);">
        {{mb_include module=mediusers template=inc_options_function list=$functions selected=$function_id}}
      </select>
    {{/me_form_field}}

    <button class="lookup me-secondary" type="button" onclick="Planning.reloadRessources('{{$function_id}}', getForm('filter_day').date.value); Modal.open('filter_more', {showClose: true, onClose:refreshPlanningNew, title:'Filtres'})">{{tr}}Filter{{/tr}}</button>
    <div id="filter_more" style="display: none;">
      {{mb_include module=cabinet template=inc_filter_new_planning}}
    </div>
    <button type="button" id="previous_day" class="left notext me-tertiary me-dark" onclick="assignDate(this);" data-date=""></button>
    <input type="hidden" name="date" value="{{$date}}" onchange="this.form.onsubmit();"/>
    <button type="button" id="next_day" class="right notext me-tertiary me-dark" onclick="assignDate(this);" data-date=""></button>

    <button class="change notext me-tertiary" onclick="this.form.request_form.value = '1';"></button>
    <div id="filter_prats" class="me-padding-top-8"></div>
  </fieldset>
</form>

<div id="planning" style="clear: both" class="me-padding-0 me-margin-top-4"></div>
