{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=edit_planning}}

<script>
  togglePlayPause = function(button) {
    button.toggleClassName("play");
    button.toggleClassName("pause");
    if (!(window.autoRefreshSuivi)) {
      window.autoRefreshSuivi = setInterval(function(){
        updateSuiviSalle();
      }, ({{math equation="a*1000" a="dPbloc affichage time_autorefresh"|gconf}}));
    }
    else {
      clearTimeout(window.autoRefreshSuivi);
    }
  };

  updateSuiviSalle = function() {
    var oform   = getForm('changeDate');
    var date    = $V(oform.date);
    var blocs_ids = $V(oform.blocs_ids);

    if (blocs_ids) {
      var url = new Url("bloc", "ajax_vw_suivi_salle");
      url.addParam('blocs_ids[]', blocs_ids, true);
      url.addParam('date', date);
      url.addParam('view_light', '{{$view_light}}');
      url.addParam("mode_presentation", 1);
      var str = DateFormat.format(Date.fromDATE(date), " dd/MM/yyyy");
      if (date == '{{$dnow}}') {
        str= str+" (Aujourd'hui)";
      }
      $('dateSuiviSalle').update(str);
      url.requestUpdate("result_suivi");
    }
  };

  printFicheBloc = function(interv_id) {
  var url = new Url("salleOp", "print_feuille_bloc");
  url.addParam("operation_id", interv_id);
  url.popup(700, 600, 'FeuilleBloc');
  };

  printAnapath = function() {
    var form = getForm('changeDate');
    var url = new Url("bloc", "print_anapath");
    url.addParam("date"   , $V(form.date));
    url.addParam("blocs_ids[]", $V(form.blocs_ids), true);
    url.popup(700, 600, 'Anapath');
  };

  printBacterio = function() {
    var form = getForm('changeDate');
    var url = new Url("bloc", "print_bacterio");
    url.addParam("date"   , $V(form.date));
    url.addParam("blocs_ids[]", $V(form.blocs_ids), true);
    url.popup(700, 600, 'Bacterio');
  };

  modePresentation = function() {
    var form = getForm('changeDate');
    var url = new Url("bloc", "vw_suivi_salles_presentation");
    url.addParam("date"   , $V(form.date));
    url.addParam("blocs_ids[]", $V(form.blocs_ids), true);
    url.addParam("salle_ids", $$(".salle-toggler:checked").invoke("get", "salle_id").join("-"));
    url.popup("100%", "100%", 'Mode présentation');
  };

  suiviBloc = function() {
    new Url("bloc", "vw_suivi_bloc")
      .addParam("type_view", "all")
      .popup("100%", "100%", "Suivi bloc");
  };

  showLegend = function() {
    new Url('bloc', 'legende').requestModal()
  };

  selectView = function() {
    Modal.open(
      $('select_planning_view'),
      {
        title: $T('pref-view_planning_bloc'),
        showClose: true,
        width: 300
      }
    );
  };

  showPreparationSterilisation = function() {
    new Url('planningOp', 'vw_preparation_sterilisation')
      .addParam('date', getForm("changeDate").date)
      .requestModal('90%', '90%');
  };

  Main.add(function () {
    {{if $blocs|@count}}
      Calendar.regField(getForm("changeDate").date, null, {noView: true});
      updateSuiviSalle();
      if (Preferences.startAutoRefreshAtStartup == 1) {
        togglePlayPause($('autorefreshSuiviSalleButton'));
      }
    {{/if}}
  });
</script>

{{if $blocs|@count}}
  <table class="main not-printable">
    <tr>
      <td>
        <button id="autorefreshSuiviSalleButton" style="float: left;" class="play" title="{{tr}}Bloc-Refresh auto{{/tr}} ({{tr}}config-dPbloc-affichage-time_autorefresh.{{"dPbloc affichage time_autorefresh"|gconf}}{{/tr}})" onclick="togglePlayPause(this);">
          {{tr}}config-dPbloc-affichage-time_autorefresh-court{{/tr}}
        </button>

        <span style="float: right;">
          {{if !$view_light}}
            {{me_button icon=print label=COperation-labo onclick="printBacterio();"}}
            {{me_button icon=print label=COperation-anapath onclick="printAnapath();"}}
          {{/if}}
          {{me_button icon=print label=COperation-print-suivi onclick="\$('suivi-salles').print();"}}
          {{if $dmi_active}}
            {{me_button icon=print label="CDM-Print DM" onclick="\$('print_dm').print();"}}
          {{/if}}
          {{me_dropdown_button button_icon=print button_label=Print button_class="me-tertiary"}}

          <button type="button" class="search me-tertiary" onclick="showLegend()">{{tr}}Legend{{/tr}}</button>
          {{if !$view_light}}
            <button type="button" class="lookup" onclick="modePresentation();">{{tr}}Bloc-Mode presentation{{/tr}}</button>
            <button type="button" class="lookup" onclick="suiviBloc();">{{tr}}Bloc-Suivi bloc{{/tr}}</button>
          {{/if}}

          <button type="button" class="search" onclick="showPreparationSterilisation();">{{tr}}Bloc-Preparation sterilisation{{/tr}}</button>
          <button type="button" class="fa fa-cog notext" style="float: right" onclick="selectView();">
            {{tr}}Bloc-Change view mode{{/tr}}
          </button>
        </span>

        <form action="?" name="changeDate" method="get">
          <label> {{tr}}CBlocOperatoire{{/tr}} :
            <select name="blocs_ids" onchange="updateSuiviSalle();" multiple="3">
              {{foreach from=$blocs item=curr_bloc name=bloc}}
                <option value="{{$curr_bloc->_id}}"
                        {{if (is_array($blocs_ids) && in_array($curr_bloc->_id, $blocs_ids)) || (!is_array($blocs_ids) && $smarty.foreach.bloc.first)}}selected{{/if}}>
                  {{$curr_bloc->nom}}
                </option>
              {{/foreach}}
            </select>
          </label>
          <label>{{tr}}Date{{/tr}} :
            <input type="hidden" name="date" class="date" value="{{$date}}" onchange="updateSuiviSalle();" /><span id="dateSuiviSalle"></span>
          </label>
        </form>
      </td>
    </tr>
  </table>
{{/if}}

<div id="result_suivi">
  {{if !$blocs|@count}}
    <div class="small-warning">
      {{tr}}dPbloc-msg-no_bloc{{/tr}}
    </div>
  {{/if}}
</div>

<div id="select_planning_view" style="display: none;">
  {{mb_include module=bloc template=select_planning_bloc_view}}
</div>
