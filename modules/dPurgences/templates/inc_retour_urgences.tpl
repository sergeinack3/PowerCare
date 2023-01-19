{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('retourUrgences');

    var box = form.box_id;

    box.observe("change", function(event){
      var service_id = box.options[box.selectedIndex].up("optgroup").get("service_id");
      $V(form.elements._service_id, service_id, false);
    });
  });
</script>

<form name="retourUrgences" method="post">
  <input type="hidden" name="m" value="urgences" />
  <input type="hidden" name="dosql" value="do_retour_urgences" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />

  <table class="tbl">
    <tr>
      <th>
        {{$sejour->_ref_patient->_view}}
      </th>
    </tr>

    <tr>
      <td>
        {{mb_include module=hospi template="inc_select_lit" field=box_id listService=$services classes="notNull"}}

        <div style="display: inline-block">
          &mdash; {{tr}}CRPU-_service_id{{/tr}} :
          {{if $services|@count == 1}}
            {{assign var=first_service value=$services_type|@first|@first}}
            <input type="hidden" name="_service_id" value="{{$first_service->_id}}" />
            {{$first_service->_view}}
          {{else}}
            <select name="_service_id" class="{{$sejour->_props.service_id}}" onchange="$V(this.form.box_id, '', false);">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$services_type item=_services key=nom_serv}}
                <optgroup label="{{$nom_serv}}">
                  {{foreach from=$_services item=_service}}
                    <option value="{{$_service->_id}}">
                      {{$_service->_view}}
                    </option>
                  {{/foreach}}
                </optgroup>
              {{/foreach}}
            </select>
          {{/if}}
        </div>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="tick" onclick="AvisMaternite.submitRetour(this.form);">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>