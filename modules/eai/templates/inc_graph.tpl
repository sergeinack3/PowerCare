{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="layout">
  <tr>
    <td>
      <div id="graph" style="width: 600px; height: 400px; margin-right: 0; margin-left: auto;"></div>
    </td>
    <td id="legend" style="width: 25%;"></td>
  </tr>
</table>

<script>
  var options = {{$options|@json}};
  options.legend.container = $('legend');
  Flotr.draw($('graph'), {{$series|@json}}, options);
</script>