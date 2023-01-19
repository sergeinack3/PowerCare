{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}CGestePeropPrecision-action-Choose the details for the selected gestures{{/tr}} ({{$gestes|@count}})
    </th>
  </tr>
  <tr>
    <th>{{mb_label class=CGestePerop field=libelle}}</th>
    <th class="narrow">{{mb_label class=CGestePerop field=precision_1_id}}</th>
    <th class="narrow">{{mb_label class=CGestePerop field=precision_2_id}}</th>
  </tr>
  {{foreach from=$gestes item=_geste}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_geste->_guid}}');">
          {{$_geste->_view}}
        </span>
      </td>
      <td>
        <form name="precision_1_{{$_geste->_guid}}" action="" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$_geste}}
          {{mb_key   object=$_geste}}

          {{mb_field object=$_geste field=precision_1_id options=$precisions style="width: 250px;" onchange="this.form.onsubmit();"}}
        </form>
      </td>
      <td>
        <form name="precision_2_{{$_geste->_guid}}" action="" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$_geste}}
          {{mb_key   object=$_geste}}

          {{mb_field object=$_geste field=precision_2_id options=$precisions style="width: 250px;" onchange="this.form.onsubmit();"}}
        </form>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">
        {{tr}}CGestePerop.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}

  <tr>
    <td class="button" colspan="3">
      <button type="button" class="singleclick" title="{{tr}}Validate{{/tr}}"
      onclick="Control.Modal.close();">
        <i class="fas fa-check"></i> {{tr}}Validate{{/tr}}
      </button>
    </td>
  </tr>
</table>
