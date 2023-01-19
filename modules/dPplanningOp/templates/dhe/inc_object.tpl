{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    {{if $object->_class == 'COperation'}}
      DHE.operation = new Operation2(true);
      {{if $object->annulee}}
        DHE.operation.displayCancelFlag();
      {{/if}}
    {{else}}
      DHE.consult = new Consultation(true);
      {{if $object->annule}}
        DHE.consult.displayCancelFlag();
      {{/if}}
    {{/if}}
  });
</script>

{{if $object->_class == 'COperation'}}
  {{mb_include module=planningOp template=dhe/inc_operation_summary operation=$object}}
  {{mb_include module=planningOp template=dhe/inc_operation_edit operation=$object}}
{{else}}
  {{mb_include module=planningOp template=dhe/inc_consultation_summary consult=$object}}
  {{mb_include module=planningOp template=dhe/inc_consultation_edit consult=$object}}
{{/if}}