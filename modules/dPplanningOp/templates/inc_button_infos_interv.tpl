
{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=multi_label value="dPplanningOp COperation multiple_label"|gconf}}
{{mb_default var=callback value=Prototype.emptyFunction}}

<script>
  editInfoInterv = function(operation_id, callback) {
    var url = new Url('planningOp', 'ajax_edit_infos_interv');
    url.addParam('operation_id', operation_id);
    url.requestModal(360, 360, {onClose: callback});
  }
</script>

<button type="button" class="edit notext me-tertiary me-dark not-printable" onclick="editInfoInterv('{{$operation->_id}}', {{$callback}});"></button>

{{if $multi_label}}
  <span class="countertip" style="margin-top:2px;">
    {{$operation->_ref_liaison_libelles|@count}}
  </span>&nbsp;
{{/if}}
