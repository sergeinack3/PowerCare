{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editFrm" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function() { Control.Tabs.GroupedTabs.refresh(); })">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_prestation_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$prestation}}

  <table class="form">
    <tr>
      {{if $prestation->_id}}
        <th class="title modify" colspan="2">
          {{mb_include module=system template=inc_object_notes      object=$prestation}}
          {{mb_include module=system template=inc_object_idsante400 object=$prestation}}
          {{mb_include module=system template=inc_object_history    object=$prestation}}
          {{tr}}CPrestation-msg-modify{{/tr}} '{{$prestation}}'
        </th>
      {{else}}
        <th class="title me-th-new" colspan="2">
          {{tr}}CPrestation-msg-create{{/tr}}
        </th>
      {{/if}}
    </tr>
    
    <tr>
      <th>{{mb_label object=$prestation field=group_id}}</th>
      <td>{{mb_field object=$prestation field=group_id options=$etablissements}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$prestation field=code}}</th>
      <td>{{mb_field object=$prestation field=code}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$prestation field=nom}}</th>
      <td>{{mb_field object=$prestation field=nom}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$prestation field=description}}</th>
      <td>{{mb_field object=$prestation field=description}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $prestation->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(
                  this.form,
                  {typeName:'la prestation ',objName:$V(this.form.nom)},
                  function() { Control.Tabs.GroupedTabs.refresh(); }
                  );">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>