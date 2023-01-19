{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=DatabaseExplorer}}

<style>
  .show_hidden .hidden:not(.empty) {
    display: table-row !important;
  }
  .show_empty .empty {
    display: table-row !important;
  }

  #table-data.fixed-width .db-data td {
    max-width: 15em;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .db-table .important_1 {
    background-color: lightsalmon !important;
  }
</style>

<script>
  Main.add(function(){
    var input = getForm('select-database').dsn;
    DatabaseExplorer.initDbAutocomplete(input);
    DatabaseExplorer.loadDbTables('{{$dsn}}');
  });
</script>

<div>
  <table class="layout">
    <tr>
      <td class="narrow" style="width: 220px; border-right: 1px solid #999 !important; vertical-align: top;">
        <form name="select-database" method="get">
          <input type="text" name="dsn" value="" size="50" />
        </form>

        <div id="tables"></div>
      </td>
      <td id="table-data" style="vertical-align: top; overflow: scroll;"></td>
    </tr>
  </table>
</div>