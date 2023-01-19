{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=nb_ops value=$sejour->_ref_operations|@count}}

<form name="cancelInterventions" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {
        Control.Modal.close();
        Admissions.validerEntree('{{$sejour->_id}}', null, function() { document.location.reload(); });
        } );">
  <input type="hidden" name="planningOp" />
  {{mb_class class="COperation"}}
  <input type="hidden" name="operation_ids" value="{{"-"|implode:$operations_ids}}" />
  <input type="hidden" name="annulee" value="1" />
</form>

<div class="small-info">
  Souhaitez-vous annuler {{if $nb_ops > 1}}les {{else}}l'{{/if}}intervention{{if $nb_ops > 1}}s{{/if}}
  suivante{{if $nb_ops > 1}}s{{/if}} pour admettre la patiente ?

  <ul>
    {{foreach from=$sejour->_ref_operations item=_op}}
      <li>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">{{$_op}}</span>
      </li>
    {{/foreach}}
  </ul>

  <br />

  <div style="text-align: center;">
    <button type="button" class="tick oneclick" onclick="getForm('cancelInterventions').onsubmit();">{{tr}}Yes{{/tr}}</button>
    <button type="button" class="cancel " onclick="Control.Modal.close();">{{tr}}No{{/tr}}</button>
  </div>
</div>