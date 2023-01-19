{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshViewBris = function() {
    var form = getForm("filter_dates_bris");
    BrisDeGlace.listBrisDeGlaceByUser("{{$user->_id}}", $V(form.date_start), $V(form.date_end), "list_my_bris");
    BrisDeGlace.listBrisDeGlaceForUser("{{$user->_id}}", $V(form.date_start), $V(form.date_end), "list_bris_for_me");
  };

  Main.add(function() {
    var form = getForm("filter_dates_bris");
    Calendar.regField(form.date_start);
    Calendar.regField(form.date_end);
    refreshViewBris();
  });
</script>

<table class="main">
  <tr>
    <td style="text-align: center;" colspan="2">
      <form method="get" name="filter_dates_bris">
        <label>Début :
          <input type="hidden" name="date_start" value="{{$date_start}}" onchange="refreshViewBris();" />
        </label>,
        <label>Fin :
          <input type="hidden" name="date_end" value="{{$date_end}}" onchange="refreshViewBris();" />
        </label>
      </form>
    </td>
  </tr>
  <tr>
    <td style="width: 50%;">
      <table class="tbl">
        <tr>
          <th class="title" colspan="4">
            Mes bris de glace
          </th>
        </tr>
        <tr>
          <th>Nom</th>
          <th>Cible</th>
          <th>Raison</th>
          <th class="narrow">Date</th>
        </tr>
        <tbody id="list_my_bris"></tbody>
      </table>
    </td>
    <td>
      <table class="tbl">
        <tr>
          <th class="title" colspan="4">
            Bris de glace de mes dossiers
          </th>
        </tr>
        <tr>
          <th>Nom</th>
          <th>Cible</th>
          <th>Raison</th>
          <th class="narrow">Date</th>
        </tr>
        <tbody id="list_bris_for_me"></tbody>
      </table>
    </td>
  </tr>
</table>