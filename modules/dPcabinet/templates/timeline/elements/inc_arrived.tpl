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
        {{tr}}Admission{{/tr}}
      </span>
    </td>
  </tr>

  {{foreach from=$list item=item name=arrived}}
    <tr>
      <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
        <br>
        {{$item->entree|date_format:$conf.datetime}}
        <br>
        {{mb_include module=system template=inc_interval_datetime from=$item->entree to=$item->sortie}}
      </td>
      <td>
        {{if $item->libelle}}
          <span class="timeline_description">{{mb_value object=$item field=libelle}}</span>
        {{/if}}
        <span
          class="timeline_description"><strong>{{tr}}Admission{{/tr}}:</strong> {{if $item->entree_reelle}}{{tr}}CAffectation-effectue{{/tr}}{{else}}{{tr}}common-Not done{{/tr}}{{/if}}</span>
        {{if $item->mode_entree}}
          <span
            class="timeline_description"><strong>{{tr}}CSejour-mode_entree{{/tr}} :</strong> {{mb_value object=$item field=mode_entree}}</span>
        {{elseif $item->mode_entree_id}}
          <span class="timeline_description">
              <strong>{{tr}}CModeEntreeSejour-mode{{/tr}} :</strong> {{mb_value object=$item->_ref_mode_entree field=mode}}
              <strong>{{tr}}CModeEntreeSejour-libelle{{/tr}} :</strong> {{mb_value object=$item->_ref_mode_entree field=libelle}}
            </span>
        {{/if}}
      </td>
    </tr>
    {{if !$smarty.foreach.arrived.last}}
      <tr>
        <td colspan="2">
          <hr class="item_separator"/>
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>