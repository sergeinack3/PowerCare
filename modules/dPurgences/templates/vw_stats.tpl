{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
var filterForm,
    options = {
      legend: {container: "graph-legend"},
      yaxis: {tickFormatter: yTickFormatter},
      y2axis: {min: null}
    },
    data;

var profiles = {
  lines: {
    lines: {show: true},
    bars: {show: false},
    mouse: {track: true},
    grid: {verticalLines: true},
    markers: {show: true, labelFormatter: function(obj) {
      var y = (Math.round(obj.y*100)/100);
      return y === 0 ? "" : y;
    }}
  },
  bars: {
    lines: {show: false},
    bars: {show: true},
    mouse: {track: false},
    grid: {verticalLines: false},
    markers: {show: false, labelFormatter: Flotr.defaultMarkerFormatter}
  }
};

function yTickFormatter(y) {
  return parseInt(y).toString();
}

function timeLabelFormatter(obj) {
  return obj.y+ "min";
}

function drawGraphs(data) {
  window.data = data || window.data;

  var mode = $V(getForm("stats-filter").mode);
  var container = $("graphs").update("");

  $H(data).each(function(pair) {
    var d = pair.value;
    if (!d || !d.series) return;

    var graph = new Element("div", {id:"stats-"+pair.key, style: "width: 600px; height: 400px; margin-right: 0; margin-left: auto;"});
    container.insert(graph);

    d.options = Object.merge(options, d.options);
    d.options = Object.merge(profiles[mode], d.options);

    var series = d.series;

    series.each(function(s){
      if (s.mouse && Object.isString(s.mouse.trackFormatter))
        s.mouse.trackFormatter = window[s.mouse.trackFormatter];
    });

    if (mode != "bars") {
      series = d.series.clone();
      series.pop();
    }

    $("graph-legend").update();
    Flotr.draw($('stats-'+pair.key), series, d.options);
  });
}

updateGraphs = function(form){
  if (!checkForm(form)) {
    return false;
  }
  if ($V(form.period) == 'HOUR') {
    $V(form.sortie, $V(form.entree));
    $V(form.sortie_da, $V(form.entree_da));
  }

  WaitingMessage.cover($("graphs"));

  var url = new Url("dPurgences", "ajax_json_stats");
  url.addFormData(form);
  url.requestJSON(drawGraphs);
  return false;
}

addAgeRange = function() {
  var rows_count = $$('#stats-filter-age_range tr').length;
  var min = DOM.input({type: 'text', name: 'age_min[]', value: '', size: 3});
  var max = DOM.input({type: 'text', name: 'age_max[]', value: '', size: 3});
  $('stats-filter-age_range').insert(
    DOM.tr(
      {className: 'age-range-row', id: 'age-range-row-' + rows_count},
      DOM.td({}, min),
      DOM.td({}, max, DOM.button({type: 'button', onclick: 'removeAgeRange(' + rows_count + ')', className: 'remove notext', title: 'Supprimer la ligne'}))
    )
  );
  min.addSpinner({min: 0});
  max.addSpinner({min: 0});
  $('stats-filter-age_range-min_label').setAttribute('rowspan', rows_count + 1);
  $('stats-filter-age_range-max_label').setAttribute('rowspan', rows_count + 1);
};

removeAgeRange = function(index) {
  var row = $('age-range-row-' + index);
  if (row) {
    row.remove();
    var rows_count = $$('#stats-filter-age_range tr').length;
    $('stats-filter-age_range-min_label').setAttribute('rowspan', rows_count);
    $('stats-filter-age_range-max_label').setAttribute('rowspan', rows_count);
  }
};

saveAgeRangePref = function(form) {
  var age_ranges = '';
  $$('table#age_range_container tr.age-range-row').each(function(row) {
    var range = '';
    if (row.down('input') && $V(row.down('input'))) {
      range = $V(row.down('input'));
    }

    if (row.down('input', 1) && $V(row.down('input', 1)) != '') {
      range = range + '-' + $V(row.down('input', 1));
    }

    if (age_ranges != '' && range != '') {
      age_ranges = age_ranges + '|' + range;
    }
    else if (range != '') {
      age_ranges = range;
    }
  });

  var form = getForm('editAgeRangePref');
  $V(form.elements['pref[stats_urgences_age_ranges]'], age_ranges);
  onSubmitFormAjax(form);
  Control.Modal.close();
};

Main.add(function () {
  var form = getForm('stats-filter');
  updateGraphs(form);
  $(form._percent).addSpinner({min: 0, step: 0.1});
  $$('input[name="age_min[]').each(function(input) {input.addSpinner({min:0});});
  $$('input[name="age_max[]').each(function(input) {input.addSpinner({min:0});});
});
</script>

<div>
  <form name="stats-filter" action="?" method="get" onsubmit="return updateGraphs(this)">
    <input type="hidden" name="suppressHeaders" value="1" />
    <input type="hidden" name="m" value="dPurgences" />

    <table class="layout" style="width: 100%;">
      <tr>
        <td class="thirdPane" style="vertical-align: top;">
          <fieldset>
            <legend>
              {{tr}}filters{{/tr}}
            </legend>
            <table class="form me-no-align me-no-box-shadow">
              <tr>
                <th><label for="entree">Date min</label></th>
                <td>{{mb_field object=$filter field=entree register=true form="stats-filter" prop="date"}}</td>
                <th><label for="sortie">Date max</label></th>
                <td>{{mb_field object=$filter field=sortie register=true form="stats-filter" prop="date"}}</td>
              </tr>
              <tr>
                <th><label for="days" title="Limiter la recherche aux jours de la semaines sélectionnés (CTRL + clic pour sélectionner plusieurs jours)">{{tr}}Days{{/tr}}</label></th>
                <td rowspan="2">
                  <select name="days[]" multiple>
                    <option value="1"{{if in_array(1, $days)}} selected{{/if}}>{{tr}}Monday{{/tr}}</option>
                    <option value="2"{{if in_array(2, $days)}} selected{{/if}}>{{tr}}Tuesday{{/tr}}</option>
                    <option value="3"{{if in_array(3, $days)}} selected{{/if}}>{{tr}}Wednesday{{/tr}}</option>
                    <option value="4"{{if in_array(4, $days)}} selected{{/if}}>{{tr}}Thursday{{/tr}}</option>
                    <option value="5"{{if in_array(5, $days)}} selected{{/if}}>{{tr}}Friday{{/tr}}</option>
                    <option value="6"{{if in_array(6, $days)}} selected{{/if}}>{{tr}}Saturday{{/tr}}</option>
                    <option value="7"{{if in_array(7, $days)}} selected{{/if}}>{{tr}}Sunday{{/tr}}</option>
                  </select>
                </td>
                <th><label for="holidays" title="Limiter la recherche aux jours fériés">{{tr}}CMbDT-holidays{{/tr}}</label></th>
                <td>
                  <input type="hidden" name="holidays" value="{{$holidays}}">
                  <input type="checkbox" name="_holidays" onclick="$V(this.form.elements['holidays'], this.checked ? '1' : '0');"{{if $holidays}} checked{{/if}}>
                </td>
              </tr>
              <tr>
                <td></td>
                <th><label for="select_service">{{tr}}CService{{/tr}}</label></th>
                <td>
                  <select name="service_id" id="select_service">
                    <option value="">&dash;&dash; {{tr}}All{{/tr}}</option>
                    {{foreach from=$services item=_service}}
                      <option value="{{$_service->_id}}">{{$_service}}</option>
                    {{/foreach}}
                  </select>
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
        <td style="vertical-align: top;">
          <fieldset style="height: 100%;">
            <legend>Statistique</legend>
            {{foreach from=$axes key=_axis item=_label}}
              <label style="width: 16em; display: inline-block;">
                {{if $_axis == "age"}}
                  <button type="button" class="fa fa-cog notext" style="float: right;" onclick="Modal.open('age_range_container', {title: 'Tranches d\'âge', showClose: true});">Configurer les tranches d'âge</button>
                {{/if}}
                <input type="radio" name="axe" value="{{$_axis}}" {{if $_axis == $axe}}checked{{/if}} onchange="this.form.onsubmit()" /> {{$_label}}
              </label>
            {{/foreach}}
          </fieldset>
        </td>
      </tr>
      <tr>
        <td class="thirdPane" style="vertical-align: top;">
          <fieldset>
            <legend>{{tr}}common-Display{{/tr}}</legend>
            <table class="form me-no-box-shadow me-no-align">
              <tr>
                <th>Grouper par</th>
                <td>
                  <select name="period" onchange="this.form.onsubmit()">
                    <option value="HOUR">{{tr}}common-Hour|pl{{/tr}}</option>
                    <option value="DAY" selected>{{tr}}Days{{/tr}}</option>
                    <option value="WEEK">{{tr}}Weeks{{/tr}}</option>
                    <option value="MONTH">{{tr}}Month{{/tr}}</option>
                  </select>
                </td>
              </tr>
              <tr>
                <th rowspan="2">Mode</th>
                <td>
                  <label>
                    <input type="radio" name="mode" value="bars" onchange="drawGraphs(window.data)" checked /> Barres
                  </label>
                  <label>
                    <input type="radio" name="mode" value="lines" onchange="drawGraphs(window.data)" /> Lignes
                  </label>
                </td>
              </tr>
              <tr>
                <td>
                  <label>
                    <input type="hidden" name="hide_cancelled" value="{{$hide_cancelled}}" />
                    <input type="checkbox" name="_hide_cancelled_view" {{if $hide_cancelled}}checked{{/if}}
                           onclick="$V(this.form.hide_cancelled, this.checked ? 1 : 0); this.form.onsubmit()"/> Cacher les annulés
                  </label>
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
        <td style="vertical-align: top;">
          <fieldset>
            <legend>Stats complémentaires</legend>
            {{foreach from=$axes_other key=_axis item=_label}}
              {{if $_axis == "diag_infirmier"}}
                <hr/>
              {{/if}}
              <label style="width: 16em; display: inline-block;">
                <input type="radio" name="axe" value="{{$_axis}}" {{if $_axis == $axe}}checked{{/if}} onchange="this.form.onsubmit()"/> {{$_label}}
              </label>
              {{if $_axis == "diag_infirmier"}}
                <label id="s_percent" title="{{tr}}CRPU-percent_diag-desc{{/tr}}">
                  {{tr}}CRPU-percent_diag{{/tr}} <input type="text"  name="_percent" value="0.5" size="2"/> %
                </label>
              {{/if}}
            {{/foreach}}
          </fieldset>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button" style="text-align: center; padding-top: 5px;">
          <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
          {{if "ecap"|module_active}}
            <button class="change" type="button" onclick="new Url('ecap', 'vw_anap').requestModal()" style="float: right">Extraction ANAP</button>
          {{/if}}
        </td>
      </tr>
    </table>
    <table class="form" id="age_range_container" style="display: none;">
      <tr>
        <th class="category" colspan="4">
          Tranches d'âge
          <button type="button" class="add notext" onclick="addAgeRange();">Ajouter une tranche d'âge</button>
        </th>
      </tr>
      <tbody id="stats-filter-age_range">
        {{foreach from=$age_min key=index item=_age_min name=age_range_loop}}
          <tr class="age-range-row" id="age-range-row-{{$index}}">
            {{if $smarty.foreach.age_range_loop.first}}
              <th id="stats-filter-age_range-min_label" rowspan="{{$age_min|@count}}">
                <label for="age_min[]">{{tr}}Age min{{/tr}}</label>
              </th>
            {{/if}}
            <td><input type="number" name="age_min[]" value="{{$_age_min}}" size="3"/></td>
            {{if $smarty.foreach.age_range_loop.first}}
              <th id="stats-filter-age_range-max_label" rowspan="{{$age_min|@count}}">
                <label for="age_max[]">{{tr}}Age max{{/tr}}</label>
              </th>
            {{/if}}
            <td>
              {{if !$smarty.foreach.age_range_loop.first}}
                <button type="button" class="remove notext" style="float: right;" onclick="removeAgeRange({{$index}});">Supprimer la ligne</button>
              {{/if}}
              <input type="number" name="age_max[]" value="{{if array_key_exists($index, $age_max)}}{{$age_max[$index]}}{{/if}}" size="3"/>
            </td>
          </tr>
        {{/foreach}}
      </tbody>
      <tr>
        <td class="button" colspan="4">
          <button type="button" class="tick me-primary" onclick="Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
          <button type="button" class="save me-secondary" onclick="saveAgeRangePref();">{{tr}}Validate{{/tr}} {{tr}}and{{/tr}} {{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<table class="main">
  <tr>
    <td id="graphs" style="width: 66%;"></td>
    <td id="graph-legend"></td>
  </tr>
</table>


<form name="editAgeRangePref" method="post">
  <input type="hidden" name="m" value="admin">
  <input type="hidden" name="dosql" value="do_preference_aed">
  <input type="hidden" name="user_id" value="{{$app->user_id}}">
  <input type="hidden" name="pref[stats_urgences_age_ranges]" value="{{$app->user_prefs.stats_urgences_age_ranges}}">
</form>
