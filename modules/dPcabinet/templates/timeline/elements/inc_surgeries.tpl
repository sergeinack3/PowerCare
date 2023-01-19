{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td>
      <span class="type_item circled">
        {{tr}}COperation{{/tr}}
      </span>
    </td>
    <td></td>
  </tr>

  {{foreach from=$list item=item name=surgery}}
    <tr>
      <td class="halfPane">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item}} [{{mb_value object=$item field=_status}}]
        </span>
        {{mb_value object=$item field=_datetime}}
        <br>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_chir}}
      </td>
      <td>
        {{if $item->libelle}}
          {{$item->libelle}}
        {{else}}
          {{foreach from=$item->_ext_codes_ccam_princ item=_code}}
            {{$_code->code}}
          {{/foreach}}
        {{/if}}
        <br>
      </td>
    </tr>
    {{if !$smarty.foreach.surgery.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>