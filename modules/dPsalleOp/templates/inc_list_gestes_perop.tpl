{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    $("listGestePerop").fixedTableHeaders();
  })
</script>

{{mb_include module=system template=inc_pagination total=$nbResultat current=$page change_page='GestePerop.changePage' step=30}}

<div id="listGestePerop">
  <table class="main tbl">
    <tbody>
    {{foreach from=$gestes_perop item=_geste}}
      {{assign var=precisions value=$_geste->_ref_precisions}}
      <tr {{if !$_geste->actif}}class="hatching"{{/if}}>
        <td class="narrow button">
            {{thumbnail document=$_geste->_ref_file profile=small style="max-height:50px; max-width:50px;"}}
        </td>
        <td class="button">
          {{mb_value object=$_geste field=libelle}}
        </td>
        <td class="text">
          {{mb_value object=$_geste field=description}}
        </td>
        <td>
          {{mb_value object=$_geste field=categorie_id tooltip=true}}
        </td>
        <td>
          <ul>
            {{foreach from=$precisions item=_precision}}
              <li {{if !$_precision->actif}}style="text-decoration: line-through;"{{/if}}>{{$_precision->_view}}</li>
            {{foreachelse}}
              <li class="empty">{{tr}}CGestePeropPrecision.none{{/tr}}</li>
            {{/foreach}}
          </ul>
        </td>
        <td>
          {{mb_value object=$_geste field=group_id tooltip=true}}
        </td>
        <td>
          {{mb_value object=$_geste field=function_id tooltip=true}}
        </td>
        <td>
          {{mb_value object=$_geste field=user_id tooltip=true}}
        </td>
        <td class="button">
          {{mb_include module=system template=inc_vw_bool_icon value=$_geste->incident}}
        </td>
        <td class="button">
          {{mb_value object=$_geste field=antecedent_code_cim}}
        </td>
        <td class="button">
            {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_geste
            onComplete="GestePerop.loadGestesPerop(getForm('filterGestePerop'))"}}
        </td>
        <td class="button narrow">
          <button type="button" onclick="GestePerop.editGestePerop('{{$_geste->_id}}');" title="{{tr}}Modify{{/tr}}">
            <i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="12" class="empty">
          {{tr}}CGestePerop.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
    <thead>
    <tr>
      <th class="title" colspan="12">
        <button type="button" class="me-primary" style="float: left;" onclick="GestePerop.editGestePerop(0);">
          <i class="fas fa-plus"></i> {{tr}}CGestePerop-action-New Geste perop{{/tr}}
        </button>

        {{tr}}CGestePerop-List of geste perop|pl{{/tr}} ({{$gestes_perop|@count}})
      </th>
    </tr>
    <tr>
      <th>{{tr}}CAnesthPeropCategorie-picture{{/tr}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=libelle}}</th>
      <th class="text">{{mb_label class=CGestePerop field=description}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=categorie_id}}</th>
      <th class="narrow">{{tr}}CGestePeropPrecision|pl{{/tr}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=group_id}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=function_id}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=user_id}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=incident}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=antecedent_code_cim}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=actif}}</th>
      <th class="narrow">{{tr}}common-Action{{/tr}}</th>
    </tr>
    </thead>
  </table>
</div>
