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
        {{tr}}CSejour{{/tr}}
      </span>
    </td>
  </tr>

  {{foreach from=$list item=item name=stays}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item}}
        </span>

        {{if $item->presence_confidentielle}}
          {{mb_include module=planningOp template=inc_badge_sejour_conf}}
        {{/if}}

        <br>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_praticien}}
      </td>
      <td>
        {{$item->libelle}}
      </td>
    </tr>
    {{if !$smarty.foreach.stays.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
