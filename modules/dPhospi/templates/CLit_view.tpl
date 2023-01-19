{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "hotellerie"|module_active}}
  {{mb_script module=hotellerie script=hotellerie ajax=1}}
{{/if}}

{{if $object->_id && !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}

      {{tr}}CLit{{/tr}} {{$object}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}CService{{/tr}}</th>
    <td><span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_service->_guid}}');">{{$object->_ref_service}}</span></td>
  </tr>

  <tr>
    <th>{{tr}}CChambre{{/tr}}</th>
    <td><span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_chambre->_guid}}');">{{$object->_ref_chambre}}</span></td>
  </tr>

  {{if $object->_ref_affectations|@count}}
    <tr>
      <th>{{tr}}CAffectation{{/tr}} du jour dans ce lit</th>
      <td>
        {{foreach from=$object->_ref_affectations item=_affectation}}
          <p><span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_guid}}');">{{$_affectation}}</span></p>
        {{/foreach}}
      </td>
    </tr>
  {{/if}}

  {{if "hotellerie"|module_active}}
    <tr>
      <th>{{tr}}CBedCleanup last finished{{/tr}}</th>
      <td {{if !$object->_ref_last_ended_cleanup->_id}}class="empty"{{/if}}>
        {{if !$object->_ref_last_ended_cleanup->_id}}
          {{tr}}CBedCleanup.none{{/tr}}
        {{else}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_last_ended_cleanup->_guid}}');">
            {{mb_value object=$object->_ref_last_ended_cleanup field=datetime_end}}
          </span>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{tr}}CBedCleanup current{{/tr}}</th>
      <td>
        {{if !$object->_ref_last_cleanup->_id}}
          <button type="button" class="new"
                  onclick="Hotellerie.editCleanup('{{$object->_ref_last_cleanup->_id }}', '{{$object->_id}}');">{{tr}}CBedCleanup.new{{/tr}}</button>
        {{else}}
          <button type="button" class="edit notext" onclick="Hotellerie.editCleanup('{{$object->_ref_last_cleanup->_id }}', '');"
                  title="{{tr}}CBedCleanup.modify{{/tr}}"></button>
        {{/if}}
      </td>
    </tr>
  {{/if}}

  <tr>
    <th>Prestations disponibles</th>
    <td>
      {{foreach from=$object->_prestations item=_prestation}}
        {{$_prestation}}
        <br />
        {{foreachelse}}
        {{tr}}None{{/tr}}
      {{/foreach}}
    </td>
  </tr>
</table>