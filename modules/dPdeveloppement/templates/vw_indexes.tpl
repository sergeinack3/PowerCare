{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}CIndexChecker-msg-Info1{{/tr}}

  <br/>

  {{tr}}CIndexChecker-msg-Info2{{/tr}}

  <br/>

  {{tr}}CIndexChecker-msg-Info3{{/tr}}
  <ul>
    <li>{{tr}}CIndexChecker-msg-Info3-Option1{{/tr}}</li>
    <li>{{tr}}CIndexChecker-msg-Info3-Option2{{/tr}}</li>
  </ul>
</div>

<form name="search-indexes" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-search-indexes')">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="a" value="ajax_vw_indexes"/>

  <table class="main form">
    <tr>
      <th>{{tr}}CIndexChecker-key_type{{/tr}}</th>
      <td>
        {{foreach from=$key_types item=_key_type}}
          <label>
            <input type="radio" name="key_type" value="{{$_key_type}}" {{if $_key_type == 'index'}}checked{{/if}}/>
            {{tr}}CIndexChecker.{{$_key_type}}{{/tr}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>{{tr}}CIndexChecker-error_type{{/tr}}</th>
      <td>
        {{foreach from=$error_types item=_error_type}}
          <label>
            <input type="radio" name="error_type" value="{{$_error_type}}" {{if $_error_type == 'all'}}checked{{/if}}/>
            {{tr}}CIndexChecker.{{$_error_type}}{{/tr}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>{{tr}}CIndexChecker-show_all_fields{{/tr}}</th>
      <td>
          <label>
            <input type="radio" name="show_all_fields" value="1"/>
            {{tr}}Yes{{/tr}}
          </label>

          <label>
            <input type="radio" name="show_all_fields" value="0" checked/>
            {{tr}}No{{/tr}}
          </label>
      </td>
    </tr>

    <tr>
      <th>{{tr}}Module{{/tr}}</th>
      <td>
        <select name="index_module">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$module_list key=_trad item=_module}}
            <option value="{{$_module->mod_name}}" {{if $module == $_module->mod_name}}selected{{/if}}>
              {{$_trad}} ({{$_module->mod_name}})
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-search-indexes"></div>
