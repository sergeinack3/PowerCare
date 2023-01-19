{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}
{{mb_default var=print value=0}}

{{if !$_materiel_operatoire->_qte_consommee}}
  {{mb_return}}
{{/if}}

<div class="me-margin-5">
  {{if !$print}}
    <button type="button" class="down notext" style="float: right;" onclick="ProtocoleOp.toggleListConsommations(this);"></button>
  {{/if}}

  <strong>
    {{$_materiel_operatoire->_qte_consommee}} {{tr}}CMaterielOperatoire-Qte consommee{{if $_materiel_operatoire->_qte_consommee > 1}}|pl{{/if}}{{/tr}}
  </strong>

  <div {{if !$print}}style="display: none;"{{/if}}>
    {{foreach from=$_materiel_operatoire->_ref_consommations item=_consommation}}
      <div class="me-margin-5">
        {{if !$readonly}}
          <button type="button" class="trash notext"
                  onclick="ProtocoleOp.delConsommationMateriel('{{$_consommation->_id}}', '{{$_materiel_operatoire->_id}}');">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}

        {{assign var=conso_qte      value=$_consommation->qte_consommee}}
        {{assign var=conso_datetime value=$_consommation->datetime|date_format:$conf.datetime}}
        {{assign var=conso_user     value=$_consommation->_ref_user->_view}}

        {{tr var1=$conso_qte var2=$conso_datetime var3=$conso_user}}CConsommationMateriel-Qte consommee detail{{/tr}}

        {{if $_consommation->lot_id}}
          {{assign var=lot value=$_consommation->_ref_lot}}
          <div class="compact">
            [{{$lot->code}}]

            {{$lot->libelle}}

            {{if $lot->lapsing_date}}
              &ndash; {{mb_value object=$lot field=lapsing_date}}
            {{/if}}

            {{if $lot->_ref_order_item->_ref_reference->societe_id}}
              &ndash; {{mb_value object=$lot->_ref_order_item->_ref_reference field=societe_id}}
            {{/if}}
          </div>
        {{/if}}
      </div>
    {{/foreach}}
  </div>
</div>
