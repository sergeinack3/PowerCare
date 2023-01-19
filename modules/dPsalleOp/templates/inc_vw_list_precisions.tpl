{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tbody>
    {{foreach from=$precisions item=_precision}}
        {{assign var=group value=$_precision->_ref_group}}
        {{assign var=valeurs value=$_precision->_ref_valeurs}}
      <tr class="{{if !$_precision->actif}}hatching{{/if}}">
        <td>
            {{mb_ditto name=libelle value=$_precision->libelle center=true}}
        </td>
        <td class="text">
            {{mb_ditto name=description value=$_precision->description center=true}}
        </td>
        <td class="narrow button">
            {{$valeurs|@count}}
        </td>
        <td class="button">
            {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_precision
            onComplete="GestePerop.loadlistPrecisions(`$_precision->geste_perop_id`)"}}
        </td>
        <td class="button narrow">
          <button type="button" onclick="GestePerop.editPrecision('{{$_precision->_id}}', '{{$_precision->geste_perop_id}}');" title="{{tr}}Modify{{/tr}}">
            <i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">
            {{tr}}CGestePeropPrecision.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
    <tr>
      <th class="title" colspan="7">
        <button type="button" style="float: left;" onclick="GestePerop.editPrecision(0, '{{$geste_perop->_id}}');">
          <i class="fas fa-plus"></i> {{tr}}CGestePeropPrecision-action-Add a precision{{/tr}}
        </button>

          {{tr}}CGestePeropPrecision-List of precision|pl{{/tr}} ({{$precisions|@count}})
      </th>
    </tr>
    <tr>
      <th class="narrow">{{mb_label class=CGestePeropPrecision field=libelle}}</th>
      <th class="text">{{mb_label class=CGestePeropPrecision field=description}}</th>
      <th class="text">{{tr}}CGestePeropPrecision-Associated values{{/tr}}</th>
      <th class="narrow">{{mb_label class=CGestePeropPrecision field=actif}}</th>
      <th class="narrow">{{tr}}common-Action{{/tr}}</th>
    </tr>
  </thead>
</table>
