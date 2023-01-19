{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function(){
    CategorieObjectifSoin.updateButton(null, '{{$countInactive}}');
  });
</script>

<table id="liste_categories_objectif_soin" class="tbl">
  <tr>
    <th colspan="4" class="title">{{tr}}CObjectifSoinCategorie.list{{/tr}}</th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CObjectifSoinCategorie field=libelle}}</th>
    <th class="narrow">{{mb_title class=CObjectifSoinCategorie field=description}}</th>
    <th class="narrow">{{mb_title class=CObjectifSoinCategorie field=group_id}}</th>
  </tr>
  {{foreach from=$categories item=_categorie}}
    <tr {{if !$_categorie->actif}}style="display:none" class="hatching"{{/if}}>
      <td>
        <button type="button" class="button edit notext" onclick="CategorieObjectifSoin.edit('{{$_categorie->_id}}');"></button>
      </td>
      <td>
        {{$_categorie->libelle|spancate}}
      </td>
      <td>
        <span class="compact">{{$_categorie->description}}</span>
      </td>
      <td>
        {{if $_categorie->group_id}}
          {{$_categorie->_ref_group->_view}}
        {{else}}
          {{tr}}All{{/tr}}
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">{{tr}}CObjectifSoinCategorie.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>