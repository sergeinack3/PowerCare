{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=personnel script=personnel}}
{{mb_script module=system    script=object_selector}}

<script>
  Main.add(Personnel.refreshList);
</script>

<div class="me-margin-top-4">
  <button type="button" class="new" onclick="Personnel.edit(0);">
    {{tr}}CPersonnel-title-create{{/tr}}
  </button>

  <button type="button" class="new" onclick="Personnel.editMultiple();">
    {{tr}}CPersonnel-title-create-multiple{{/tr}}
  </button>
</div>

<form name="filterFrm" method="get" onsubmit="return Personnel.refreshList();">
  <input type="hidden" name="m" value="personnel" />
  <input type="hidden" name="a" value="ajax_list_personnel" />

  <table class="form">
    <tr>
      <th colspan="4" class="title">Recherche d'un membre du personnel</th>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_user_last_name"}}</th>
      <td>{{mb_field object=$filter field="_user_last_name"}}</td>
      <th>{{mb_label object=$filter field="_user_first_name"}}</th>
      <td>{{mb_field object=$filter field="_user_first_name"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="emplacement"}}</th>
      <td>{{mb_field object=$filter emptyLabel="All" canNull=true field="emplacement"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button class="search">{{tr}}Show{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="area_personnel" class="me-padding-0"></div>