{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">{{tr}}CExObject-legend-Usage statistics.{{/tr}}</div>

<form name="exobject-filter" method="get" data-loaded="" onsubmit="return onSubmitFormAjax(this, null, 'exobject-results');">
  <input type="hidden" name="m" value="forms" />
  <input type="hidden" name="a" value="ajax_vw_ex_object_stats" />

  <input type="hidden" class="date notNull" name="ex_object_date_min" value="{{$ex_object_date_min}}" />
  &raquo;
  <input type="hidden" class="date notNull" name="ex_object_date_max" value="{{$ex_object_date_max}}" />

  <select name="ex_object_grouping" onchange="this.form.onsubmit();">
    <option value="day" {{if $ex_object_grouping == 'day'}}selected{{/if}}>{{tr}}common-Day{{/tr}}</option>
    <option value="week" {{if $ex_object_grouping == 'week'}}selected{{/if}}>{{tr}}common-Week{{/tr}}</option>
    <option value="month" {{if $ex_object_grouping == 'month'}}selected{{/if}}>{{tr}}common-Month{{/tr}}</option>
  </select>

  <label>
    {{tr}}common-Threshold{{/tr}}
    <input type="text" name="ex_object_limit_threshold" value="{{$ex_object_limit_threshold}}" size="3" /> %
  </label>

  <button type="submit" class="stats">{{tr}}Display{{/tr}}</button>
</form>

<hr class="me-no-display"/>
<div id="exobject-results"></div>