{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour_types value='Ox\Mediboard\PlanningOp\CSejour'|static:'types'}}
{{assign var=date_min value='Ox\Core\CMbDT::date'|static_call:'-1 MONTH'}}
{{assign var=date_max value='Ox\Core\CMbDT::date'|static_call:null}}

<script type="text/javascript">
  checkFilters = function (form) {
    if ($V(form.elements['constant']) != '' && $V(form.elements['operator']) != '' && $V(form.elements['value']) != '') {
      $('btn_export_constant_stats').enable();
      $('btn_show_constant_stats').enable();
    }
    else {
      $('btn_export_constant_stats').disable();
      $('btn_show_constant_stats').disable();
    }
  };

  exportStatConstant = function (form) {
    $V(form.elements['export'], 1);
    $V(form.elements['suppressHeaders'], 1);

    form.submit();

    $V(form.elements['export'], '');
    $V(form.elements['suppressHeaders'], '');
  };

  Main.add(function () {
    var form = getForm('filterConstantsStats');
    var url = new Url('patients', 'ajax_do_autocomplete_constants');
    url.addParam('show_main_unit', 1);
    url.addParam('show_formfields', 0);
    url.autoComplete(form.elements['_search_constants'], null, {
      minChars:      2,
      dropdown:      true,
      updateElement: function (selected) {
        $V(form.elements['_search_constants'], selected.down('.view').getText().strip(), false);
        $V(form.elements['constant'], selected.get('constant'));
        $('constant_unit').update(selected.get('unit'));
      }
    });

    Calendar.regField(form.elements['date_min']);
    Calendar.regField(form.elements['date_max']);
    form.elements['value'].addSpinner();
  });
</script>

<form name="filterConstantsStats" method="get" action="?" target="_blank">
  <input type="hidden" name="m" value="hospi">
  <input type="hidden" name="a" value="ajax_stats_constants">
  <input type="hidden" name="suppressHeaders" value="">
  <input type="hidden" name="export" value="">

  <table class="form">
    <tr>
      <th class="title" colspan="6">
        {{tr}}filters{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        <label for="date_min">{{tr}}date.From_long{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="date_min" value="{{$date_min}}">
      </td>
      <th>
        <label for="date_max">{{tr}}date.To_long{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="date_max" value="{{$date_max}}">
      </td>
      <th>
        <label for="sejour_type">{{tr}}CSejour-type{{/tr}}</label>
      </th>
      <td>
        <select name="sejour_type">
          <option value="">&mdash; {{tr}}Select{{/tr}}</option>
          {{foreach from=$sejour_types item=type}}
            <option value="{{$type}}">
              {{tr}}CSejour.type.{{$type}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="constant">{{tr}}CConstantComment-constant{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="constant" value="" onchange="checkFilters(this.form);">
        <input type="text" name="_search_constants" value="" class="autocomplete" placeholder="{{tr}}Search{{/tr}}">
        <span id="constant_unit">
      </td>
      <th>
        <label for="operator">{{tr}}Operator{{/tr}}</label>
      </th>
      <td>
        <select name="operator" onchange="checkFilters(this.form);">
          <option value="=">=</option>
          <option value="<="><=</option>
          <option value="<"><</option>
          <option value=">">></option>
          <option value=">=">>=</option>
        </select>
      </td>
      <th>
        <label for="value">{{tr}}CObservationResult-value{{/tr}}</label>
      </th>
      <td>
        <input type="text" name="value" value="" size="3" onchange="checkFilters(this.form);">
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="submit" id="btn_show_constant_stats" class="search"
                onclick="return onSubmitFormAjax(this.form, null, 'constants_stats_results');" disabled>
          {{tr}}Filter{{/tr}}
        </button>
        <button type="button" id="btn_export_constant_stats" class="download" onclick="exportStatConstant(this.form);" disabled>
          {{tr}}Export{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="constants_stats_results">

</div>