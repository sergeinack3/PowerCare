{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
    <form name="link-{{$act->_guid}}" method="post" action="?" onsubmit="return false;">
        <input type="checkbox" name="link_act" title="{{tr}}CJfseAct-action-link_act{{/tr}}" {{if !$invoice->data_model->isPending()}}disabled="disabled"{{else}}onclick="MedicalActs.linkAct('{{$invoice->id}}', '{{$act->_class}}', '{{$act->_id}}');"{{/if}}>
    </form>
</td>
