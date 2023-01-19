{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=DatabaseExplorer}}

<script>
Main.add(function() {
  var form = getForm('display-std-tables');
  form.onsubmit();
});
</script>

<form name="display-std-tables" method="get" onsubmit="return onSubmitFormAjax(this, null, 'tables')">
  <input type="hidden" name="m" value="importTools"/>
  <input type="hidden" name="a" value="ajax_vw_tables"/>

  {{if $slave_exists}}
    <strong>Base de données :</strong>
    <label for="select-dsn-std">Std</label>
    <input id="select-dsn-std" type="radio" name="dsn" value="std" checked onchange="this.form.onsubmit();"/>
    <label for="select-dsn-slave">Slave</label>
    <input id="select-dsn-slave" type="radio" name="dsn" value="slave" onchange="this.form.onsubmit();"/>

    {{else}}

    <input type="hidden" name="dsn" value="std"/>
  {{/if}}

</form>

<table class="layout">
  <tr>
    <td class="narrow" style="width: 220px; border-right: 1px solid #999 !important; vertical-align: top;">
      <div id="tables"></div>
      <div id="show-tables"></div>
    </td>
    <td id="table-data" style="vertical-align: top; overflow: scroll;"></td>
  </tr>
</table>