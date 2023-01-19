{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=affectation_uf ajax=true}}

{{mb_include module=hospi template=inc_alerte_ufs object=$affectation}}

{{if $see_validate && "dPplanningOp CSejour use_charge_price_indicator"|gconf === "obl"}}
  <fieldset>
    <legend>{{mb_label object=$sejour field=charge_id}}</legend>

    <form name="alterChargeId" method="post" onsubmit="return onSubmitFormAjax(this);">
      {{mb_class object=$sejour}}
      {{mb_key   object=$sejour}}

      <table class="form">
        <tr>
          <td>
            <select name="charge_id" onchange="this.form.onsubmit();">
              {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:null item=_charge}}
                <option value="{{$_charge->_id}}" {{if $sejour->charge_id === $_charge->_id}}selected{{/if}}>{{$_charge}}</option>
              {{/foreach}}
            </select>
          </td>
        </tr>
      </table>
    </form>
  </fieldset>
{{/if}}

<form name="affect_uf" id="affecter_uf" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);"
      style="text-align:left;">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />

  {{mb_key object=$affectation}}

  <table class="main">
    <tr>
      <td>
        <fieldset>
          <legend>
            <i class="me-icon search me-white" onclick="$(this).up('fieldset').down('tbody').toggle();"></i>
            {{mb_label class=CAffectation field=uf_hebergement_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=hebergement}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$sejour  ufs=$uf_sejour_hebergement}}
            {{mb_include template=inc_vw_ufs_object object=$service ufs=$ufs_service.$context}}
            {{mb_include template=inc_vw_ufs_object object=$chambre ufs=$ufs_chambre.$context}}
            {{mb_include template=inc_vw_ufs_object object=$lit     ufs=$ufs_lit.$context    }}
            </tbody>

            {{mb_include template=inc_options_ufs_context ufs=$ufs_hebergement}}
          </table>
        </fieldset>
      </td>

      <td>
        <fieldset>
          <legend>
            <i class="me-icon search me-white" onclick="$(this).up('fieldset').down('tbody').toggle();"></i>
            {{mb_label class=CAffectation field=uf_soins_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=soins}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$sejour  ufs=$uf_sejour_soins}}
            {{mb_include template=inc_vw_ufs_object object=$service ufs=$ufs_service.$context}}
            {{mb_include template=inc_vw_ufs_object object=$chambre ufs=$ufs_chambre.$context}}
            {{mb_include template=inc_vw_ufs_object object=$lit     ufs=$ufs_lit.$context    }}
            </tbody>

            {{mb_include template=inc_options_ufs_context ufs=$ufs_soins}}
          </table>
        </fieldset>
      </td>

      <td>
        <fieldset>
          <legend>
            <i class="me-icon search me-white" onclick="$(this).up('fieldset').down('tbody').toggle();"></i>
            {{mb_label class=CAffectation field=uf_medicale_id}}
          </legend>
          <table class="form me-no-box-shadow">
            {{assign var=context value=medicale}}
            <tbody style="display: none;">
            {{mb_include template=inc_vw_ufs_object object=$sejour         ufs=$uf_sejour_medicale}}
            {{mb_include template=inc_vw_ufs_object object=$function       ufs=$ufs_function }}
            {{mb_include template=inc_vw_ufs_object object=$function       ufs=$ufs_function_second  uf_secondaire=true}}
            {{mb_include template=inc_vw_ufs_object object=$praticien      ufs=$ufs_praticien_sejour name="Praticien séjour"}}
            {{mb_include template=inc_vw_ufs_object object=$praticien      ufs=$ufs_praticien_sejour_second uf_secondaire=true}}
            {{mb_include template=inc_vw_ufs_object object=$prat_placement ufs=$ufs_prat_placement   name="Praticien placement"}}
            {{mb_include template=inc_vw_ufs_object object=$prat_placement ufs=$ufs_prat_placement   uf_secondaire=true}}
            </tbody>
            <tr>
              <th>{{tr}}CAffectation-praticien_id{{/tr}}</th>
              <td colspan="2" id="select_prat_uf_med">
                {{mb_include template=inc_vw_select_prat_uf}}
              </td>
            </tr>
            {{assign var=affectation_id value=$affectation->_id}}
            {{assign var=lit_id         value=$lit->_id}}
            {{mb_include template=inc_options_ufs_context ufs=$ufs_medicale
            callback_uf="AffectationUf.reloadPratUfMed(this, '$affectation_id', '$lit_id', '$see_validate')"}}
          </table>
        </fieldset>
      </td>
    </tr>
    {{if $see_validate}}
      <tr>
        <td class="button" colspan="3">
          <button class="submit" type="submit">{{tr}}Validate{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
</form>
