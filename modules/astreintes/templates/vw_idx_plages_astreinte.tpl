{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="astreintes" script="plage"}}
<button class="new" type="button" onclick="PlageAstreinte.modal(null, null, null, null, PlageAstreinte.refreshList)">
{{tr}}CPlageAstreinte-title-create{{/tr}}
</button>

{{if $plages|@count >= 100}}
  <div class="info">Sont affichés les 100 dernières astreintes de l'utilisateur</div>
{{/if}}

<table class="main">
  <tr>
     <td>
        <table class="tbl">
          <tr>
            <th class="title" colspan="5">{{tr}}CPlageAstreinte-list{{/tr}}</th>
          </tr>
          <tr>
            {{*<th class="category">
            {{tr}}CMediusers-_user_last_name{{/tr}} {{tr}}CMediusers-_user_first_name{{/tr}}
            </th>*}}
            <th class="catergory">
              {{tr}}CPlageAstreinte-user{{/tr}}
            </th>
            <th class="category">
            {{tr}}CPlageAstreinte-libelle{{/tr}}
            </th>
            <th class="category">
            {{tr}}Date{{/tr}}
            </th>
            <th class="category">
              {{tr}}Duration{{/tr}}
            </th>
            <th class="category">
              {{tr}}CPlageAstreinte-type{{/tr}}
            </th>
          </tr>
          {{foreach from=$plages item=_plage}}
            {{assign var=class value=""}}
            {{if $_plage->start <= $today && $_plage->end >= $today}}{{assign var=class value="highlight"}}{{/if}}
            <tr>
              <td class="{{$class}}">
                <a href="#" onclick="PlageAstreinte.modal('{{$_plage->_id}}','{{$_plage->user_id}}');">
                {{$_plage->_ref_user}}
                </a>
              </td>
              <td class="{{$class}} {{if !$_plage->libelle}}empty{{/if}}">
                {{if $_plage->libelle}}{{$_plage->libelle}}{{else}}<em>{{tr}}CPlageAstreinte.noLibelle{{/tr}}</em>{{/if}}
              </td>
              <td class="{{$class}}">
                {{mb_include module="system" template="inc_interval_datetime" from=$_plage->start to=$_plage->end}}
              </td>
              <td class="{{$class}}">
                {{mb_include module=system template=inc_vw_duration duration=$_plage->_duree}}
              </td>
              <td style="background-color: #{{$_plage->_color}}; color:#{{$_plage->_font_color}}">
                {{tr}}CPlageAstreinte.type.{{$_plage->type}}{{/tr}}
              </td>
            </tr>
          {{foreachelse}}
            <tr>
              <td colspan="5" class="empty">{{tr}}CMediusers.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
     </td>
  </tr>
</table>