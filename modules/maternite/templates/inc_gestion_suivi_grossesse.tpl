{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  callbackOpenConsult = function (consult_id) {
    Tdb.editConsult(consult_id, refreshListeSuivis);
  };

  refreshListeSuivis = function () {
    var url = new Url("maternite", "ajax_vw_liste_suivis_grossesse");
    url.addParam("grossesse_id", '{{$grossesse->_id}}');
    url.addParam("print", '{{$print}}');
    url.requestUpdate('listeSuivisGrossesse');
  };

  Main.add(function () {
    {{if !$print}}
      $("listeSuivisGrossesse").fixedTableHeaders();
    {{/if}}

    refreshListeSuivis();
  });
</script>

<table class="main">
  <tr>
    <th>
      {{mb_include module=cabinet template=inc_button_consult_immediate
      patient_id=$grossesse->parturiente_id grossesse_id=$grossesse->_id callback="callbackOpenConsult"}}
    </th>
  </tr>
</table>

<div id="listeSuivisGrossesse" class="x-y-scroll">
</div>
