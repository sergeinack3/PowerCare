{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editScoreApfel" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
  <input type="hidden" name="callback" value="afterStoreScore" />
  {{mb_key object=$consult_anesth}}

  <div style="height: 90%;">
    <label title="Calculé en cliquant sur Evaluer">
      <input type="checkbox" onclick="$V(this.form.apfel_femme, this.checked ? 1 : 0); this.form.onsubmit();"
             name="_apfel_femme_view" {{if $consult_anesth->apfel_femme}}checked="checked"{{/if}}/> Femme
      <input type="hidden" name="apfel_femme" value="{{$consult_anesth->apfel_femme}}" />
    </label> <br />
    <label title="Calculé en cliquant sur Evaluer (codes cim10 détectés : F17 / T652 / Z720 / Z864 / Z587">
      <input type="checkbox" onclick="$V(this.form.apfel_non_fumeur, this.checked ? 1 : 0); this.form.onsubmit();"
             name="_apfel_non_fumeur_view" {{if $consult_anesth->apfel_non_fumeur}}checked="checked"{{/if}}/> Non fumeur
      <input type="hidden" name="apfel_non_fumeur" value="{{$consult_anesth->apfel_non_fumeur}}" />
    </label> <br />
    <label title="Non calculé">
      <input type="checkbox" onclick="$V(this.form.apfel_atcd_nvp, this.checked ? 1 : 0); this.form.onsubmit();"
             name="_apfel_atcd_nvp_view" {{if $consult_anesth->apfel_atcd_nvp}}checked="checked"{{/if}}/> Antécédents de NVPO
      <input type="hidden" name="apfel_atcd_nvp" value="{{$consult_anesth->apfel_atcd_nvp}}" />
    </label> <br />
    <label title="Non calculé">
      <input type="checkbox" onclick="$V(this.form.apfel_morphine, this.checked ? 1 : 0); this.form.onsubmit();"
             name="_apfel_morphine_view" {{if $consult_anesth->apfel_morphine}}checked="checked"{{/if}}/> Morphine
      <input type="hidden" name="apfel_morphine" value="{{$consult_anesth->apfel_morphine}}" />
    </label>
  </div>

  <div style="text-align: right; padding: 5px;">
    <strong>Score: <span id="score_apfel">{{$consult_anesth->_score_apfel}}</span> </strong>
  </div>
</form>