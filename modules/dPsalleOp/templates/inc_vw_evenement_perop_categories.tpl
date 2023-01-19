{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="9">
      <button type="button" class="me-primary" style="float: left;" onclick="GestePerop.editEventPeropCategorie(0);">
        <i class="fas fa-plus"></i> {{tr}}CAnesthPeropCategorie-New category{{/tr}}
      </button>

      {{tr}}CAnesthPeropCategorie-List of category|pl{{/tr}} ({{$evenement_categories|@count}})
    </th>
  </tr>
  <tr>
    <th>{{tr}}CAnesthPeropCategorie-picture{{/tr}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropCategorie field=libelle}}</th>
    <th class="text">{{mb_label class=CAnesthPeropCategorie field=description}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropCategorie field=chapitre_id}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropCategorie field=group_id}}</th>
    <th class="text narrow">{{tr}}CAnesthPeropCategorie-Associated Perop Gestures{{/tr}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropCategorie field=actif}}</th>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
  </tr>
  {{foreach from=$evenement_categories item=_categorie}}
    {{assign var=group value=$_categorie->_ref_group}}
    <tr {{if !$_categorie->actif}}class="hatching"{{/if}}">
      <td class="narrow button">
        {{thumbnail document=$_categorie->_ref_file profile=small style="max-height:50px; max-width:50px;"}}
      </td>
      <td>
        {{mb_value object=$_categorie field=libelle}}
      </td>
      <td class="text">
        {{mb_value object=$_categorie field=description}}
      </td>
      <td class="button">
        {{mb_value object=$_categorie field=chapitre_id}}
      </td>
      <td class="button">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$group->_guid}}')">
          {{mb_ditto name=group value=$group->_view}}
        </span>
      </td>
      <td class="button narrow">
        <span style="cursor: pointer;" title="{{tr}}CAnesthPeropCategorie-See the perop gestures associated with this category{{/tr}}"
        onclick="GestePerop.showListGestes('{{$_categorie->_id}}');">
          {{$_categorie->_ref_gestes_perop|@count}}
        </span>
      </td>
      <td class="button">
        {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_categorie
        onComplete="GestePerop.loadEventPeropCategories();"}}
      </td>
      <td class="button narrow">
        <button type="button" onclick="GestePerop.editEventPeropCategorie('{{$_categorie->_id}}');" title="{{tr}}Modify{{/tr}}">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="9" class="empty">
        {{tr}}CAnesthPeropCategorie.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
