{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new"
        onclick="Prestation.removeSelected('item'); Prestation.editItem(0, '{{$item->object_class}}', '{{$item->object_id}}')">
  Création d'item
</button>
<form name="edit_item" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$item}}
  {{mb_key   object=$item}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="Prestation.afterEditItem" />
  {{mb_field object=$item field=object_id hidden=1}}
  {{mb_field object=$item field=object_class hidden=1}}
  {{mb_field object=$item field=rank hidden=1}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$item}}
    <tr>
      <th>{{mb_label object=$item field=nom}}</th>
      <td>{{mb_field object=$item field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=nom_court}}</th>
      <td>{{mb_field object=$item field=nom_court}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=actif typeEnum=checkbox}}</th>
      <td>{{mb_field object=$item field=actif typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=color}}</th>
      <td>{{mb_field object=$item field=color form="edit_item"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=facturable}}</th>
      <td>{{mb_field object=$item field=facturable}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=price}}</th>
      <td>{{mb_field object=$item field=price}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field=chambre_double}}</th>
      <td>{{mb_field object=$item field=chambre_double}}</td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$item field=chambre_part_id}}
      </th>
      <td>
        <select name="chambre_part_id">
          <option value>{{tr}}CItemPrestation.none{{/tr}}</option>
          {{foreach from='Ox\Mediboard\Hospi\CPrestationJournaliere::loadCurrentList'|static_call:null item=_prestation}}
            <optgroup label="{{$_prestation}}"></optgroup>
            {{foreach from=$_prestation->loadRefsItems() item=_item}}
              {{if $_item->_id != $item->_id}}
                <option value="{{$_item->_id}}" {{if $_item->_id == $item->chambre_part_id}}selected{{/if}}>{{$_item}}</option>
              {{/if}}
            {{/foreach}}
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit()">
          {{tr}}{{if $item->_id}}Save{{else}}Create{{/if}}{{/tr}}
        </button>
        {{if $item->_id}}
          <button type="button" class="cancel" onclick="confirmDeletion(this.form, {
            typeName: 'l\'item de prestation',
            objName:'{{$item->_view|smarty:nodefaults|JSAttribute}}',
            ajax: true})">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
