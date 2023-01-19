{{*
 * @package Mediboard\openData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="import-hd" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-import-hd-finess')">
  <input type="hidden" name="m" value="openData"/>
  <input type="hidden" name="dosql" value="do_import_hd_finess"/>

  <table class="main form">

    <tr>
      <th><label for="geolocalisation">{{tr}}mod-openData-import-geolocalisation{{/tr}}</label></th>
      <td><input type="checkbox" name="geolocalisation" value="1" checked/></td>
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

<div id="result-import-hd-finess"></div>
