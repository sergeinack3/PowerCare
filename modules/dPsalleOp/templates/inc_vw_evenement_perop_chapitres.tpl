{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="7">
      <button type="button" class="me-primary" style="float: left;" onclick="GestePerop.editEventPeropChapitre(0);">
        <i class="fas fa-plus"></i> {{tr}}CAnesthPeropChapitre-action-create an new chapter{{/tr}}
      </button>

      {{tr}}CAnesthPeropChapitre-List of chapter|pl{{/tr}} ({{$evenement_chapitres|@count}})
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_label class=CAnesthPeropChapitre field=libelle}}</th>
    <th class="text">{{mb_label class=CAnesthPeropChapitre field=description}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropChapitre field=group_id}}</th>
    <th class="text narrow">{{tr}}CAnesthPeropChapitre-Associated Perop Categories{{/tr}}</th>
    <th class="narrow">{{mb_label class=CAnesthPeropChapitre field=actif}}</th>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
  </tr>
  {{foreach from=$evenement_chapitres item=_chapitre}}
  {{assign var=group value=$_chapitre->_ref_group}}
    <tr {{if !$_chapitre->actif}}class="hatching"{{/if}}">
      <td>
        {{mb_value object=$_chapitre field=libelle}}
      </td>
      <td class="text">
        {{mb_value object=$_chapitre field=description}}
      </td>
      <td class="button">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$group->_guid}}')">
          {{mb_ditto name=group value=$group->_view}}
        </span>
      </td>
      <td class="button narrow">
        <span>
          {{$_chapitre->_ref_anesth_categories_perop|@count}}
        </span>
      </td>
      <td class="button">
        {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_chapitre
        onComplete="GestePerop.loadEventPeropChapitres()"}}
      </td>
      <td class="button narrow">
        <button type="button" onclick="GestePerop.editEventPeropChapitre('{{$_chapitre->_id}}');" title="{{tr}}Modify{{/tr}}">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CAnesthPeropChapitre.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
