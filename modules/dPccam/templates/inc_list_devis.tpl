{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="8">
      <span style="float: left" class="me-float-right">
        {{mb_include module=ccam template=inc_create_devis}}
      </span>
      {{tr}}CDevisCodage{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}CDevisCodage-libelle{{/tr}}</th>
    <th>{{tr}}CDevisCodage-event_type{{/tr}}</th>
    <th>{{tr}}CDevisCodage-creation_date{{/tr}}</th>
    <th>{{tr}}CDevisCodage-date{{/tr}}</th>
    <th>{{tr}}CDevisCodage-base{{/tr}}</th>
    <th>{{tr}}CDevisCodage-dh{{/tr}}</th>
    <th>{{tr}}CDevisCodage-_total{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$list_devis item=_devis}}
    <tr>
      <td>{{mb_value object=$_devis field=libelle}}</td>
      <td>{{mb_value object=$_devis field=event_type}}</td>
      <td>{{mb_value object=$_devis field=creation_date}}</td>
      <td>{{mb_value object=$_devis field=date}}</td>
      <td>{{mb_value object=$_devis field=base}}</td>
      <td>{{mb_value object=$_devis field=dh}}</td>
      <td>{{mb_value object=$_devis field=_total}}</td>
      <td class="narrow">
        <button class="edit notext" type="button" onclick="DevisCodage.edit('{{$_devis->_id}}', DevisCodage.list.curry('{{$object->_class}}', '{{$object->_id}}'));">
          {{tr}}CDevisCodage-title-modify{{/tr}}
        </button>
        <button class="print notext" type="button" onclick="DevisCodage.print({{$_devis->_id}});">{{tr}}CDevisCodage-print{{/tr}}</button>
        <form name="deleteDevis-{{$_devis->_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, function() {
          DevisCodage.list('{{$_devis->codable_class}}', '{{$_devis->codable_id}}');
        })">
          {{mb_class object=$_devis}}
          {{mb_key object=$_devis}}
          <input type="hidden" name="del" value="1"/>


          <button class="trash notext" type="submit" onclick="">
            {{tr}}Delete{{/tr}}
          </button>
        </form>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CDevisCodage.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>