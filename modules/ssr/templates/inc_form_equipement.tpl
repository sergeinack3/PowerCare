{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CEquipement" action="?m={{$m}}" method="post" onsubmit="return Equipement.onSubmit(this)">
  {{mb_key   object=$equipement}}
  {{mb_class object=$equipement}}
  {{mb_field object=$equipement field=plateau_id hidden=1}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    <tr>
      {{if $equipement->_id}}
        <th class="title modify" colspan="4">
          {{mb_include module=system template=inc_object_notes      object=$equipement}}
          {{mb_include module=system template=inc_object_idsante400 object=$equipement}}
          {{mb_include module=system template=inc_object_history    object=$equipement}}
          {{tr}}CEquipement-title-modify{{/tr}}
          '{{$equipement}}'
        </th>
      {{else}}
        <th class="title me-th-new" colspan="4">{{tr}}CEquipement-title-create{{/tr}}</th>
      {{/if}}
    </tr>

    <tr>
      <th>{{mb_label object=$equipement field=nom}}</th>
      <td>{{mb_field object=$equipement field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$equipement field=visualisable}}</th>
      <td>{{mb_field object=$equipement field=visualisable}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$equipement field=actif}}</th>
      <td>{{mb_field object=$equipement field=actif}}</td>
    </tr>

    <tr>
      <td class="button" colspan="4">
        {{if $equipement->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {
            typeName:'l\'équipement ',
            objName:'{{$equipement->_view|smarty:nodefaults|JSAttribute}}',
            ajax: 1}, Equipement.edit.curry('{{$plateau->_id}}', ''))">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit me-primary" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>