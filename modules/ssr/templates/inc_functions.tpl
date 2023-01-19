{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=codes_affectation ajax=$ajax}}

<tr>
  <td colspan="3">
    {{mb_include module=system template=inc_pagination change_page='CodesAffectation.changePage' total=$total current=$page step=$step}}
  </td>
</tr>
{{foreach from=$functions item=_function}}
  <tr>
    <td>{{mb_include module=mediusers template=inc_vw_function function=$_function}}</td>
    <td>
      <ul>
        {{foreach from=$_function->_refs_codes_affectations item=_affectation}}
          <li>{{$_affectation->code}}: {{$_affectation->_ref_code->libelle}}</li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      <button type="button"
              class="edit notext"
              onclick="CodesAffectation.openAffectations(this)"
              data-function-id="{{$_function->_id}}"
              style="float: right;"
              title="{{tr}}CCodeAffectation{{/tr}}"></button>
    </td>
  </tr>
{{foreachelse}}
  <tr>
    <td colspan="6" class="empty">{{tr}}CFunctions.none{{/tr}}</td>
  </tr>
{{/foreach}}
