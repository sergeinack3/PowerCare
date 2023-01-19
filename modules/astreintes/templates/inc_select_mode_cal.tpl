{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=plage ajax=$ajax}}

<script>
  Main.add(function() {
    var form = getForm("filterPlanningAstreinte");
    window.calendar_planning = Calendar.regField(form.date);

    PlageAstreinte.filterCategoryCalendar();
  });
</script>

<form name="filterPlanningAstreinte" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="calendrierAstreinte" />
  <table class="width100 me-align-auto">
    <tr>
      <td>
        <button type="button" style="float:left;" onclick="PlageAstreinte.modal()" class="new me-primary">
          {{tr}}Create{{/tr}} {{tr}}CPlageAstreinte{{/tr}}
        </button>
      </td>


      <td rowspan="2">
        <table class="tbl form" style="width: 50% !important;">
          <tr>
            {{me_form_field nb_cells=2 label="Category"}}
              <select name="category" id="category">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{foreach from=$categories item=_category}}
                  <option value="{{$_category->_id}}"
                          {{if $_category->_id == $current_category_id}}selected{{/if}}>{{$_category->name}}</option>
                {{/foreach}}
              </select>
            {{/me_form_field}}
          </tr>

          <tr>
            <th>Mode</th>
            <td>
              <label>
                <input type="radio" name="mode" value="day" {{if $mode == "day"}}checked{{/if}} onclick="submit(this)"/>
                {{tr}}day{{/tr}}
              </label>

              <label>
                <input type="radio" name="mode" value="week" {{if $mode == "week"}}checked{{/if}} onclick="submit(this)"/>
                {{tr}}week{{/tr}}
              </label>

              <label>
                <input type="radio" name="mode" value="month" {{if $mode == "month"}}checked{{/if}} onclick="submit(this)"/>
                {{tr}}month{{/tr}}
              </label>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td>
        <button type="button"
                class="print"
                onclick="PlageAstreinte.printShifts('filterPlanningAstreinte');">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
  </table>

  <div style="text-align: center;" class="me-margin-bottom-8">
    <a class="button notext left" href="?m={{$m}}&date={{$prev}}&mode={{$mode}}">&lt;&lt;&lt;</a>
    <input type="hidden" name="date" value="{{$date}}" class="date" onchange="this.form.submit()"/>
    <a class="button notext right" href="?m={{$m}}&date={{$next}}&mode={{$mode}}">&gt;&gt;&gt;</a>
  </div>
</form>