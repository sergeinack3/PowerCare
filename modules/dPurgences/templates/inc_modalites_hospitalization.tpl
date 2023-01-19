{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=services    value=null}}
{{mb_default var=other_group value=0}}
{{mb_default var=group_id    value=$g}}

{{if is_array($services) && $services|@count}}
  <tr>
    <th>{{mb_label object=$sejour field=service_id}}</th>
    <td>
      <select name="service_id">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$services item=_service}}
          <option value="{{$_service->_id}}">
            {{$_service}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
{{/if}}

<tr>
  <th class="halfPane"><label for="praticien_id" class="notNull">{{tr}}CSejour-Praticien responsable hospitalisation{{/tr}}</label></th>
  <td>
    {{mb_field object=$sejour field=praticien_id hidden=1 class=notNull}}
    <input type="text" name="praticien_id_view" value="{{$sejour->_ref_praticien}}" />

    <script>
      Main.add(function() {
        var form = getForm("confirmHospitalization");
        var element = form.praticien_id_view;
        new Url("mediusers", "ajax_users_autocomplete")
          .addParam("praticiens", "1")
          {{if !"dPurgences CRPU hospi_with_urgentiste"|gconf}}
          .addParam("with_urgentistes", "0")
          {{/if}}
          .addParam("use_group", "1")
          .addParam("group_id", "{{$group_id}}")
          .addParam("input_field", element.name)
          .autoComplete(element, null, {
            minChars: 0,
            method: "get",
            select: "view",
            dropdown: true,
            afterUpdateElement: function (field, selected) {
              var span = selected.down('.view');
              $V(field, span.getText());

              var id = selected.getAttribute("id").split("-")[2];
              $V(field.form.praticien_id, id);

              preselectUf();
            }
          });

        form.removeClassName("prepared");
        prepareForm(form);
      });
    </script>
  </td>
</tr>
<tr>
  <th>{{mb_label object=$sejour field=mode_entree}}</th>
  <td>{{mb_field object=$sejour field=mode_entree onchange="Urgences.toggleModeEntree(this.value);"}}</td>
</tr>

{{if $required_uf_soins === "obl" && $ufs.soins|@count}}
  <tr>
    <th>{{mb_label object=$sejour field=uf_soins_id}}</th>
    <td>
      <select name="uf_soins_id" class="ref notNull" style="width: 15em">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$ufs.soins item=_uf}}
          <option value="{{$_uf->_id}}">
            {{mb_value object=$_uf field=libelle}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
{{/if}}

{{if $required_uf_med !== "no" && $ufs.medicale|@count}}
  <tr>
    <th>{{mb_label object=$sejour field=uf_medicale_id}}</th>
    <td>
      <select name="uf_medicale_id" class="ref {{if $required_uf_med === "obl"}}notNull{{/if}}" style="width: 15em">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$ufs.medicale item=_uf}}
          <option value="{{$_uf->_id}}">
            {{mb_value object=$_uf field=libelle}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
{{/if}}

{{assign var=init_duree_prev_mutation_hospit value="dPurgences CRPU init_duree_prev_mutation_hospit"|gconf}}
{{if $init_duree_prev_mutation_hospit}}
  <tr id="duree_prevue_container" {{if $sejour->mode_entree !== '6'}}style="display:none"{{/if}}>
    <th>{{mb_label object=$sejour field=_duree_prevue}}</th>
    <td>
      {{mb_field object=$sejour field=_duree_prevue increment=true form=confirmHospitalization size=2
                 value=$init_duree_prev_mutation_hospit}} {{tr}}night|pl{{/tr}}
    </td>
  </tr>
{{/if}}
