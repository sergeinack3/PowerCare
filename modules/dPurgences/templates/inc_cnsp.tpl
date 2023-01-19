{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  chooseUFSoinsCNSP = function(form, callback) {
    new Url("urgences", "ajax_choose_uf_soins")
      .addParam("sejour_id", $V(form.sejour_id))
      .addParam("type_sejour", $V(form.type))
      .addParam("callback", callback)
      .addParam("UHCD", 0)
      .requestModal(500, 380);
  };

  validateUFSoinsCNSP = function(form) {
    var csnp = getForm("editPassageCNSPH");
    ['affectation_id', 'lit_id', 'date_aff',
     'uf_soins_id', 'uf_medicale_id', 'charge_id',
     'mode_entree', 'mode_entree_id', 'provenance'].each(function (_field) {
      if (form.elements[_field]) {
        $V(csnp.elements[_field], $V(form.elements[_field]));
      }
    });

    csnp.submit();
  };
</script>

{{assign var=required_uf_soins     value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=required_uf_med       value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=use_cpi               value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}

<!--  Passage en CNSPH / Revenir en dossier d'urgences -->
<form name="editPassageCNSPH" method="post">
  <input type="hidden" name="m" value="urgences" />
  <input type="hidden" name="dosql" value="do_cnsp_hospitalisation" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="affectation_id" />
  <input type="hidden" name="lit_id" />
  <input type="hidden" name="charge_id" />
  <input type="hidden" name="mode_entree" />
  <input type="hidden" name="mode_entree_id" />
  <input type="hidden" name="uf_medicale_id" />
  <input type="hidden" name="uf_soins_id" />
  <input type="hidden" name="provenance" />
  <input type="hidden" name="date_aff" />

  <input type="hidden" name="postRedirect" value="m=urgences&{{if $tab_mode}}tab{{else}}dialog{{/if}}=edit_consultation{{if $redirect_synthese}}&synthese_rpu=1{{/if}}" />

  {{if $sejour->type == "consult"}}
    <input type="hidden" name="type" value="comp" />
    <button class="hslip singleclick" type="button" onclick="
      Urgences.callback_cnsph = Urgences.modalSortie.curry(ContraintesRPU.checkObligatory.curry('{{$rpu->_id}}', getForm('editSejour'), (function () {
      chooseUFSoinsCNSP(this.form, 'validateUFSoinsCNSP');
      }).bind(this)));

        Urgences.callback_cnsph();
      ">
      {{tr}}CRPU-Move to Hospitalisation{{/tr}}
    </button>
  {{else}}
    <input type="hidden" name="type" value="consult" />
    <button class="hslip singleclick" type="button" onclick="Urgences.modalSortie(ContraintesRPU.checkObligatory.curry('{{$rpu->_id}}', getForm('editSejour'), (function () {
        chooseUFSoinsCNSP(this.form, 'validateUFSoinsCNSP');
      }).bind(this)));">
      {{tr}}CRPU-Come back from CSNPH{{/tr}}
    </button>
  {{/if}}
</form>

