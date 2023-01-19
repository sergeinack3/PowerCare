{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
    <form name="unlink-{{$linked_act->_guid}}" method="post" action="?" onsubmit="return false;">
        <input type="checkbox" name="unlink_act" title="{{tr}}CJfseAct-action-unlink_act{{/tr}}" checked="checked" {{if !$invoice->data_model->isPending() && !$invoice->data_model->isRejected()}}disabled="disabled"{{else}}onclick="MedicalActs.unlinkAct('{{$linked_act->_id}}');"{{/if}}>
    </form>
</td>
