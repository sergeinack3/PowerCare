{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="list_sejours" class="dhe_flag" onmouseover="ObjectTooltip.createDOM(this, 'list_sejours_tooltip');" style="position: absolute; padding: 2px; color: black; background-color: #eee; border: 1px solid #abe; display: none;" data-count="{{$sejours|@count}}">
  {{$sejours|@count}} séjours existants
</span>

<span id="collision" class="dhe_flag dhe_flag_important" onmouseover="ObjectTooltip.createDOM(this, 'collisions_tooltip');" style="position: absolute; padding: 2px; font-weight: bold; display: none;" data-count="{{$collisions|@count}}">
  Collision
</span>

<div id="list_sejours_tooltip" style="display: none;{{if $sejours|@count > 10}} height: 150px;{{/if}} overflow-x: hidden; overflow-y: auto;">
  <table class="layout">
    {{foreach from=$sejours item=_sejour}}
      <tr>
        <td class="narrow{{if $_sejour->annule}} cancelled{{/if}}" style="vertical-align: middle;">
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=true}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
            {{$_sejour->_shortview}}
          </span>
        </td>
        <td class="narrow{{if $_sejour->annule}} cancelled{{/if}}" style="padding-left: 5px; vertical-align: middle;">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
        </td>
        <td class="narrow">
          {{if array_key_exists($_sejour->_id, $collisions)}}
          <span class="dhe_flag dhe_flag_important" style="float: left;">
            COL
          </span>
        {{/if}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty">
          Aucun séjour existant pour ce patient
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="collisions_tooltip" style="display: none;">
    <table class="layout">
    {{foreach from=$collisions item=_sejour}}
      <tr>
        <td{{if $_sejour->annule}} class="cancelled"{{/if}}>
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=true}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
            {{$_sejour->_shortview}}
          </span>
        </td>
        <td{{if $_sejour->annule}} class="cancelled"{{/if}} style="padding-left: 5px;">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td>
          Aucune collisions
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>