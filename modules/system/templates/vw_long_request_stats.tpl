{{*
* @package Mediboard\System
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("Filter-Stats");

    form.elements.duration.addSpinner({min: 1});
    form.elements.limit.addSpinner({min: 1});
    form.elements.threshold.addSpinner({min: 1, max: 100, step: 5});

    Calendar.regField(form.elements.date);

    form.onsubmit();
  });
</script>

<form name="Filter-Stats" method="get" onsubmit="return onSubmitFormAjax(this, null, 'logs_stats');">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_stats_long_request_logs"/>

    {{mb_field object=$filter field=module_action_id hidden=true}}

  <table class="main">
    <tr>
      <th>
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.onsubmit();"/>

        <label>
          &mdash; {{tr}}common-Interval{{/tr}}

          <select name="interval" onchange="this.form.onsubmit();">
            <option value="day" {{if $interval == "day"  }} selected{{/if}}>{{tr}}common-Day{{/tr}}</option>
            <option value="week" {{if $interval == "week" }} selected{{/if}}>{{tr}}common-Week{{/tr}}</option>
            <option value="month" {{if $interval == "month"}} selected{{/if}}>{{tr}}common-Month{{/tr}}</option>
          </select>
        </label>

        <label>
          &mdash; {{tr}}common-Number of items{{/tr}}

          <input type="text" name="limit" value="{{$limit}}" size="1"/>
        </label>

        <label>
          &mdash; {{tr}}common-Threshold{{/tr}}

          <input type="text" name="threshold" value="{{$threshold}}" size="1"/> %
        </label>
      </th>
    </tr>

    <tr>
      <th>
        <label>
            {{mb_label object=$filter field=duration}}

          <select name="duration_operand">
            <option value=">="> >=</option>
            <option value=">"> ></option>
            <option value="<="> <=</option>
            <option value="<"> <</option>
            <option value="="> =</option>
          </select>

            {{mb_field object=$filter field=duration canNull=true size=2}}
        </label>

        <label>
          &mdash; {{tr}}common-noun-View{{/tr}}

          <select name="group_mod" style="width: 20em;" onchange="this.form.onsubmit();">
            <option value="1" {{if $group_mod == '1'}}selected{{/if}}>{{tr}}CModuleAction-Get all{{/tr}}</option>
            <option value="2" {{if $group_mod == '2'}}selected{{/if}}>{{tr}}CModuleAction-Group by
              module{{/tr}}</option>

            <optgroup label="{{tr}}CModule|pl{{/tr}}">
                {{foreach from=$modules item=_module}}
              <option {{if $group_mod == $_module->mod_name}}selected{{/if}} value="{{$_module->mod_name}}">
                  {{$_module}}
              </option>
              {{/foreach}}
            </optgroup>
          </select>
        </label>

        <label for="user_type">Type utilisateur</label>
        <select name="user_type" onchange="this.form.onsubmit()">
          <option value="0" selected="selected">Tous</option>
          <option value="1">Humains</option>
          <option value="2">Robots</option>
          <option value="3">Public</option>
        </select>
      </th>
    </tr>

    <tr>
      <th>
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </th>
    </tr>
  </table>
</form>

<div id="logs_stats"></div>
