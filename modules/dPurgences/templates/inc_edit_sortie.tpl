{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=atu value=$sejour->_ref_consult_atu}}
<script>
  submitSejour = function(force) {
    if (!force) {
      return null;
    }

    var form = getForm('editSejour');
    return onSubmitFormAjax(form, { onComplete: function() {
      {{if $atu->_id && $conf.dPurgences.valid_cotation_sortie_reelle}}
        onSubmitFormAjax(getForm('ValidCotation'), { onComplete: function() {
          Sortie.refresh('{{$rpu->_id}}');
          Sortie.close();
        }});
      {{else}}
        Sortie.refresh('{{$rpu->_id}}');
        Sortie.close();
      {{/if}}
    }});
  };

  Fields = {
    init: function(mode_sortie) {
      $('etablissement_sortie_transfert').setVisible(mode_sortie == "transfert");
      if($('lit_sortie_transfert')) {
        $('lit_sortie_transfert').setVisible(mode_sortie == "mutation");
        $('service_sortie_transfert').setVisible(mode_sortie == "mutation");
      }
      else{
        $('service_sortie_transfert').setVisible(mode_sortie == "mutation");
      }
      $('date_deces').setVisible(mode_sortie === "deces");
      var date_deces = getForm("editSejour")._date_deces;
      if (mode_sortie != "deces") {
        $V(date_deces, "", false);
        $V(date_deces.previous().down("input"), "", false);
        date_deces.removeClassName("notNull");
      }
    },

    modif: function(lit_id) {
      var form = getForm('editSejour');
      $('service_sortie_transfert').setVisible(lit_id);

      var service = $('CLit-'+lit_id).className;
      service = service.split("-");
      form.service_sortie_id.value = service[1];

      form.service_sortie_id_autocomplete_view.value = service[2];
    },

    clear: function() {
      if (confirm($T('CSejour-sortie-confirm-clearall'))) {
        var form = getForm('editSejour');
        form.mode_sortie.clear();
        if (form.sortie_reelle) {
          form.sortie_reelle.clear();
        }
        if (form.sortie_reelle_da) {
          form.sortie_reelle_da.clear();
        }
        form.etablissement_sortie_id_autocomplete_view.clear();
        form.etablissement_sortie_id.clear();
        form.service_sortie_id_autocomplete_view.clear();
        form.service_sortie_id.clear();
        form.commentaires_sortie.clear();

        submitSejour(true);
      }
    }
  }
</script>
{{mb_script module=dPurgences script=contraintes_rpu ajax=true}}
{{mb_include template=inc_form_sortie}}

<form name="editRPU" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$rpu}}
  {{mb_key   object=$rpu}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_bind_sejour" value="1" />
  <table class="form">
    <tr>
      <th style="width: 30%;">{{mb_label object=$rpu field="_destination"}}</th>
      <td>{{mb_field object=$rpu field="_destination" emptyLabel="CRPU-_destination" onchange="this.form.onsubmit()"}}<br /></td>
    </tr>
    <tr>
      <th>{{mb_label object=$rpu field="orientation"}}</th>
      <td>{{mb_field object=$rpu field="orientation"  emptyLabel="CRPU-orientation"  onchange="this.form.onsubmit()"}}</td>
    </tr>
  </table>
</form>

<table class="form">
  <tr>
    <td class="button">
      <button class="cancel singleclick" onclick="Fields.clear();">
        {{tr}}Cancel{{/tr}}
        {{mb_label object=$sejour field=sortie}}
      </button>
      <button class="save singleclick" onclick="ContraintesRPU.checkObligatory({{$rpu->_id}}, getForm('editSejour'), submitSejour.curry(true));">
        {{tr}}Validate{{/tr}}
        {{mb_label object=$sejour field=sortie}}
      </button>
    </td>
  </tr>
</table>

{{if $atu->_id && $conf.dPurgences.valid_cotation_sortie_reelle}}
  <form name="ValidCotation" action="" method="post" onsubmit="return onSubmitFormAjax(this)">
    <input type="hidden" name="dosql" value="do_consultation_aed" />
    <input type="hidden" name="m" value="dPcabinet" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="consultation_id" value="{{$atu->_id}}" />
    <input type="hidden" name="valide" value="1" />
  </form>
{{/if}}