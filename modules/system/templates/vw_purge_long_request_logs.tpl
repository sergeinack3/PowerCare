{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=long_request_log}}

<script>
  Main.add(function () {
    var form = getForm("Purge-Log");
    form.elements.duration.addSpinner({min: 10});
    form.elements.purge_limit.addSpinner({min: 1});

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
  });

  function checkModule(input) {
    var form = input.form;

    ($V(input)) ? form.elements.filter_action.disabled = '' : form.elements.filter_action.disabled = '1';
  }
</script>

<form name="Purge-Log" method="get" onsubmit="return false;">
  {{mb_field object=$log field=module_action_id hidden=true}}

  <table class="main form">
    <tr>
      <th class="narrow">{{mb_label object=$log field=user_id}}</th>
      <td>
        <select name="user_id" class="ref" style="width: 200px;">
          <option value="">&mdash; {{tr}}CUser.all{{/tr}}</option>
          {{foreach from=$user_list item=_user}}
            <option value="{{$_user->_id}}" {{if $_user->_id == $log->user_id}}selected="selected"{{/if}} >
              {{$_user}}
            </option>
          {{/foreach}}
        </select>

        <label>
          {{mb_label object=$log field=duration}}

          <select name="duration_operand">
            <option value=">="> >=</option>
            <option value=">"> ></option>
            <option value="<="> <=</option>
            <option value="<"> <</option>
            <option value="="> =</option>
          </select>

          {{mb_field object=$log field=duration canNull=true}}
        </label>
      </td>

      <td rowspan="5" class="greedyPane" id="resultPurgeLogs"></td>
    </tr>

    <tr>
      <th>{{tr}}CModule{{/tr}}</th>
      <td>
        <select name="filter_module" style="width: 20em;"
                onchange="$V(this.form.elements.start, 0); $V(this.form.elements.filter_action, '', false);
                  $V(this.form.elements.module_action_id, '', false); checkModule(this);">
          <option value="">&mdash; {{tr}}CModule.all{{/tr}}</option>

          {{foreach from=$modules item=_module}}
            <option value="{{$_module->mod_name}}">
              {{$_module}}
            </option>
          {{/foreach}}
        </select>

        <label>
          {{tr}}Action{{/tr}}

          <input type="text" class="autocomplete" name="filter_action" value="" size="30" disabled
                 placeholder="{{tr}}CModuleAction-All actions{{/tr}}" />

          <button type="button" class="erase notext compact"
                  onclick="$V(this.form.elements.filter_action, ''); $V(this.form.elements.module_action_id, '');">
            {{tr}}Reset{{/tr}}
          </button>
        </label>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=datetime_start}}</th>
      <td>
        {{mb_field object=$log field=_datetime_start_min form="Purge-Log" register=true}}
        &raquo;
        {{mb_field object=$log field=_datetime_start_max form="Purge-Log" register=true}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=datetime_end}}</th>
      <td>
        {{mb_field object=$log field=_datetime_end_min form="Purge-Log" register=true}}
        &raquo;
        {{mb_field object=$log field=_datetime_end_max form="Purge-Log" register=true}}
      </td>
    </tr>

    <tr>
      <th>{{tr}}common-Limit at each passage{{/tr}}</th>
      <td>
        <input type="text" name="purge_limit" value="100" size="3" />

        <label>
          {{tr}}common-Auto{{/tr}}
          <input type="checkbox" name="auto" id="clean_auto" />
        </label>

        <button type="button" class="info" onclick="LongRequestLog.purgeSome(this.form, true);">
          {{tr}}common-action-Count{{/tr}}
        </button>

        <button type="submit" class="trash" onclick="LongRequestLog.purgeSome(this.form);">
          {{tr}}common-action-Purge{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="list-logs"></div>
