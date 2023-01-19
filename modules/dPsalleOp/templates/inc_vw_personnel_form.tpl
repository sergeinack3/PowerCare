{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="affectationPers-{{$emplacement}}" method="post">
  <input type="hidden" name="m" value="personnel" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="affect_id" />
  <input type="hidden" name="object_class" value="COperation" />
  <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
  <input type="hidden" name="realise" value="0" />

  <select name="personnel_id" onchange="submitPersonnel(this.form);" style="width: 11em;">
    <option value="">&mdash; {{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}</option>
    {{foreach from=$listPers.$emplacement item="pers"}}
      <option value="{{$pers->_id}}" class="mediuser" style="border-color: #{{$pers->_ref_user->_ref_function->color}};">{{$pers->_ref_user}}</option>
    {{/foreach}}
  </select>
</form>
