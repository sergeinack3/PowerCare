{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback_texte_libre value=""}}

<script>
  onSubmitTraitement = function(form) {
    var trait = $(form.traitement);
    if (!trait.present()) {
      return false;
    }

    onSubmitFormAjax(form, {
      onComplete : function() {
        {{if $type_see}}
        DossierMedical.reloadDossierPatient(null, '{{$type_see}}');
        {{elseif $callback_texte_libre}}
          {{$callback_texte_libre}}();
        {{else}}
        DossierMedical.reloadDossiersMedicaux();
        {{/if}}
      }
    } );

    trait.clear().focus();

    return false;
  };
</script>

<form name="editTrmtFrm{{$addform}}" action="?m=cabinet" method="post" onsubmit="return onSubmitTraitement(this);">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_traitement_aed" />
  <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />

  {{if $_is_anesth}}
    <!-- On passe _sejour_id seulement s'il y a un sejour_id -->
    <input type="hidden" name="_sejour_id" value="{{$sejour_id}}" />
  {{/if}}

  <table class="layout">
    <tr>
      {{if $app->user_prefs.showDatesAntecedents}}
        {{me_form_field nb_cells=2 mb_object=$traitement mb_field="debut"}}
          {{mb_field object=$traitement field=debut form=editTrmtFrm$addform register=true}}
        {{/me_form_field}}
      {{else}}
        <td colspan="2"></td>
      {{/if}}
      {{me_form_field nb_cells=2 style_css="width: 100%"}}
        {{mb_field object=$traitement field=traitement rows=4 form=editTrmtFrm$addform
        aidesaisie="validateOnBlur: 0"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{if $app->user_prefs.showDatesAntecedents}}
        {{me_form_field nb_cells=2 mb_object=$traitement mb_field="fin"}}
          {{mb_field object=$traitement field=fin form=editTrmtFrm$addform register=true}}
        {{/me_form_field}}
      {{else}}
        <td colspan="2"></td>
      {{/if}}
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button class="tick me-primary" {{if !$patient->canEdit()}}disabled{{/if}}>
          {{tr}}CPrescriptionLineMedicament-action-add-traitement{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>