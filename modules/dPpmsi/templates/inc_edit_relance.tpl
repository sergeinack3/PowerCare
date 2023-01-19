{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("praticiens", 1)
      .addParam("input_field", "chir_id_view")
      .autoComplete(getForm("editRelance").chir_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var form = getForm("editRelance");
          if ($V(form.chir_id_view) == "") {
            $V(form.chir_id_view, selected.down('.view').innerHTML);
          }
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.chir_id, id);
        }
      });
  });
</script>

<form name="editRelance" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$relance}}
  {{mb_key object=$relance}}

  {{mb_field object=$relance field=sejour_id hidden=true}}
  {{mb_field object=$relance field=patient_id hidden=true}}
  {{mb_field object=$relance field=chir_id hidden=true}}
  {{mb_field object=$relance field=datetime_creation hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$relance}}

    {{if $relance->chir_id == $app->user_id}}
    <tr>
      <th>{{mb_label object=$relance field=commentaire_med}}</th>
      <td>{{mb_field object=$relance field=commentaire_med form=editRelance}}</td>
    </tr>
    {{else}}
    <tr>
      <th>{{mb_label object=$relance field=urgence}}</th>
      <td>{{mb_field object=$relance field=urgence}}</td>
    </tr>

    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
      {{if "dPpmsi relances $doc"|gconf}}
        {{assign var=onchange value=""}}

        {{if $doc == "autre"}}
          {{assign var=onchange value="\$('description_autre')[this.value == 1 ? 'show' : 'hide']()"}}
        {{/if}}
        <tr>
          <th>{{mb_label object=$relance field=$doc}}</th>
          <td>{{mb_field object=$relance field=$doc onchange=$onchange|smarty:nodefaults}}</td>
        </tr>
      {{/if}}
    {{/foreach}}

    <tr id="description_autre" {{if !$relance->autre}}style="display: none;"{{/if}}>
      <th>{{mb_label object=$relance field=description}}</th>
      <td>{{mb_field object=$relance field=description form=editRelance}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$relance field=commentaire_dim}}</th>
      <td>{{mb_field object=$relance field=commentaire_dim form=editRelance}}</td>
    </tr>

    <tr {{if !$relance->datetime_relance}}style="display: none;"{{/if}}>
      <th>{{mb_label object=$relance field=datetime_relance}}</th>
      <td>{{mb_field object=$relance field=datetime_relance form=editRelance register=true}}</td>
    </tr>

    <tr {{if !$relance->datetime_cloture}}style="display: none;"{{/if}}>
      <th>{{mb_label object=$relance field=datetime_cloture}}</th>
      <td>{{mb_field object=$relance field=datetime_cloture form=editRelance register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$relance field=chir_id}}</th>
      <td>
        <input type="text" name="chir_id_view" value="{{$relance->_ref_chir}}" />
      </td>
    </tr>
    {{/if}}

    <tr>
      <td colspan="2" class="button">
        {{if !$relance->_id}}
          <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
        {{else}}
          <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>

          {{if $relance->chir_id != $app->user_id}}
            {{if !$relance->datetime_cloture}}
              {{if !$relance->datetime_relance}}
              <button type="button" class="tick"
                      onclick="$V(this.form.datetime_relance, 'current'); this.form.onsubmit();">Relancer</button>
              {{/if}}

              <button type="button" class="cancel"
                      onclick="$V(this.form.datetime_cloture, 'current'); this.form.onsubmit();">Clôturer</button>
            {{/if}}

            <button type="button" class="trash"
                    onclick="confirmDeletion(this.form, {typeName: 'la relance', objName: 'pour le patient {{$relance->_ref_patient|JSAttribute}}'}, Control.Modal.close)">{{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>