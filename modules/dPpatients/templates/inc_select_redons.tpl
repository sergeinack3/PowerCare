{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="selectRedons" method="post">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_select_redons" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />

  <table class="tbl">
    <tr>
      <th>
        {{tr}}CRedon.constante_medicale.{{$class_redon}}{{/tr}}
      </th>
    </tr>

    {{foreach from=$sejour->_ref_redons_by_redon.$class_redon item=_redon}}
    <tr>
      <td>
        <label>
          <input type="checkbox" {{if $_redon->_id}}checked{{/if}} value="{{$_redon->_id}}"
                 onclick="$V(this.form.elements['redons[{{$_redon->constante_medicale}}]'], this.checked ? 1 : 0);" />
          <input type="hidden" name="redons[{{$_redon->constante_medicale}}]" value="{{if $_redon->_id}}1{{/if}}" />
          {{tr}}CRedon.constante_medicale.{{$_redon->constante_medicale}}{{/tr}}
        </label>
      </td>
    </tr>
    {{/foreach}}
    <tr>
      <td class="button">
        <button type="button" class="tick me-primary" onclick="Redon.validateRedons(this.form);">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>