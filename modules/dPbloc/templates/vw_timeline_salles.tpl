{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style type="text/css">
  @media print {
    button {
      display: none!important;
    }
  }

  #timeline_salle_body .event .body{
    line-height: 120% !important;
  }

  /* below events, force up */
  .event-container div.now {
    z-index: 50!important;
  }

  #timeline_salle_body td {
    border:solid 1px #bbb!important;
  }

  #timeline_salle_body div.minute-30 {
    border-top:solid 1px #ccc!important;
  }

  .plage_planning {
    position:relative;
  }

  div.hover_chir {
    display: none;
    position: absolute;
    top:0;
    white-space: nowrap;
    background-color: white;
    font-size: 1.4em;
  }

  .plage_planning:hover div.hover_chir {
    display: block;
    left:-.1em;

    transform-origin: 100% 100%;
    -webkit-transform-origin: 100% 100%;
    -moz-transform-origin: 100% 100%;
    -ms-transform-origin: 100% 100%;

    transform: rotate(-90deg);
    -webkit-transform: translate(-100%, 0) rotate(-90deg) ;
    -moz-transform: translate(-100%, 0) rotate(-90deg);
    -ms-transform: translate(-100%, 0) rotate(-90deg);
  }

  div.hidden_content {
    display: none;
  }

  .planning .plage_planning {
    overflow: visible;
  }

  .plage_planning div.plageop_header {
    min-width: 190px;
    text-align: center;
    position: absolute;
    top: -33px;
    left: 0px;
    background-color: rgb(239, 191, 153);
    border-top: solid 1px #888;
    border-right: solid 1px #888;
    border-left: solid 1px #888;
    border-radius: 3px 3px 0 0;
    padding: 3px 3px 2px 3px;
    margin-left: -1px;
    margin-bottom: -1px;
  }

  .planning .tl_operation {
    z-index:20;
  }

  .planning .tl_operation:hover {
    z-index:100;
    overflow:visible;
    min-height:35px;
    opacity: 1;
  }

  .planning .tl_operation:hover .body {
    border: solid 1px #444;
    box-shadow: 0 1px 4px black;
  }

  .tl_operation:hover div.hidden_content {
    display: block!important;
  }
</style>

<script type="text/javascript">
  /* Print and display buttons actions */
  printBacteriologie = function() {
    var form = getForm('timeline_filters');
    var url = new Url("bloc", "print_bacterio");
    url.addParam("date" , $V(form.elements['date']));
    url.addParam("blocs_ids[]", $V(form.elements['blocs_ids']), true);
    url.popup(700, 600, 'Bacteriologie');
  };

  printAnapath = function() {
    var form = getForm('timeline_filters');
    var url = new Url("bloc", "print_anapath");
    url.addParam("date" , $V(form.elements['date']));
    url.addParam("blocs_ids[]", $V(form.elements['blocs_ids']), true);
    url.popup(700, 600, 'Anapath');
  };

  presentationMode = function() {
    var form = getForm('timeline_filters');
    var url = new Url('bloc', 'vw_timeline_salles');
    url.addParam('date', $V(form.elements['date']));
    url.addParam('blocs_ids[]', $V(form.elements['blocs_ids']));
    url.addParam('display', 'fullscreen');
    url.popup("100%", "100%", 'Mode présentation');
  };

  suiviBloc = function() {
    var url =new Url('bloc', 'vw_suivi_bloc');
    url.addParam('type_view', 'all');
    url.popup('100%', '100%', 'Suivi bloc');
  };

  toggleAutoRefresh = function(button) {
    button.toggleClassName('play');
    button.toggleClassName('pause');
    if (!(window.autoRefreshTimelineSalle)) {
      {{math assign=period equation="a*1000" a="dPbloc affichage time_autorefresh"|gconf}}
      {{if $period && $period > 0}}
        window.autoRefreshTimelineSalle = setInterval(displayTimelineSalle.curry(getForm('timeline_filters')), {{$period}});
      {{/if}}
    }
    else {
      clearTimeout(window.autoRefreshTimelineSalle);
    }
  };

  /* Timeline displays and form functions */

  displayTimelineSalle = function(form) {
    var url = new Url('bloc', 'ajax_timeline_salles');
    url.addParam('date', $V(form.elements['date']));
    url.addParam('blocs_ids[]', $V(form.elements['blocs_ids']));
    url.addParam('salles_ids', $$('.salle_selector:checked').invoke('get', 'salle_id').join('|'));
    url.addParam('hour_min', $V(form.elements['hour_min']));
    url.addParam('hour_max', $V(form.elements['hour_max']));
    url.requestUpdate('timeline_salle_body');
  };

  refreshDate = function(form, refresh) {
    var months = Control.DatePicker.Language['fr'].months;
    var days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']
    var date = new Date($V(form.elements['date']));
    $('date_placeholder').innerHTML = days[date.getDay()] + ' ' + date.getDate() + ' ' + months[date.getMonth()].toLowerCase() + ' ' + date.getFullYear();

    if (refresh) {
      form.onsubmit();
    }
  };

  refreshSelectedBlocs = function(form) {
    var blocs = $V(form.elements['blocs_ids']);
    $$('label.salle_display').each(function(element) {
      if (blocs.indexOf(element.get('bloc_id')) != -1) {
        element.down('input').checked = true;
        element.show();
        element.up().show();
      }
      else {
        element.hide();
        element.down('input').checked = false;
      }
    });

    $$('span.bloc_display').each(function(element) {
      if (blocs.indexOf(element.get('bloc_id')) != -1) {
        element.show();
      }
      else {
        element.hide();
      }
    });

    if (blocs.length == 0) {
      $('bloc_none').show();
    }
    else {
      $('bloc_none').hide();
    }
  };

  showFilters = function() {
    Modal.open($('timeline_salle_filters'), {title: "Filtres d'affichage", showClose: true});
  };

  showLegend = function() {
    Modal.open($('timeline_salle_legend'), {title: "Légende", showClose: true});
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

  /* Timeline menu actions */

  editPlageOp = function(plageop_id) {
    var form = getForm('timeline_filters');
    var url = new Url('bloc', 'inc_edit_planning');
    url.addParam('plageop_id', plageop_id);
    url.addParam('date', $V(form.elements['date']));
    url.requestModal(800, null, {
      onClose: displayTimelineSalle.curry(getForm('timeline_filters'))
    });
  };

  editOperation = function(operation_id) {
    var url = new Url('planningOp', 'vw_edit_planning');
    url.addParam('operation_id', operation_id);
    url.addParam('dialog', 1);
    url.requestModal('90%', '90%', {
      onClose: displayTimelineSalle.curry(getForm('timeline_filters'))
    });
  };

  orderOperations = function(plageop_id) {
    var url = new Url('bloc', 'vw_edit_interventions');
    url.addParam('plageop_id', plageop_id);
    url.requestModal('90%', '90%', {
      onClose: displayTimelineSalle.curry(getForm('timeline_filters'))
    });
  };

  printOperation = function(operation_id) {
    var url = new Url('salleOp', 'print_feuille_bloc');
    url.addParam('operation_id', operation_id);
    url.popup(700, 600, 'FeuilleBloc');
  };

  Main.add(function() {
    ViewPort.SetAvlHeight($('timeline_salle_body'), 1.0);
    Calendar.regField(getForm('timeline_filters').elements['date'], null, {noView: true});
    refreshSelectedBlocs(getForm('timeline_filters'));
    displayTimelineSalle(getForm('timeline_filters'));
    {{if $display == 'normal'}}
      if (Preferences.startAutoRefreshAtStartup == 1) {
        toggleAutoRefresh($('btn_auto_refresh_timeline'));
      }
    {{else}}
      toggleAutoRefresh($('btn_auto_refresh_timeline'));
    {{/if}}
  });
</script>

<div class="buttons_placeholder" style="min-height: 22px;">
  {{if $display == 'normal'}}
    <span style="float: right;">
      {{me_button icon=print label=COperation-labo onclick="printBacteriologie();"}}
      {{me_button icon=print label=COperation-anapath onclick="printAnapath();"}}
      {{me_button icon=print label=COperation-print-suivi onclick="\$('timeline_placeholder').print();"}}
      {{me_dropdown_button button_icon=print button_label=Print button_class="me-tertiary"}}
      <button type="button" class="search me-tertiary me-float-none" style="float: right;" onclick="showLegend();">
        {{tr}}Legend{{/tr}}
      </button>
      <button type="button" class="lookup" onclick="presentationMode();">
        Mode présentation
      </button>
      <button type="button" class="lookup" onclick="suiviBloc()">
        Suivi bloc
      </button>
      <button type="button" class="fa fa-cog notext" style="float: right" onclick="selectView();">
        Changer le mode d'affichage de la vue
      </button>
    </span>
    <button id="btn_auto_refresh_timeline" type="button" class="play notext" onclick="toggleAutoRefresh(this);" style="float: left;">
      Rechargement automatique de la vue ({{tr}}config-dPbloc-CPlageOp-time_autorefresh-{{"dPbloc affichage time_autorefresh"|gconf}}{{/tr}})
    </button>
  {{else}}
    <button id="btn_auto_refresh_timeline" type="button" class="play notext" onclick="toggleAutoRefresh(this);" style="float: left;">
      Rechargement automatique de la vue ({{tr}}config-dPbloc-CPlageOp-time_autorefresh-{{"dPbloc affichage time_autorefresh"|gconf}}{{/tr}})
    </button>
    <button type="button" onclick="App.fullscreen();" style="float: right;">
      <i class="fa fa-lg fa-arrows-alt"></i>
      Plein écran
    </button>
  {{/if}}
</div>

<div id="timeline_placeholder">
  <div id="timeline_header" style="width: 100%; text-align: center;">
    <form name="timeline_filters" method="get" onsubmit="return displayTimelineSalle(this);">
      <h1 class="no-break" style="margin-top: 0; margin-bottom: 5px;">
        <span id="date_placeholder">
          {{$date|date_format:$conf.longdate}}
        </span>
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="refreshDate(this.form, true);">
      </h1>

      <div id="blocs_spaceholder" style="font-weight: bold;">
        <button type="button" class="search notext" title="Sélectionner les blocs opératoires" onclick="showFilters();"></button>
        Blocs :
        {{foreach from=$blocs item=_bloc}}
          <span class="bloc_display" data-bloc_id="{{$_bloc->_id}}"{{if !array_key_exists($_bloc->_id, $selected_blocs)}} style="display: none;"{{/if}}>{{$_bloc}}</span>
        {{/foreach}}
        <span id="bloc_none"{{if $blocs|@count != 0}} style="display: none;"{{/if}}>
          Aucun bloc sélectionné
        </span>
      </div>

      <div>

        <span id="bloc_none"{{if $blocs|@count != 0}} style="display: none;"{{/if}}>
          Aucun bloc sélectionné
        </span>
      </div>

      <table id="timeline_salle_filters" class="form" style="display: none;">
        <tr>
          <th>
            <label for="bloc_ids">Blocs</label>
          </th>
          <td>
            <select name="blocs_ids" multiple="3" onchange="refreshSelectedBlocs(this.form)">
              {{foreach from=$blocs item=_bloc name=blocs}}
                <option value="{{$_bloc->_id}}" data-bloc_name="{{$_bloc}}" {{if (is_array($blocs_ids) && in_array($_bloc->_id, $blocs_ids)) || !is_array($blocs_ids) && $smarty.foreach.blocs.first}} selected{{/if}}>
                  {{$_bloc}}
                </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>
            <label for="hour_min">
              Heures
            </label>
          </th>
          <td>
            Afficher de
            <select name="hour_min">
              {{section name=hour_min start=0 loop=24}}
                <option value="{{$smarty.section.hour_min.index}}"{{if $hour_min == $smarty.section.hour_min.index}} selected{{/if}}>
                  {{$smarty.section.hour_min.index}}h
                </option>
              {{/section}}
            </select> à
            <select name="hour_max">
              {{section name=hour_max start=0 loop=24}}
                <option value="{{$smarty.section.hour_max.index}}"{{if $hour_max == $smarty.section.hour_max.index}} selected{{/if}}>
                  {{$smarty.section.hour_max.index}}h
                </option>
              {{/section}}
            </select>
          </td>
        </tr>
        <tr>
          <th>
            <label>Salles</label>
          </th>
          <td>
            {{foreach from=$blocs item=_bloc}}
              {{if $_bloc->_ref_salles|@count}}
                <div id="bloc_{{$_bloc->_id}}_salles" style="display: none;">
                  {{foreach from=$_bloc->_ref_salles item=_salle}}
                    <label class="salle_display" style="font-weight: normal;" data-bloc_id="{{$_bloc->_id}}">
                      <input type="checkbox" class="salle_selector bloc-{{$_bloc->_id}}" data-salle_id="{{$_salle->_id}}" {{if array_key_exists($_bloc->_id, $selected_blocs)}} checked{{/if}}/>
                          {{$_salle->nom}}
                    </label>
                  {{/foreach}}
                </div>
              {{/if}}
            {{/foreach}}
          </td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            <button type="button" class="tick" onclick="Control.Modal.close(); this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
          </td>
        </tr>
      </table>
    </form>
  </div>

  <div id="timeline_salle_body">

  </div>
</div>

<table class="tbl" id="timeline_salle_legend" style="display: none;">
  <tr>
    <td style="width: 20px; background-color: darkgreen"></td>
    <td>
      Intervention commencée et terminée
    </td>
  </tr>
  <tr>
    <td style="width: 20px; background-color: darkorange"></td>
    <td>
      Intervention commencée ou terminée
    </td>
  </tr>
  <tr>
    <td style="width: 20px; background-color: lightgrey"></td>
    <td>
      Intervention ni commencée ni terminée
    </td>
  </tr>
  <tr>
    <td style="width: 20px; background-color: firebrick"></td>
    <td>
      Intervention commencée et/ou terminée en dehors de la vacation
    </td>
  </tr>
</table>

<div id="select_planning_view" style="display: none;">
  {{mb_include module=bloc template=select_planning_bloc_view}}
</div>
