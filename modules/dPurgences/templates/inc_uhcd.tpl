{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  chooseUFSoins = function(form, UHCD, callback) {
    new Url("urgences", "ajax_choose_uf_soins")
      .addParam("sejour_id", $V(form.sejour_id))
      .addParam("callback", callback)
      .addParam("UHCD", UHCD)
      .requestModal(500, 380);
  };

  validateUFSoins = function(form) {
    var uhcd = getForm("editPassageUHCD");
    ['affectation_id', 'lit_id', 'date_aff',
     'uf_soins_id', 'uf_medicale_id', 'charge_id',
     'mode_entree', 'mode_entree_id', 'provenance'].each(function (_field) {
      if (form.elements[_field]) {
        $V(uhcd.elements[_field], $V(form.elements[_field]));
      }
    });

    uhcd.submit();
  };
</script>

{{assign var=required_uf_soins     value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=required_uf_med       value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=use_cpi               value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=criteres_passage_uhcd value="dPurgences CRPU criteres_passage_uhcd"|gconf}}

<!--  Passage en UHCD / Revenir en dossier d'urgences -->
<form name="editPassageUHCD" method="post" action="?m={{$m}}">
  <input type="hidden" name="m" value="urgences" />
  <input type="hidden" name="dosql" value="do_uhcd_atu" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="affectation_id" />
  <input type="hidden" name="lit_id" />
  <input type="hidden" name="mode_entree" />
  <input type="hidden" name="mode_entree_id" />
  <input type="hidden" name="provenance" />
  <input type="hidden" name="date_aff" />

  <input type="hidden" name="postRedirect" value="m=urgences&{{if $tab_mode}}tab{{else}}dialog{{/if}}=edit_consultation{{if $redirect_synthese}}&synthese_rpu=1{{/if}}" />

  {{if $required_uf_soins != "no"}}
    {{mb_field object=$sejour field=uf_soins_id hidden=true}}
  {{/if}}

  {{if $required_uf_med != "no"}}
    {{mb_field object=$sejour field=uf_medicale_id hidden=true}}
  {{/if}}

  {{if $use_cpi != "no"}}
    {{mb_field object=$sejour field=charge_id hidden=true}}
  {{/if}}

  {{if !$sejour->UHCD}}
    <input type="hidden" name="UHCD" value="1" />
    <input type="hidden" name="type" value="comp" />
    <button class="hslip singleclick" type="button" onclick="
      Urgences.callback_uhcd = Urgences.modalSortie.curry(ContraintesRPU.checkObligatory.curry('{{$rpu->_id}}', getForm('editSejour'), (function () {
        chooseUFSoins(this.form, 1, 'validateUFSoins');
      }).bind(this)));

      {{if $criteres_passage_uhcd}}
        Urgences.verifyNbInscription('{{$rpu->_id}}', 'Urgences.criteresUHCD.curry(\'{{$rpu->_id}}\')');
      {{else}}
        Urgences.verifyNbInscription('{{$rpu->_id}}', 'Urgences.callback_uhcd');
      {{/if}}
      ">
      {{tr}}CRPU-Move to UHCD{{/tr}}
    </button>
  {{else}}
    <input type="hidden" name="UHCD" value="0" />
    <input type="hidden" name="type" value="{{if "dPurgences CRPU type_sejour"|gconf === "urg_consult"}}consult{{else}}urg{{/if}}" />
    <button class="hslip singleclick" type="button" onclick="Urgences.modalSortie(ContraintesRPU.checkObligatory.curry('{{$rpu->_id}}', getForm('editSejour'), (function () {
        chooseUFSoins(this.form, 0, 'validateUFSoins');
      }).bind(this)));">
      {{tr}}CRPU-Come back from UHCD{{/tr}}
    </button>
  {{/if}}
</form>

