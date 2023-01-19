{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == 'assignment_begin'}}
  <table class="main layout">
    {{foreach from=$list item=item name=movements}}
      <tr>
        <td>
          <span class="type_item circled">
            {{if $item->_class == 'CAffectation'}}{{tr}}CAffectation-Begin{{/tr}}{{else}}{{tr}}Admission{{/tr}}{{/if}}
          </span>
        </td>
      </tr>
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
        <td style="width: 75%">
          {{if $item->_class == 'CAffectation'}}
            {{$item}}
          {{else}}
            {{if $item->libelle}}
              <span class="timeline_description">{{mb_value object=$item field=libelle}}</span>
            {{/if}}
            <span class="timeline_description"><strong>{{tr}}Admission{{/tr}}:</strong> {{if $item->entree_reelle}}{{tr}}CAffectation-effectue{{/tr}}{{else}}{{tr}}common-Not done{{/tr}}{{/if}}</span>
            {{if $item->mode_entree}}
              <span class="timeline_description"><strong>{{tr}}CSejour-mode_entree{{/tr}}:</strong> {{mb_value object=$item field=mode_entree}}</span>
            {{elseif $item->mode_entree_id}}
              <span class="timeline_description">
                <strong>{{tr}}CModeEntreeSejour-mode{{/tr}}:</strong> {{mb_value object=$item->_ref_mode_entree field=mode}}
                <strong>{{tr}}CModeEntreeSejour-libelle{{/tr}}:</strong> {{mb_value object=$item->_ref_mode_entree field=libelle}}
              </span>
            {{/if}}
          {{/if}}
        </td>
      </tr>
      {{if !$smarty.foreach.movements.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "movements"}}
  <table class="main layout">
    <tr>
      <td>
          <span class="type_item circled">
            {{tr}}CBrancardage{{/tr}}
          </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=mov_begin}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br/>
          {{$item->_ref_brancardage->date|date_format:$conf.date}}
          <br/>
          {{if $item->_ref_personnel->_id}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_personnel->_ref_user}}
            <br/>
          {{/if}}
          {{if $item->_ref_origine->_id}}
            <strong>{{tr}}CBrancardage-origine{{/tr}} :</strong> <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_origine->_guid}}');">{{$item->_ref_origine}}</span>
          {{/if}}
          {{if $item->_ref_destination->_id}}
            <strong>{{tr}}CBrancardage-destination{{/tr}} :</strong> <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_destination->_guid}}');">{{$item->_ref_destination}}</span>
          {{/if}}
        </td>
        <td>
          {{if $item->prise_en_charge}}
            <span class="timeline_description"><strong>{{tr}}CBrancardage-_ref_prise_en_charge{{/tr}} :</strong> {{mb_value object=$item field=prise_en_charge}}</span>
          {{/if}}
          {{if $item->depart}}
            <span class="timeline_description"><strong>{{tr}}CBrancardage-_ref_debut{{/tr}} :</strong> {{mb_value object=$item field=depart}}</span>
          {{/if}}
          {{if $item->arrivee}}
            <span class="timeline_description"><strong>{{tr}}CBrancardage-_ref_arrivee{{/tr}} :</strong> {{mb_value object=$item field=arrivee}}</span>
          {{/if}}
        </td>
      </tr>
      {{if !$smarty.foreach.mov_begin.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "arrived"}}
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
          <span class="timeline_description"><strong>{{tr}}Admission{{/tr}}:</strong> {{if $item->entree_reelle}}{{tr}}CAffectation-effectue{{/tr}}{{else}}{{tr}}common-Not done{{/tr}}{{/if}}</span>
          {{if $item->mode_entree}}
            <span class="timeline_description"><strong>{{tr}}CSejour-mode_entree{{/tr}} :</strong> {{mb_value object=$item field=mode_entree}}</span>
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
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "left"}}
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
{{/if}}

{{if $type == 'assignment_end'}}
  <table class="main layout">
    {{foreach from=$list item=item name=mov_end}}
      <tr>
        <td>
          <span class="type_item circled">
            {{if $item->_class == 'CAffectation'}}{{tr}}CAffectation-End{{/tr}}{{else}}{{tr}}CAffectation-sortie{{/tr}}{{/if}}
          </span>
        </td>
      </tr>
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
        <td>
          {{if $item->_class == 'CAffectation'}}
            {{$item}}
          {{else}}
            {{if $item->libelle}}
              <span class="timeline_description">{{mb_value object=$item field=libelle}}</span>
            {{/if}}
            <span class="timeline_description"><strong>{{tr}}CAffectation-sortie{{/tr}} :</strong> {{if $item->entree_reelle}}{{tr}}CAffectation-effectue{{/tr}}{{else}}{{tr}}common-Not done{{/tr}}{{/if}}</span>
            {{if $item->mode_sortie}}
              <span class="timeline_description"><strong>{{tr}}CSejour-mode_sortie{{/tr}} :</strong> {{mb_value object=$item field=mode_sortie}}</span>
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
          {{/if}}
        </td>
      </tr>
      {{if !$smarty.foreach.mov_end.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}
