{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("Filter-Log");
    form.elements.duration.addSpinner({min: 10});

    // Autocomplete des actions (dépend du module sélectionné)
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CModuleAction");
    url.addParam("view_field", "action");
    url.addParam("input_field", "filter_action");
    url.autoComplete(form.elements.filter_action, null, {
      minChars:           2,
      method:             "get",
      dropdown:           true,
      callback:           function (input, queryString) {
        var form = getForm("Filter-Log");
        var module = $V(form.elements.filter_module);

        if (module) {
          return queryString + "&where[module]=" + module;
        }

        return queryString;
      },
      afterUpdateElement: function (field, selected) {
        $V(form.elements.filter_action, selected.select(".view")[0].getText(), false);
        $V(form.elements.module_action_id, selected.get('id'));
        form.onsubmit();
      }
    });

    LongRequestLog.refresh();
  });
</script>

<form name="Filter-Log" method="get" onsubmit="return LongRequestLog.refresh();">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_list_long_request_logs"/>

  <input type="hidden" name="start" value="0"/>

    {{mb_field object=$filter field=module_action_id hidden=true}}

  <table class="main form">
    <tr>
      <th class="narrow">{{mb_label object=$filter field=user_id}}</th>
      <td>
        <select name="user_id" class="ref" style="width: 200px;" onchange="$V(this.form.elements.start, 0);">
          <option value="">&mdash; {{tr}}CUser.all{{/tr}}</option>

            {{foreach from=$user_list item=_user}}
              <option value="{{$_user->_id}}" {{if $_user->_id == $filter->user_id}}selected="selected"{{/if}} >
                  {{$_user}}
              </option>
            {{/foreach}}
        </select>

        <label>
            {{tr}}CModule{{/tr}}

          <select name="filter_module" style="width: 20em;"
                  onchange="$V(this.form.elements.start, 0); $V(this.form.elements.filter_action, '', false);
                  $V(this.form.elements.module_action_id, '', false); LongRequestLog.checkModule(this);">
            <option value="">&mdash; {{tr}}CModule.all{{/tr}}</option>

              {{foreach from=$modules item=_module}}
                <option value="{{$_module->mod_name}}">
                    {{$_module}}
                </option>
              {{/foreach}}
          </select>
        </label>

        <label>
            {{tr}}Action{{/tr}}

          <input type="text" class="autocomplete" name="filter_action" value="" size="30" disabled
                 placeholder="{{tr}}CModuleAction-All actions{{/tr}}"/>

          <button type="button" class="erase notext compact"
                  onclick="$V(this.form.elements.filter_action, ''); $V(this.form.elements.module_action_id, '');">
              {{tr}}Reset{{/tr}}
          </button>
        </label>
      </td>
    </tr>

    <tr>
      <th class="narrow">{{mb_label object=$filter field=_enslaved}}</th>
      <td>
        <label><input type="radio" name="enslaved" value="all" checked/>{{tr}}All{{/tr}}</label>
        <label><input type="radio" name="enslaved" value="true"/>{{tr}}Yes{{/tr}}</label>
        <label><input type="radio" name="enslaved" value="false"/>{{tr}}No{{/tr}}</label>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=datetime_start}}</th>
      <td>
          {{mb_field object=$filter field=_datetime_start_min form="Filter-Log" register=true}}
        &raquo;
          {{mb_field object=$filter field=_datetime_start_max form="Filter-Log" register=true}}

        <label>
            {{mb_label object=$filter field=duration}}

          <select name="duration_operand">
            <option value=">="> >=</option>
            <option value=">"> ></option>
            <option value="<="> <=</option>
            <option value="<"> <</option>
            <option value="="> =</option>
          </select>

            {{mb_field object=$filter field=duration canNull=true}}
        </label>

          {{mb_label object=$filter field=_user_type}}
          {{mb_field object=$filter field=_user_type typeEnum='radio'}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=datetime_end}}</th>
      <td>
          {{mb_field object=$filter field=_datetime_end_min form="Filter-Log" register=true}}
        &raquo;
          {{mb_field object=$filter field=_datetime_end_max form="Filter-Log" register=true}}

        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>

        <button type="button" class="lookup" onclick="LongRequestLog.showPurge(this.form);">
            {{tr}}common-action-Purge{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="list-logs"></div>
