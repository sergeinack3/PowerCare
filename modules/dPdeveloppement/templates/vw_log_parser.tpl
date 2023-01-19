{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="parse-log-form" method="post" action="?m=dPdeveloppement&a=ajax_parse_log" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-parse-log')">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="a" value="ajax_parse_log"/>

  <table class="main form">
    <tr>
      <th>
        <h2>{{tr}}dPdeveloppement-parse log{{/tr}}</h2>
      </th>
      <td></td>
    </tr>

    <tr>
      <th>
        <label for="log_file" title="Logs à parser">{{tr}}File{{/tr}}</label>
      </th>
      <td>
        {{mb_include module=system template=inc_inline_upload lite=true multi=false}}

        <strong>{{tr}}common-OR{{/tr}}</strong>

        <label for="log_file_path" title="Logs à parser">{{tr}}dPdeveloppement-log-file{{/tr}}</label> :
        <input type="text" name="log_file_path" size="30"/>
      </td>
    </tr>

    <tr>
      <th>
        <label for="log_type" title="Type de logs">{{tr}}dPdeveloppement-log type{{/tr}}</label>
      </th>
      <td>
        <select name="log_type">
          {{foreach from=$allowed_types item=_type}}
            <option value="{{$_type}}">{{$_type}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>
        <label for="hits_per_sec" title="Hits par seconde">{{tr}}dPdeveloppement-log hits per sec{{/tr}}</label>
      </th>
      <td>
        <input type="checkbox" name="hits_per_sec" value="1"/>
      </td>
    </tr>

    <tr>
      <th>
        <label for="show_size">{{tr}}dPdeveloppement-log show size{{/tr}}</label>
      </th>
      <td>
        <input type="checkbox" name="show_size" value="1"/>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button id="import_button" type="submit" class="import">
          {{tr}}common-action-Parse{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="result-parse-log"></div>