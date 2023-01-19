{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=sejour_maman value=""}}

<script>
  checkCut = function (elt) {
    if (elt.value == $V(elt.form.entree)) {
      $('cut_affectation').disabled = 'disabled'
    } else {
      $('cut_affectation').disabled = '';
    }
  };

  addUfs = function () {
    var form = getForm("affect_uf");
    var form2 = getForm("cutAffectation");
    form2.uf_hebergement_id.value = form.uf_hebergement_id.value;
    form2.uf_medicale_id.value = form.uf_medicale_id.value;
    form2.uf_soins_id.value = form.uf_soins_id.value;
  };

  {{if $from_tempo}}
  Main.add(function () {
    var dates = {
      limit: {
        start: "{{$affectation->entree}}",
        stop:  "{{$affectation->sortie}}"
      }
    };
    Calendar.regField(getForm('cutAffectation')._date_cut, dates, {timePicker: true});
  });
  {{/if}}

  liaisonMaman = function (status, parent_affectation_id, datetime) {
    var oForm = getForm('cutAffectation');
    if (status && parent_affectation_id) {
      changeLit('{{$affectation->_id}}', 1, datetime);
      return;
    }
    oForm.onsubmit();
    Control.Modal.close();
  };

  submitLiaison = function (lit_id, uf_hebergement_id, uf_medicale_id, uf_soins_id) {
      var oForm = getForm('cutAffectation');
      $V(oForm.lit_id, lit_id);
      $V(oForm.uf_hebergement_id, uf_hebergement_id);
      $V(oForm.uf_medicale_id, uf_medicale_id);
      $V(oForm.uf_soins_id, uf_soins_id);
      oForm.onsubmit();
  };

  refreshNewLit = function (id, obj) {
    Control.Modal.close();
    callback = obj._ask_etab_externe ? openModalEtab.curry(id) : Prototype.emptyFunction;
    refreshMouvements(callback, obj.lit_id);
  };

  selectService = function () {
    var url = new Url("hospi", "ajax_select_service");
    url.requestModal('95%', null, {maxWidth: '100%', maxHeight: '100%'});
  };
</script>

<table class="form">
  <tr>
    <th class="category" colspan="4">{{tr}}CAffectation-Modification of the current assignment{{/tr}}</th>
  </tr>
  <tr>
    <td id="ufs_affectation" colspan="2">
      <script>
        Main.add(function () {
          new Url("hospi", "ajax_vw_association_uf")
            .addParam("curr_affectation_id", '{{$affectation->_id}}')
            .addParam("lit_id", '{{$affectation->lit_id}}')
            .addParam("see_validate", 0)
            .requestUpdate('ufs_affectation');
        });
      </script>
    </td>
  </tr>
  {{if $from_tempo}}
    <tr>
      <th class="category" colspan="4">{{tr}}CAffectation-Anticipation of the patient s movements{{/tr}}</th>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <form name="cutAffectation" method="post" action="?"
              onsubmit="{{if "dPplanningOp CSejour use_uf_sejour_to_affectation"|gconf == "all"}}addUfs();{{/if}}
                return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="hospi" />
          <input type="hidden" name="dosql" value="do_cut_affectation_aed" />
          <input type="hidden" name="lit_id" value="{{$lit_id}}" />
          <input type="hidden" name="entree" value="{{$affectation->entree}}" />
          <input type="hidden" name="uf_hebergement_id" value="" />
          <input type="hidden" name="uf_medicale_id" value="" />
          <input type="hidden" name="uf_soins_id" value="" />
          <input type="hidden" name="callback" value="refreshNewLit" />
          <input type="hidden" name="service_id" value="" />
          {{mb_key object=$affectation}}
          <input type="text" name="_date_cut_da" value="{{$dtnow|date_format:$conf.datetime}}" readonly="readonly" />
          <input type="hidden" name="_date_cut" class="dateTime" value="{{$dtnow|@date_format:"%Y-%m-%d %H:%M:%S"}}"
                 onchange="checkCut(this)" />
          <button type="button" class="hslip"
                  onclick="
                  {{if "maternite"|module_active && $sejour_maman}}
                    liaisonMaman(this.form._action_maman.checked, '{{$affectation->parent_affectation_id}}', '{{$dtnow|@date_format:"%Y-%m-%d %H:%M:%S"}}')
                  {{elseif !$lit_id}}
                    changeLit('{{$affectation->_id}}', 1, $V(this.form._date_cut));
                  {{else}}
                    this.form.onsubmit();
                  {{/if}}" id="cut_affectation">{{tr}}CAffectation-Create movement{{/tr}}</button>
          {{if "maternite"|module_active && $sejour_maman}}
            <label>
              <input type="checkbox" name="_action_maman" checked="checked" />
              {{if $affectation->parent_affectation_id}}Détacher de{{else}}Attacher à {{/if}} la maman ({{$sejour_maman->_ref_patient}}
              )
            </label>
          {{/if}}
          {{if !$sejour_maman}}
            <button type="button" class="hslip" onclick="selectService()">{{tr}}CAffectation-Create movement corridor{{/tr}}</button>
          {{/if}}
        </form>
        <br />
      </td>
    </tr>
  {{/if}}
</table>
