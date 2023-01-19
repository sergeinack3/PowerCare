{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="ccam_selector"}}

<script type="text/javascript">
  var oCcamField = null,
    filterForm,
    options = {},
    data;

  var profiles = {
    lines: {
      lines: {show: true},
      bars:  {show: false},
      mouse: {track: true},
      grid:  {verticalLines: true}
    },
    bars:  {
      lines: {show: false},
      bars:  {show: true},
      mouse: {track: false},
      grid:  {verticalLines: false}
    }
  };

  function yTickFormatter(y) {
    return parseInt(y).toString();
  }

  function drawGraphs(data) {
    window.data = data || window.data;

    var container = $("graphs").update("");

    var style = window.data.comp ?
      "width: 800px; height: 500px;" :
      "width: 600px; height: 300px;";

    $H(window.data).each(function (pair) {
      var d = pair.value;
      if (!d || !d.series) {
        return;
      }

      var graph = new Element("div", {id: "stats-" + pair.key, style: style + " margin: auto;"});
      container.insert(graph);

      d.options = Object.merge(options, d.options);

      Flotr.draw(
        $('stats-' + pair.key),
        d.series, Object.extend({
          yaxis: {tickFormatter: yTickFormatter}
        }, d.options)
      );
    });
  }

  function updateGraphs(form) {
    WaitingMessage.cover($("graphs"));

    var url = new Url("bloodSalvage", "httpreq_json_stats");
    url.addFormData(form);
    url.requestJSON(drawGraphs);
    return false;
  }

  Main.add(function () {
    filterForm = getForm('stats-filter');

    updateGraphs(filterForm);

    oCcamField = new TokenField(filterForm["filters[codes_ccam]"], {
      onChange: updateTokenCcam
    });

    updateTokenCcam($V(filterForm["filters[codes_ccam]"]));

    switchMode("{{$mode}}");

    var url = new Url("dPccam", "autocompleteCcamCodes");
    url.autoComplete(filterForm._codes_ccam, '', {
      minChars:      1,
      dropdown:      true,
      width:         "250px",
      updateElement: function (selected) {
        $V(filterForm._codes_ccam, selected.down("strong").innerHTML);
        oCcamField.add($V(filterForm._codes_ccam), true);
      }
    });
  });

  function switchMode(mode) {
    var type = (mode === "comparison") ? "radio" : "checkbox";

    $$(".comparison input").each(function (input) {
      input.hide();
      input.disabled = true;
    });

    $$(".comparison input[type=" + type + "]").each(function (input) {
      input.show();
      input.disabled = null;
    });

    if ((type == "radio") && ($$(".comparison input:checked").length < 2)) {
      $$(".comparison input[name=comparison_left]")[0].checked = true;
      $$(".comparison input[name=comparison_right]")[1].checked = true;
    }
  }

  CCAMSelector.init = function () {
    this.sForm = "stats-filter";
    this.sView = "_codes_ccam";
    this.sChir = "filters[chir_id]";
    this.sClass = "_class";
    this.pop();
  }

  function updateTokenCcam(v) {
    var i, codes = v.split("|").without("");
    for (i = 0; i < codes.length; i++) {
      codes[i] += '<button class="remove notext" type="button" onclick="oCcamField.remove(\'' + codes[i] + '\')"></button>';
    }
    $("list_codes_ccam").update(codes.join(", "));
    $V(filterForm._codes_ccam, '');
  }
</script>

<form name="stats-filter" action="?" method="get" onsubmit="return updateGraphs(this)">
  <input type="hidden" name="suppressHeaders" value="1" />

  <table class="main form" style="table-layout: fixed;">
    <tr>
      <th><label for="months_count">{{tr}}common-since{{/tr}}</label></th>
      <td>
        <select name="months_count">
          <option value="24" {{if $months_count == 24}}selected="selected"{{/if}}>24 {{tr}}Month{{/tr}}</option>
          <option value="12" {{if $months_count == 12}}selected="selected"{{/if}}>12 {{tr}}Month{{/tr}}</option>
          <option value="6" {{if $months_count == 6}}selected="selected"{{/if}}>6 {{tr}}Month{{/tr}}</option>
        </select>
      </td>
      <th>
        <label for="_codes_ccam">{{tr}}CConsultation-codes_ccam{{/tr}}</label>
        <!--
        <span class="comparison">
          <input type="checkbox" value="1" name="comparison[_codes_ccam]" />
          <input type="radio" value="_codes_ccam" name="comparison_left" />
          <input type="radio" value="_codes_ccam" name="comparison_right" />
        </span>
        -->
      </th>
      <td>
        <input type="hidden" name="filters[codes_ccam]" value="{{$filters.codes_ccam}}" />
        <input type="hidden" name="_class" value="COperation" />
        <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()" size="10" value="" />
        <button class="search notext" type="button" onclick="CCAMSelector.init()">{{tr}}Search{{/tr}}</button>
        <button class="tick notext" type="button" onclick="oCcamField.add($V(this.form['_codes_ccam']))">{{tr}}Add{{/tr}}</button>
      </td>

      <td rowspan="4" style="white-space: normal;">
        {{foreach from=$mean_fields item=_field}}
        <div style="display: block;">
            <span class="comparison">
              <input type="checkbox" value="1" name="filters[{{$_field}}]" {{if @$filters.$_field}}checked="checked"{{/if}} />
              <input type="radio" value="{{$_field}}" name="comparison_left" />
              <input type="radio" value="{{$_field}}" name="comparison_right" />
            </span>

          <label for="filters[{{$_field}}]">
            {{if $_field == "age"}}
                {{tr}}msg-CBloodSalvage.filter-age{{/tr}}
            {{else}}
                {{tr}}CBloodSalvage-{{$_field}}{{/tr}}
            {{/if}}
          </label>
        </div>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>
        <label for="filters[chir_id]">{{tr}}COperation-chir_id{{/tr}}</label>
      </th>
      <td>
        <select name="filters[chir_id]">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$fields.chir_id item=user}}
          <option value="{{$user->_id}}" {{if $filters.chir_id == $user->_id}}selected="selected"{{/if}}
                  style="border-left: #{{$user->_ref_function->color}} 5px solid;">{{$user->_view}}</option>
          {{/foreach}}
        </select>
      </td>
      <th>{{tr}}msg-CBloodSalvage.code_choosed{{/tr}} :</th>
      <td id="list_codes_ccam"></td>
    </tr>

    <tr>
      <th>
        <label for="filters[anesth_id]">{{tr}}CBloodSalvage.anesthesist{{/tr}}</label>
      </th>
      <td>
        <select name="filters[anesth_id]">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$fields.anesth_id item=user}}
          <option value="{{$user->_id}}" {{if $filters.anesth_id == $user->_id}}selected="selected"{{/if}}
                  style="border-left: #{{$user->_ref_function->color}} 5px solid;">{{$user->_view}}</option>
          {{/foreach}}
        </select>
      </td>
      <th>
        <label for="filters[code_asa]">{{tr}}Code{{/tr}} {{tr}}CConsultAnesth-ASA{{/tr}}</label>
        <!--
        <span class="comparison">
          <input type="checkbox" value="1" name="comparison[code_asa]" />
          <input type="radio" value="code_asa" name="comparison_left" />
          <input type="radio" value="code_asa" name="comparison_right" />
        </span>
        -->
      </th>
      <td>
        <select name="filters[code_asa]">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$fields.codes_asa item=code}}
          <option value="{{$code}}" {{if $code == $filters.code_asa}}selected="selected"{{/if}}>{{$code}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>
        <label for="filters[cell_saver_id]">{{tr}}CCellSaver{{/tr}}</label>
        <span class="comparison">
          <input type="checkbox" value="1" name="comparison[cell_saver_id]" />
          <input type="radio" value="cell_saver_id" name="comparison_left" />
          <input type="radio" value="cell_saver_id" name="comparison_right" />
        </span>
      </th>
      <td>
        <select name="filters[cell_saver_id]">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$fields.cell_saver_id item=_cell_saver}}
          <option value="{{$_cell_saver->_id}}"
                  {{if $filters.cell_saver_id == $_cell_saver->_id}}selected="selected"{{/if}}>{{$_cell_saver}}</option>
          {{/foreach}}
        </select>
      </td>
      <th></th>
      <td></td>
    </tr>

    <tr>
      <th colspan="2">
        <label>
          {{tr}}msg-CBloodSalvage.comparison_mode{{/tr}}
          <input type="checkbox" name="mode"
                 onclick="switchMode(this.checked ? 'comparison' : '')" {{if $mode == "comparison"}} checked="checked" {{/if}}
                 value="comparison" />
        </label>
        <br />
        <label>
          {{tr}}msg-CBloodSalvage.lines_diagrams{{/tr}}
          <input type="checkbox" onclick="options = profiles[this.checked ? 'lines' : 'bars']; drawGraphs()" />
        </label>
      </th>
      <td colspan="2">
        <button type="submit" class="search">{{tr}}Filtrer{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="graphs"></div>
