{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=import script=import_campaign}}

<script>
  Main.add(function () {
    var form = getForm("search-import-campaign");

    Calendar.regField(form._creation_date_min);
    Calendar.regField(form._creation_date_max);
    Calendar.regField(form._closing_date_min);
    Calendar.regField(form._closing_date_max);

    form.onsubmit();
  });
</script>

<form name="search-import-campaign" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-search-import-campaign')">
  <input type="hidden" name="m" value="import"/>
  <input type="hidden" name="a" value="ajax_search_import_campaign"/>

  <table class="main form">
    <tr>
      <th>{{mb_title class=CImportCampaign field=name}}</th>
      <td><input type="text" name="name"</td>

      <th>{{mb_title class=CImportCampaign field=creation_date}}</th>
      <td>
        <input type="hidden" name="_creation_date_min"> >
        <input type="hidden" name="_creation_date_max">
      </td>
    </tr>

    <tr>
      <th></th>
      <td></td>

      <th>{{mb_title class=CImportCampaign field=closing_date}}</th>
      <td>
        <input type="hidden" name="_closing_date_min"> >
        <input type="hidden" name="_closing_date_max">
      </td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button type="button" class="new" onclick="ImportCampaign.showCreateCampaign()">{{tr}}CImportCampagn-action-Create{{/tr}}</button>
        <button style="align-self: center;" type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-search-import-campaign"></div>