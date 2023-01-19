{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=vw_affectations ajax=1}}
{{mb_script module=planningOp script=prestations ajax=1}}

{{if "dPImeds"|module_active && "dPhospi vue_tableau show_labo_results"|gconf}}
  <script>
    Main.add(function () {
      ImedsResultsWatcher.loadResults();
    });
  </script>
{{/if}}

<table class="main me-padding-top-4 me-w100">
  <tr>
    <td id="tableauAffectations">
      {{mb_include module=hospi template="inc_tableau_affectations_lits"}}
    </td>
    <td class="me-padding-left-4 narrow">
      {{mb_include module=hospi template="inc_patients_a_placer"}}
    </td>
  </tr>
</table>