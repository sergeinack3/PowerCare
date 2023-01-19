{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="category" colspan="2">
      {{tr}}CBloodSalvage-nurse_sspi{{/tr}}
    </th>
  </tr>
  <tr>
    <td style="text-align:center">
      <form name="affectNurse" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="dPpersonnel" />
        <input type="hidden" name="dosql" value="do_affectation_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="affect_id" value="" />
        <input type="hidden" name="object_class" value="CBloodSalvage" />
        <input type="hidden" name="object_id" value="{{$blood_salvage->_id}}" />
        <input type="hidden" name="realise" value="0" />

        <select name="personnel_id" onchange="submitNurse(this.form)">
          <option value="">&mdash; {{tr}}CBloodSalvage-nurse_sspi.all{{/tr}}</option>
          {{foreach from=$list_nurse_sspi item="nurse"}}
            <option value="{{$nurse->_id}}">{{$nurse->_ref_user->_view}}</option>
          {{/foreach}}
        </select>
      </form>
    </td>

    <td>
      {{foreach from=$tabAffected key=affectation_id item=affectation}}
        {{if $modif_operation}}
          <div style="float: right">
            <form name="cancelAffectation-{{$affectation_id}}" action="?m={{$m}}" method="post">
              <input type="hidden" name="affect_id" value="{{$affectation->_id}}" />
              <input type="hidden" name="m" value="dPpersonnel" />
              <input type="hidden" name="dosql" value="do_affectation_aed" />
              <input type="hidden" name="del" value="1" />
              <button type="button" class="cancel notext" onclick="submitNurse(this.form)">{{tr}}Cancel{{/tr}}</button>
            </form>
          </div>
        {{/if}}

        {{$affectation->_ref_personnel->_ref_user->_view}}
        <hr />
      {{/foreach}}
    </td>
  </tr>

</table>