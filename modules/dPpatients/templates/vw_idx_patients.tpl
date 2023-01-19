{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPcompteRendu" script="document"}}
{{mb_script module="patients" script="patient"}}

<script>
  markAsSelected = function (anchor) {
    if (anchor) {
      $(anchor).up('tr').addUniqueClassName('selected');
    }
  };
</script>

<table class="main me-idx-patient">
  <tr>
    <td class="halfPane">
      {{mb_include template="inc_list_patient"}}
    </td>
    {{if !$board}}
      <td class="halfPane" id="vwPatient">
        <div class="small-info me-align-auto">
          Veuillez sélectionner un patient sur la gauche pour pouvoir le visualiser
        </div>
      </td>
    {{/if}}
  </tr>
</table>