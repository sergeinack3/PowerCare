{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$offline}}
  <script>
    Main.add(function() {
      window.print();
    })
  </script>
{{/if}}

{{foreach from=$protocoles_op item=_protocole_op name=protocole_op}}
  <div {{if !$smarty.foreach.protocole_op.last}}style="page-break-after: always;"{{/if}}>
    {{mb_include module=planningOp template=inc_edit_protocole_op protocole_op=$_protocole_op print=1}}
  </div>
{{/foreach}}
