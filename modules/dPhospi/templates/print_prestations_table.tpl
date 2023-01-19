{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="width: 100%;">
  <th>{{tr}}common-Date{{/tr}}</th>
  <th>{{tr}}CItemLiaison-souhait{{/tr}}</th>
  <th>{{tr}}CItemLiaison-realise{{/tr}}</th>
  <th style="width: 1%;">{{tr}}CItemLiaison-quantite{{/tr}}</th>

  {{foreach from=$dates key=liaison_id item=_intervalle}}
    {{assign var=liaison value=$liaisons.$liaison_id}}
    {{assign var=prestation value=$liaison->_ref_prestation}}
    {{assign var=item_souhait value=$liaison->_ref_item}}
    {{assign var=item_realise value=$liaison->_ref_item_realise}}
    {{assign var=sous_item value=$liaison->_ref_sous_item}}

    {{if $item_souhait->_id || $item_realise->_id}}
      <tr>
        <td>
          {{mb_include module=system template=inc_interval_date from=$_intervalle.debut to=$_intervalle.fin}}
        </td>
        <td>
          {{if $item_souhait->_id}}
            <strong>
              {{if $sous_item->_id}}
                {{$sous_item}}
              {{else}}
                {{$item_souhait}}
              {{/if}}
            </strong>
          {{/if}}
        </td>
        <td>
          {{if $item_realise->_id}}
            <strong>
              {{if $item_realise}}
                {{if $sous_item->_id}}
                  {{$sous_item}}
                {{else}}
                  {{$item_realise}}
                {{/if}}
              {{/if}}
            </strong>
          {{/if}}
        </td>
        <td style="text-align: right;">
          {{$_intervalle.qte}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>