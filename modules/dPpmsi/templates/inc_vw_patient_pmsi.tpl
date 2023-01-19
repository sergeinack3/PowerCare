{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td>
      {{mb_include module=patients template=inc_vw_identite_patient}}
      <!-- Champ C2S, ALD et ACS -->
      <table class="main form">
        <tr>
          <th class="category">{{tr}}CPatient-C2S_ALD_ACS{{/tr}}</th>
        </tr>
        <tr>
          <td>
            <form name="patAldForm" method="post" onsubmit="return onSubmitFormAjax(this)">
              <input type="hidden" name="m" value="dPpatients" />
              <input type="hidden" name="dosql" value="do_patients_aed" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="patient_id" value="">
              <input type="hidden" name="ald" value="">
              <input type="hidden" name="c2s" value="">
              <input type="hidden" name="acs" value="">
            </form>
            <form name="editSejour" method="post" onsubmit="return onSubmitFormAjax(this)">
              <input type="hidden" name="m" value="planningOp">
              <input type="hidden" name="dosql" value="do_sejour_aed">
              <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}">
              {{mb_key object=$sejour}}
              <table class="">
                {{mb_include module=planningOp template=inc_check_ald patient=$patient onchange="this.form.onsubmit()"}}
              </table>
            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="vertical-align: top; height: 100%;">
        <table class="form">
          {{if ($conf.dPpmsi.systeme_facturation == "siemens") && $patient->_ref_IPP}}
            <tr>
              <th id="IPP">{{mb_include module=pmsi template=inc_ipp_form}}</th>
            </tr>
          {{/if}}

          {{if ($conf.dPpmsi.systeme_facturation == "siemens") && $sejour->_id}}
            <tr>
              <td id="Numdos{{$sejour->sejour_id}}" class="text">
                {{mb_include module=pmsi template=inc_numdos_form}}
              </td>
            </tr>
          {{/if}}
        </table>
    </td>
  </tr>
</table>
