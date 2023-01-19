{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new" onclick="updateSelected('list_type_ressources'); TypeRessource.editTypeRessource(0);">{{tr}}CTypeRessource-create{{/tr}}</button>
<table class="tbl">
  <tr>
    <th colspan="3" class="title">{{tr}}CTypeRessource.all{{/tr}}</th>
  </tr>
  <tr>
    <th class="category" style="width: 33%">{{tr}}CTypeRessource-libelle{{/tr}}</th>
    <th class="category">{{tr}}CRessourceMaterielle.all{{/tr}}</th>
    <th class="category narrow">{{tr}}Action{{/tr}}</th>
  </tr>
  {{foreach from=$type_ressources item=_type_ressource}}
    <tr class="ressource {{if $type_ressource_id == $_type_ressource->_id}}selected{{/if}}"">
      <td>
        <a href="#1" onclick="updateSelected('list_type_ressources', this.up('tr')); TypeRessource.editTypeRessource('{{$_type_ressource->_id}}')">
          <strong>{{mb_value object=$_type_ressource field=libelle}}</strong>
        </a>
        <div class="compact">
          {{mb_value object=$_type_ressource field=description}}
        </div>
      </td>
      <td>
        {{foreach from=$_type_ressource->_ref_ressources item=_ressource}}
          <div>
            <a href="#1" onclick="updateSelected('list_type_ressources', this.up('tr')); Ressource.editRessource('{{$_ressource->_id}}')">{{$_ressource}}</a>
          </div>
          
        {{/foreach}}
      </td>
      <td>
        <button type="button" class="add " onclick="updateSelected('list_type_ressources', this.up('tr')); Ressource.editRessource(0, '{{$_type_ressource->_id}}')">
          {{tr}}Add{{/tr}} {{tr}}CRessourceMaterielle{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">
        {{tr}}CTypeRessource.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>