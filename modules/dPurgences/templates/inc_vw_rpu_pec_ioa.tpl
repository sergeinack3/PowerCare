{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=submit_ajax value=""}}

{{assign var=is_infirmiere value=$app->_ref_user->isInfirmiere()}}

<div id="pec_ioa_reload">
  <script>
    HorodatageIoa = {
      onSubmit:  function(form) {
        {{if $is_infirmiere}}
        if ($V(form.pec_ioa)) {
          $V(form.ioa_id, User.id);
        }
        {{/if}}
        if ($V(form.pec_ioa) && !$V(form.ioa_id)) {
          alert("Veuillez choisir l'IOA qui prend en charge le patient.");
          return;
        }
        return onSubmitFormAjax(form, HorodatageIoa.reload.curry(form));
      },
      reload: function(form) {
        new Url("urgences", "ajax_vw_pec_ioa")
          .addParam("rpu_id", $V(form.rpu_id))
          .addParam("submit_ajax", "{{$submit_ajax}}")
          .requestUpdate('pec_ioa_reload');
      }
    }
  </script>

  <script>
    Main.add(function() {
      var form = getForm("editRPU");
      new Url("urgences", "ajax_ide_responsable_autocomplete")
        .addParam("field", "ioa_id_view")
        .autoComplete(form.ioa_id_view, null, {
          minChars: 2,
          method: "get",
          select: "view",
          dropdown: true,
          updateElement: function(selected) {
            var id = selected.get("id");
            $V(form.ioa_id, id, !!$V(form.pec_ioa));
            $V(form.ioa_id_view, selected.get("name"));
          }.bind(form)
        });
    });
  </script>

  <input type="text" name="ioa_id_view" class="autocomplete"
         value="{{if !$rpu->ioa_id && $is_infirmiere}}{{$app->_ref_user}}{{else}}{{$rpu->_ref_ioa}}{{/if}}"
         placeholder="&mdash; {{tr}}Choose{{/tr}}"/>

  {{mb_field object=$rpu field="ioa_id" hidden=true onchange=$submit_ajax}}

  {{if $rpu->ioa_id}}
    {{mb_field object=$rpu field="pec_ioa" form="editRPU" register=true
               onchange="if (!\$V(this)) { \$V(this.form.ioa_id, '', false); } HorodatageIoa.onSubmit(this.form)"}}
    <button type="button" class="cancel notext"
            onclick="$V(this.form.pec_ioa,'', false); $V(this.form.ioa_id,'', false); HorodatageIoa.onSubmit(this.form);">{{tr}}Cancel{{/tr}}</button>
  {{else}}
    {{mb_field object=$rpu field="pec_ioa" hidden=true}}

    <button type="button" class="submit"
            onclick="$V(this.form.pec_ioa, 'now'); HorodatageIoa.onSubmit(this.form);">
      Prise en charge IOA
    </button>
  {{/if}}
</div>
