{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpmsi script=DiagPMSI ajax=true}}

{{if $modal}}
  <table class="tbl">
    <tr>
      <th class="title">{{tr}}CRUM-Modify PMSI Diagnostics of the RUM{{/tr}}</th>
    </tr>
{{/if}}

<!--  Diagnostic Principal (OMS et à visée PMSI)-->
{{mb_include module=dPpmsi template=inc_diag_pmsi_dp}}
<!--  Diagnostic Relié (OMS et à visée PMSI)-->
{{mb_include module=dPpmsi template=inc_diag_pmsi_dr}}
<!--  Diagnostics Associés (OMS et à visée PMSI)-->
{{mb_include module=dPpmsi template=inc_diag_pmsi_das}}

{{if $modal}}
  </table>
{{/if}}
