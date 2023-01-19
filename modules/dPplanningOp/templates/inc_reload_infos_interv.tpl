{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=just_alert value=0}}
{{mb_default var=just_button value=0}}

<script>
  reloadIntervArea = function(operation_id, just_button) {
    var url = new Url("planningOp", "ajax_reload_infos_interv");
    url.addParam("operation_id", operation_id);
    url.addParam("just_button", just_button);
    url.requestUpdate("info_interv_area_"+operation_id);
  }
</script>

<span id="info_interv_area_{{$operation->_id}}">
  {{mb_include module=system template=inc_icon_alerts
    object=$operation
    callback="function() {reloadIntervArea(`$operation->_id`, $just_button)}"}}
  {{if !$just_alert}}
    {{if $operation->_can->read}}
      {{mb_include module=planningOp template=inc_button_infos_interv operation_id=$operation->_id callback="function() {reloadIntervArea(`$operation->_id`, $just_button)}"}}
    {{/if}}
    {{if !$just_button}}
      {{if $operation->libelle}}{{$operation->libelle}} &mdash;{{/if}}
      {{mb_label object=$operation field=cote}} :
      {{if !($conf.dPplanningOp.COperation.verif_cote && !$operation->cote_bloc) || ($operation->cote != "droit" && $operation->cote != "gauche")}}
        {{mb_value object=$operation field=cote}}
      {{else}}
        Non validé en salle
      {{/if}}
    {{/if}}
  {{/if}}
</span>