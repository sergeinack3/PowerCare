{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=context script=ContextualIntegration}}

<script>
  Main.add(function(){
    ContextualIntegration.updateList();
  });
</script>

<table class="main me-align-auto">
  <tr>
    <td style="width: 50%;">
      <div id="list-integrations"></div>
    </td>
    <td id="edit-integration"></td>
  </tr>
</table>
