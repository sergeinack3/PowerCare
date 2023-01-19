{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<th>
  <form name="editAffectation{{$name}}" action="?m={{$m}}" method="post">
    <input type="hidden" name="m" value="dPpersonnel" />
    <input type="hidden" name="dosql" value="do_affectation_aed" />

    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="object_id" value="{{$plage->_id}}" />
    <input type="hidden" name="object_class" value="{{$plage->_class}}" />
    <input type="hidden" name="realise" value="0" />
    <select name="personnel_id" style="width: 12em;" onchange="onSubmitFormAjax(this.form, {onComplete: reloadPersonnelPrevu});">
      <option value="">&mdash; {{tr}}CPersonnel.emplacement.{{$type}}{{/tr}}</option>
      {{foreach from=$list item=_personnelBloc}}
        <option value="{{$_personnelBloc->_id}}">{{$_personnelBloc->_ref_user->_view}}</option>
      {{/foreach}}
    </select>
  </form>
</th>
<td style="min-width: 15em;">
  {{foreach from=$affectations_plage.$type item=_affectation}}
    <form name="supAffectation-{{$_affectation->_id}}" action="?m={{$m}}" method="post">
      <input type="hidden" name="m" value="dPpersonnel" />
      <input type="hidden" name="dosql" value="do_affectation_aed" />
      <input type="hidden" name="affect_id" value="{{$_affectation->_id}}" />
      <input type="hidden" name="del" value="1" />
      <button class="cancel" type="button" onclick="onSubmitFormAjax(this.form, {onComplete: reloadPersonnelPrevu});">
        {{$_affectation->_ref_personnel->_ref_user->_view}}
      </button>
    </form>
  {{/foreach}}
</td>
