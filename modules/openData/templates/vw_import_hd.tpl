{{*
 * @package Mediboard\openData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="import-hd" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-import-hd')">
  <input type="hidden" name="m" value="openData"/>
  <input type="hidden" name="dosql" value="do_import_hd"/>

  <table class="main form">
    <tr>
      <th><label for="annee">{{tr}}mod-openData-import-hd-annee{{/tr}}</label></th>
      <td>
        <select name="annee">
          {{foreach from=$annees item=_annee}}
            <option value="{{$_annee}}">{{$_annee}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="update">{{tr}}mod-openData-import-update{{/tr}}</label></th>
      <td><input type="checkbox" name="update" value="1" checked/></td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="import">{{tr}}Import{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import-hd"></div>
