{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name=vitals}}
    <tr>
      <td class="halfPane">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          <span class="type_item circled">
            {{tr}}CConstantesMedicales{{/tr}}
          </span>
          &mdash;
          {{mb_value object=$item field=datetime}}
        </span>
        <br />
        {{if $item->_ref_user}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_user}}
        {{/if}}
      </td>
      <td>
        {{foreach from=$item->_valued_cst key=name item=value}}
          <span class="timeline_description">
            {{tr}}CConstantesMedicales-{{$name}}{{/tr}} : {{$value.value}} {{$value.description.unit}}
          </span>
        {{/foreach}}
      </td>
    </tr>
    {{if !$smarty.foreach.vitals.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
