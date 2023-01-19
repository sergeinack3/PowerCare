{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=exchange_source ajax=true}}

<script>
  Main.add(ExchangeSource.showDirectory.curry('{{$source_guid}}'));
</script>

<table class="layout" style="width: 100%">
  <tr>
    <td style="vertical-align: top; width: 25%">
      <div id="listDirectory">
      </div>
    </td>
    <td style="vertical-align: top; width: 75%">
      <div id="listFiles">
      </div>
    </td>
  </tr>
</table>

