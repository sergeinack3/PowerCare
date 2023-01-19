{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      {{mb_include module=hprim21 template=inc_list_patient}}
    </td>

    {{if $patient->_id}}
    <td class="halfPane" id="vwPatient">
      {{mb_include module=hprim21 template=inc_vw_patient}}
    </td>
    {{/if}}
  </tr>
</table>