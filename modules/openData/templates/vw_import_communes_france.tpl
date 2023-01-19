{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="width: 33%; float: left">
  <form name="import-communes-france" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-import-communes-france')">
    <input type="hidden" name="m" value="openData"/>
    <input type="hidden" name="a" value="ajax_import_communes"/>
    <input type="hidden" name="pays" value="france"/>

    <table class="main tbl">
      <tr>
        <th>{{tr}}mod-openData-import-options{{/tr}}</th>
      </tr>
    </table>
    <table class="main form">
      <tr>
        <th>{{tr}}mod-openData-version{{/tr}}</th>
        <td>
          <select name="version">
            {{foreach from=$versions_france item=_version}}
              <option value="{{$_version}}">{{tr}}mod-openData-import-communes-{{$_version}}{{/tr}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="start">{{tr}}mod-openData-start-at{{/tr}}</label></th>
        <td><input type="text" name="start" value="0" size="5"></td>
      </tr>

      <tr>
        <th><label for="step">{{tr}}mod-openData-step{{/tr}}</label></th>
        <td><input type="text" name="step" value="100" size="5"></td>
      </tr>

      <tr>
        <th><label for="zip">{{tr}}mod-openData-zip{{/tr}}</label></th>
        <td><input type="checkbox" name="zip" value="1" checked></td>
      </tr>

      <tr>
        <th><label for="continue">{{tr}}mod-openData-auto{{/tr}}</label></th>
        <td><input type="checkbox" name="continue" value="1"></td>
      </tr>

      <tr>
        <th><label for="import_all">{{tr}}mod-openData-import-all{{/tr}}</label></th>
        <td><input type="checkbox" name="import_all" value="1"></td>
      </tr>

      <tr>
        <th><label for="update">{{tr}}mod-openData-import-update{{/tr}}</label></th>
        <td><input type="checkbox" name="update" value="1"></td>
      </tr>

      <tr>
        <th>
          <button type="submit" class="change">{{tr}}Import{{/tr}}</button>
        </th>
        <td></td>
      </tr>
    </table>
  </form>
</div>
<div id="result-import-communes-france"></div>