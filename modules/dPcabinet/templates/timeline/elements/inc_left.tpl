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
        {{tr}}CAffectation-sortie{{/tr}}
      </span>
    </td>
  </tr>

  {{foreach from=$list item=item name=left}}
    <tr>
      <td style="width: 50%;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item}}
        </span>
        <br/>
        {{$item->sortie|date_format:$conf.datetime}}
        <br/>
        {{mb_include module=system template=inc_interval_datetime from=$item->entree to=$item->sortie}}
      </td>
      <td style="width: 75%">
        {{if $item->libelle}}
          <span class="timeline_description">{{mb_value object=$item field=libelle}}</span>
        {{/if}}
        <span class="timeline_description"><strong>{{tr}}CAffectation-sortie{{/tr}}:</strong> {{if $item->entree_reelle}}{{tr}}CAffectation-effectue{{/tr}}{{else}}{{tr}}common-Not done{{/tr}}{{/if}}</span>
        {{if $item->mode_sortie}}
          <span class="timeline_description"><strong>{{tr}}CSejour-mode_sortie{{/tr}}:</strong> {{mb_value object=$item field=mode_sortie}}</span>
        {{elseif $item->mode_sortie_id}}
          <span class="timeline_description">
              <strong>{{tr}}CModeEntreeSejour-mode{{/tr}} :</strong> {{mb_value object=$item->_ref_mode_sortie field=mode}}
              <strong>{{tr}}CModeEntreeSejour-libelle{{/tr}} :</strong> {{mb_value object=$item->_ref_mode_sortie field=libelle}}
            </span>
        {{/if}}
        {{if $item->transport_sortie}}
          <span class="timeline_description">
              <strong>{{tr}}CSejour-transport_sortie{{/tr}} :</strong> {{mb_value object=$item field=transport_sortie}}
            </span>
        {{/if}}
        {{if $item->destination}}
          <span class="timeline_description">
              <strong>{{tr}}CSejour-destination{{/tr}} :</strong> {{mb_value object=$item field=destination}}
            </span>
        {{/if}}
        {{if $item->commentaires_sortie}}
          <span class="timeline_description">
              <strong>{{tr}}CSejour-commentaires_sortie{{/tr}} :</strong> {{mb_value object=$item field=commentaires_sortie}}
            </span>
        {{/if}}
      </td>
    </tr>
    {{if !$smarty.foreach.left.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>