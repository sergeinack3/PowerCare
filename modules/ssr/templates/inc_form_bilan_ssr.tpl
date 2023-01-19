{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td style="width: 60%">
      {{if "dPprescription"|module_active}}
      {{mb_include module=ssr template=inc_form_prescription_ssr}}
      {{else}}
      <div class="small-warning">
        <div>{{tr}}ssr-param_prescription_no_acces{{/tr}}</div>
        <div>{{tr}}ssr-prescription_reeduc_no_acces{{/tr}}</div>
      </div>
      {{/if}}
    </td>

    <td>
      <form name="Edit-CBilanSSR" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="ssr" />
        <input type="hidden" name="dosql" value="do_bilan_ssr_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="callback" value="updateBilanId" />
        {{mb_key object=$bilan}}
        {{mb_field object=$bilan field=sejour_id hidden=1}}
        <table class="form">
          <tr>
            <th class="title" style="width: 50%">{{tr}}CBilan{{$m|strtoupper}}{{/tr}}</th>
          </tr>
          <tr>
            <td>
              <fieldset class="me-no-align me-no-box-shadow">
                <legend>{{mb_label object=$bilan field=entree}}</legend>
                {{mb_field object=$bilan field=entree rows=6 onchange="this.form.onsubmit()" form="Edit-CBilanSSR"}}
              </fieldset>
            </td>
          </tr>
          <tr>
            <td>
              <fieldset class="me-no-align me-no-box-shadow">
                <legend>{{mb_label object=$bilan field=sortie}}</legend>
                {{mb_field object=$bilan field=sortie rows=6 onchange="this.form.onsubmit()" form="Edit-CBilanSSR"}}
              </fieldset>
            </td>
          </tr>
        </table>
      </form>

      {{if $can->admin && $bilan->_id}}
        <hr class="me-no-display"/>
        <form name="Planification-CBilanSSR" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_bilan_ssr_aed" />
          <input type="hidden" name="del" value="0" />
          {{mb_key object=$bilan}}
          {{mb_field object=$bilan field=sejour_id hidden=1}}
          {{mb_field object=$bilan field=planification hidden=1}}
          <table class="form">
            <tr>
              <td class="button">
                {{if $bilan->planification}}
                <button type="button" class="cancel" onclick="$V(this.form.planification, '0'); this.form.submit();">
                  {{tr}}CBilan{{$m|strtoupper}}-planification-turn-off{{/tr}}
                </button>
                {{else}}
                <button type="button" class="change" onclick="$V(this.form.planification, '1'); this.form.submit();">
                  {{tr}}CBilan{{$m|strtoupper}}-planification-turn-on{{/tr}}
                </button>
                {{/if}}
              </td>
            </tr>
          </table>
        </form>
      {{/if}}

      <!-- Affichage des sejours SSR du patient -->
      <div id="sejours_ssr"></div>
    </td>
  </tr>
</table>