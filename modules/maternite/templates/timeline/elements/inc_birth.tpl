{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td>
      <span class="type_item circled">
        {{tr}}CNaissance{{/tr}}
      </span>
    </td>
  </tr>

  {{foreach from=$list item=item name=birth}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item->date_time|date_format:$conf.datetime}}
        </span>
        -
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_sejour_enfant->_ref_patient->_guid}}');">
          {{$item->_ref_sejour_enfant->_ref_patient}}
        </span>
        {{if $item->_ref_nouveau_ne_constantes->poids}}
          -
          <strong>{{tr}}CConstantesMedicales-poids{{/tr}}:</strong> {{$item->_ref_nouveau_ne_constantes->poids}}kg
        {{/if}}
        <br>
        {{if $item->_ref_sejour_enfant->_ref_praticien && $item->_ref_sejour_enfant->_ref_praticien->_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_sejour_enfant->_ref_praticien}}
        {{/if}}
      </td>
    </tr>
    {{if !$smarty.foreach.birth.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>