{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=see_global_users value="soins UserSejour see_global_users"|gconf}}

{{if $see_global_users}}
  <script>
    Main.add(function () {
      Control.Tabs.create('tab-edit_service', true);
      {{if $service->_id}}
      Infrastructure.loadListUsersService('{{$service->_id}}');
      {{/if}}
    });
  </script>
  <ul id="tab-edit_service" class="control_tabs small">
    <li><a href="#service_to_edit">{{tr}}CService{{/tr}}</a></li>
    <li><a href="#affectation_user">{{tr}}CAffectationUserService{{/tr}}</a></li>
  </ul>
  <div id="affectation_user" style="display:none;">
    {{if !$service->_id}}
      <div class="small-info">{{tr}}CService.none{{/tr}}</div>{{/if}}
  </div>
{{/if}}

<script>
  Main.add(function () {
    var form = getForm("edit{{$service->_guid}}");

    // Double focus car appel ajax
    form.nom.focus();
    form.nom.focus();
  });
</script>

<div id="service_to_edit" {{if $see_global_users}}style="display:none;"{{/if}}>
  <!-- Formulaire d'un service -->
  <form name="edit{{$service->_guid}}" method="post"
        onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close();}});">
    {{mb_key object=$service}}
    {{mb_class object=$service}}
    <table class="form">
      {{mb_include module=system template=inc_form_table_header_uf object=$service tag=$tag_service}}
      <tr>
        <th>{{mb_label object=$service field=group_id}}</th>
        <td>{{mb_field object=$service field=group_id options=$etablissements}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=nom}}</th>
        <td>{{mb_field object=$service field=nom}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=code}}</th>
        <td>{{mb_field object=$service field=code}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=is_soins_continue}}</th>
        <td>{{mb_field object=$service field=is_soins_continue}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=cancelled}}</th>
        <td>{{mb_field object=$service field=cancelled}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=responsable_id}}</th>
        <td>
          <select name="responsable_id">
            <option value="">&mdash; {{tr}}None{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens selected=$service->responsable_id}}
          </select>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=type_sejour}}</th>
        <td>{{mb_field object=$service field=type_sejour}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=default_destination}}</th>
        <td>{{mb_field object=$service field=default_destination emptyLabel="Choose"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=default_orientation}}</th>
        <td>{{mb_field object=$service field=default_orientation emptyLabel="Choose"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=max_ambu_per_day}}</th>
        <td>{{mb_field object=$service field=max_ambu_per_day}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=max_hospi_per_day}}</th>
        <td>{{mb_field object=$service field=max_hospi_per_day}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=urgence}}</th>
        <td>{{mb_field object=$service field=urgence}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=uhcd}}</th>
        <td>{{mb_field object=$service field=uhcd}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=hospit_jour}}</th>
        <td>{{mb_field object=$service field=hospit_jour}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=externe}}</th>
        <td>{{mb_field object=$service field=externe}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=neonatalogie}}</th>
        <td>{{mb_field object=$service field=neonatalogie}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=radiologie}}</th>
        <td>{{mb_field object=$service field=radiologie}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=obstetrique}}</th>
        <td>{{mb_field object=$service field=obstetrique}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=usc}}</th>
        <td>{{mb_field object=$service field=usc}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=tel}}</th>
        <td>{{mb_field object=$service field=tel}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=description}}</th>
        <td>{{mb_field object=$service field=description}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$service field=use_brancardage}}</th>
        <td>{{mb_field object=$service field=use_brancardage}}</td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          {{if $service->_id}}
            <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            <button class="trash" type="button"
                    onclick="confirmDeletion(this.form,{typeName:'le service ',objName: $V(this.form.nom)}, Control.Modal.close)">
              {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
</div>