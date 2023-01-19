{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new" onclick="updateSelected('list_ressources'); Ressource.editRessource(0);">{{tr}}CRessourceMaterielle-create{{/tr}}</button>
<table class="tbl">
  <tr>
    <th colspan="2" class="title">{{tr}}CRessourceMaterielle.all{{/tr}}</th>
  </tr>
  <tr>
    <th class="category">{{tr}}CRessourceMaterielle-nom{{/tr}}</th>
    <th class="category">{{tr}}CRessourceMaterielle-type_ressource_id{{/tr}}</th>
  </tr>
  {{foreach from=$ressources_materielles item=_ressource}}
    <tr class="ressource {{if $ressource_id == $_ressource->_id}}selected{{/if}}"">
      <td>
        <a href="#1" onclick="updateSelected('list_ressources', this.up('tr')); Ressource.editRessource('{{$_ressource->_id}}')">
          {{mb_value object=$_ressource field=libelle}}
        </a>
      </td>
      <td>
        {{$_ressource->_ref_type_ressource}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">
        {{tr}}CRessourceMaterielle.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>