{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=CMbObject_view}}

{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}
<table class="tbl tooltip">
  <tr>
    <td class="text {{if !$object->_ref_actes|@count && $use_acte_presta == "aucun"}}empty{{/if}}">
      {{if $object->_ref_actes|@count}}
        {{foreach from=$object->_ref_actes_by_type key=type item=_actes}}
          {{if $_actes|@count}}
            <strong>{{tr}}CEvenementSSR-code|pl{{/tr}} {{tr}}CActePlage.type.{{$type}}{{/tr}}:</strong>
            <ul>
              {{foreach from=$_actes item=_acte}}
                <li>{{$_acte->code}} (x{{$_acte->quantite}})</li>
              {{/foreach}}
            </ul>
          {{/if}}
        {{/foreach}}
      {{else}}
        <div {{if $use_acte_presta != "aucun"}}class="small-warning"{{/if}}>
          {{tr}}CActeCsARR-none_params{{/tr}}
        </div>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td class="text">
      <strong>{{tr}}CPatient|pl{{/tr}}:</strong>
      <ul>
        {{foreach from=$object->_ref_sejours_affectes item=_sejour}}
          <li>{{$_sejour->_view}}</li>
        {{foreachelse}}
          <li class="empty">{{tr}}CPatient.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
  <tr>
    <td class="button">
      {{if $app->user_prefs.edit_planning_collectif}}
        <button type="button" class="edit" onclick="TrameCollective.editPlage('{{$object->_id}}')">
          {{tr}}CPlageSeanceCollective-title-modify{{/tr}}
        </button>
      {{/if}}
      {{if ($app->user_prefs.edit_planning_collectif
        || $app->user_id == $object->user_id
        || $app->_ref_user->function_id == $object->_ref_user->function_id)
        && $object->active}}
        <button type="button" class="list" onclick="TrameCollective.gestionPatient('{{$object->_id}}');">
          {{tr}}CPlageSeanceCollective.gestionPatient.court{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>
