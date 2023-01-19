{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td class="narrow">
  <select name="_{{$type}}_id" style="width: 10em;">
    <option value="">&mdash; {{tr}}CPersonnel.emplacement.{{$type}}{{/tr}}</option>
    {{foreach from=$list item=_personnelBloc}}
      <option value="{{$_personnelBloc->_id}}">{{$_personnelBloc->_ref_user->_view}}</option>
    {{/foreach}}
  </select>
</td>
<td class="text" style="width: 20%;">
  {{if isset($plagesel->_ref_affectations_personnel.$type|smarty:nodefaults)}}
    {{foreach from=$plagesel->_ref_affectations_personnel.$type item=_affectation_personnel}}
      {{assign var=personnel value=$_affectation_personnel->_ref_personnel}}
      <span style="white-space: nowrap;">
                  <input type="hidden" name="_del_{{$type}}_ids[{{$personnel->_id}}]" value="{{$personnel->_id}}" disabled/>
                  <button type="button" class="cancel notext"
                          onclick="toggleDel(this.form.elements['_del_{{$type}}_ids[{{$personnel->_id}}]'])"></button>
        {{$personnel->_ref_user}}
      </span>
    {{/foreach}}
  {{/if}}
</td>
