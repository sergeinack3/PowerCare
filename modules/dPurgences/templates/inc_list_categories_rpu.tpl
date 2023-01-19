{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CRPUCategorie.list{{/tr}}
    </th>
  </tr>
  {{foreach from=$categories_rpu item=_categorie_rpu}}
    <tr onclick="CategorieRPU.edit('{{$_categorie_rpu->_id}}')" style="cursor: pointer;">
      <td class="narrow">
        {{if $_categorie_rpu->_ref_icone}}
          {{thumbnail document=$_categorie_rpu->_ref_icone profile=small style="width: 20px; height: 20px;"}}
        {{/if}}
      </td>
      <td {{if !$_categorie_rpu->actif}}class="opacity-40"{{/if}}>
        {{$_categorie_rpu}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">
        {{tr}}CRPUCategorie.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>