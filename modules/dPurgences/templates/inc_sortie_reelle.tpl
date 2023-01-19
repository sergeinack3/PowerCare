{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
autoriserSortie = function(value){
  var form = getForm('editSortieAutorise');

  if (!checkForm(getForm('editRPU'))) {
    return false;
  }

  form.elements.sortie_autorisee.value = value;
  onSubmitFormAjax(getForm('editSejour'), function(){
    submitRPU();
  });
};

autoriserEffectuerSortie = function() {
  if (!checkForm(getForm('editRPU'))) {
    return false;
  }

  getForm('editSortieAutorise').elements.sortie_autorisee.value = 1;

  let callback = () => {
    if (!Urgences.from_synthese) {
      Admissions.validerSortie('{{$rpu->sejour_id}}', false, Urgences.reloadSortieReelle);
    }
  };

  return onSubmitFormAjax(getForm('editSejour'), function(){
    {{if $conf.dPurgences.valid_cotation_sortie_reelle}}
      return onSubmitFormAjax(getForm('ValidCotation'), function(){
        submitSejRpuConsult(callback);
        $('button_reconvoc').disabled = null;
      });
    {{else}}
      $('button_reconvoc').disabled = null;
      return submitSejRpuConsult(callback);
    {{/if}}
  });
}
</script>

<form name="editSortieReelle" method="post" action="?m={{$m}}">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$sejour}}
  {{if $sejour->sortie_reelle && $rpu->sortie_autorisee}}
    {{tr}}CRPU-sortie_assuree.1{{/tr}} à 
    {{mb_field object=$sejour field="sortie_reelle" register=true form="editSortieReelle"
               onchange="onSubmitFormAjax(this.form, (function() { if (!this.value) { Urgences.reloadSortieReelle(); } }).bind(this));"}}
    {{mb_field object=$sejour field=entree_reelle hidden=true}}
    <button class="cancel" type="button" onclick="autoriserSortie(0)">
      Annuler l'autorisation de sortie
    </button>
  {{else}}    
    {{if $rpu->sortie_autorisee}}
      <button class="cancel" type="button" onclick="autoriserSortie(0)">
        Annuler l'autorisation de sortie
      </button>
    {{else}}
      <button class="tick singleclick autoriser_sortie" type="button"
              onclick="Urgences.verifyNbInscription('{{$rpu->_id}}', 'ContraintesRPU.checkObligatory.curry(\'{{$rpu->_id}}\', getForm(\'editSejour\'), autoriserSortie.curry(1))');">
        {{mb_label object=$rpu field="sortie_autorisee"}}
      </button>
      <input type="hidden" name="date_sortie_aut" value="now" />

      {{if !$sejour->sortie_reelle}}
        {{if $rpu->sejour_id != $rpu->mutation_sejour_id && "dPurgences CRPU display_aut_eff_sortie_button"|gconf}}
          <input type="hidden" name="sortie_reelle" value="now" />
          <button class="tick singleclick autoriser_sortie" type="button"
                  onclick="Urgences.verifyNbInscription('{{$rpu->_id}}', 'Urgences.modalSortie.curry(ContraintesRPU.checkObligatory.curry(\'{{$rpu->_id}}\', getForm(\'editSejour\'), autoriserEffectuerSortie.curry()))');">
            Autoriser et effectuer la sortie
          </button>
        {{/if}}
      {{else}}
        Sortie à 
          {{mb_field object=$sejour field="sortie_reelle" register=true form="editSortieReelle" 
            onchange="onSubmitFormAjax(this.form, Urgences.reloadSortieReelle);"}}
      {{/if}}
    {{/if}}
  {{/if}}
</form>

<form name="ValidCotation" action="" method="post" onsubmit="return onSubmitFormAjax(this)"> 
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="consultation_id" value="{{$consult->_id}}" />
  <input type="hidden" name="valide" value="1" />
</form>
