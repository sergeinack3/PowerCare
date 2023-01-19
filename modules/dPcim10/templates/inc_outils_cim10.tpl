{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listDPforbidden = function (page) {
    new Url("cim10", "ajax_sejours_dp_forbidden")
      .addParam('page', page)
      .requestModal("60%", "70%");
  };
</script>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td colspan="2">
      <button type="button" class="search" onclick="listDPforbidden(0);">{{tr}}CCodeCIM10-action-Search for stays with a prohibited DP{{/tr}}</button>
    </td>
  </tr>
</table>
