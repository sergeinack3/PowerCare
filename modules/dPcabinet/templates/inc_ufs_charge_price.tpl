{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=create_consult_sejour value="dPcabinet CConsultation create_consult_sejour"|gconf}}

{{if $create_consult_sejour}}
  {{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}
  {{assign var=required_uf_med   value="dPplanningOp CSejour required_uf_med"|gconf}}
  {{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}

  {{assign var=ufs value='Ox\Mediboard\Hospi\CUniteFonctionnelle::getUFs'|static_call:null}}

  {{if $required_uf_soins != "no"}}
    <!-- Selection de l'unité de soins -->
    <tr>
      <th>{{mb_label class=CConsultation field="_uf_soins_id"}}</th>
      <td>
        <select name="_uf_soins_id" class="ref {{if $required_uf_soins == "obl"}}notNull{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.soins item=_uf}}
            <option value="{{$_uf->_id}}">
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}
  {{if $required_uf_med != "no"}}
    <!-- Selection de l'unité de soins -->
    <tr>
      <th>{{mb_label class=CConsultation field="_uf_medicale_id"}}</th>
      <td>
        <select name="_uf_medicale_id" class="ref {{if $required_uf_med == "obl"}}notNull{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.medicale item=_uf}}
            <option value="{{$_uf->_id}}">
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}

  {{if $use_charge_price_indicator != "no"}}
    <tr>
      <th>
        {{mb_label class=CConsultation field="_charge_id"}}
      </th>
      <td colspan="3">
        <select class="ref{{if $use_charge_price_indicator == "obl"}} notNull{{/if}}" name="_charge_id">
          <option value="">&ndash; {{tr}}Choose{{/tr}}</option>
          {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:"consult" item=_cpi name=cpi}}
            <option value="{{$_cpi->_id}}">
              {{$_cpi|truncate:50:"...":false}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}
{{/if}}