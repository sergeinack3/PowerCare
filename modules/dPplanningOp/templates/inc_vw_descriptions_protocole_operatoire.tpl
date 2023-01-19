{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      <div class="small-info">
        {{mb_label object=$protocole_operatoire field=description_equipement_salle}} :
        {{mb_value object=$protocole_operatoire field=description_equipement_salle}}
      </div>
    </td>
    <td>
      <div class="small-info">
        {{mb_label object=$protocole_operatoire field=description_installation_patient}} :
        {{mb_value object=$protocole_operatoire field=description_installation_patient}}
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <div class="small-info">
        {{mb_label object=$protocole_operatoire field=description_preparation_patient}} :
        {{mb_value object=$protocole_operatoire field=description_preparation_patient}}
      </div>
    </td>
    <td>
      <div class="small-info">
        {{mb_label object=$protocole_operatoire field=description_instrumentation}} :
        {{mb_value object=$protocole_operatoire field=description_instrumentation}}
      </div>
    </td>
  </tr>
</table>