{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="main">
  <tr>
    <td colspan="2" class="button">
      <button type="button" class="add not-printable me-small" onclick="DossierMater.addDepistage(null, '{{$grossesse->_id}}');">
        {{tr}}Add{{/tr}} {{tr}}CDepistageGrossesse.one{{/tr}}
      </button>
      <button type="button" class="close not-printable me-small" id="close_dossier_perinat" onclick="Control.Modal.close();">
        {{tr}}Close{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-Immuno-hematology{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=groupe_sanguin}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rhesus}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rhesus_bb}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rai}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=test_kleihauer}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=val_kleihauer unite=" /ml"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_immuno}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-Serology{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=syphilis}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=TPHA}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=toxoplasmose}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rubeole}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hepatite_b}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hepatite_c}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=vih}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=parvovirus}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=cmvg}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=cmvm}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=varicelle}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=htlv}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_serologie}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0  me-small  me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-Biochemistry{{/tr}} - {{tr}}CDepistageGrossesse-Hematology and Hemostasis{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=nfs_hb unite=" g/dl"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=gr unite=" /mm³"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=gb unite=" g/L"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=vgm unite=" fL"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=ferritine unite=" µg/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=glycemie unite=" g/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_a1 unite=" %"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_a2 unite=" %"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=electro_hemoglobine_s unite=" %"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=tp unite=" %"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=tca unite=" s"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=fg unite=" g/L"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=nfs_plaquettes unite=" (x1000)/mm³"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=depistage_diabete}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_biochimie}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-urine{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=albuminerie unite=" g/L"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=glycosurie unite=" g/L"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=albuminerie_24 unite=" g/L"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=cbu}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-Vasculo-Renal Assessment{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=acide_urique unite=" mg/24h"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=asat unite=" UI/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=alat unite=" UI/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=phosphatase unite=" UI/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=brb}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=sel_biliaire}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=creatininemie}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_bacteriologie}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-1er trimestre{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=marqueurs_seriques_t21}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=dpni}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=dpni_rques}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=pappa}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hcg1 unite=" mUI/ml"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_t1}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-2nd trimestre{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=afp unite=" ng/l"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=hcg2 unite=" mUI/ml"}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=estriol}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_t2}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}General{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=amniocentese}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=pvc}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_hemato}}
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-prelevement_vaginal{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=strepto_b}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=parasitobacteriologique}}
          {{mb_include module=maternite template=depistages/inc_depistage_line_cte cte=rques_vaginal}}
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset class="me-padding-bottom-0 me-small me-margin-2">
        <legend>{{tr}}CDepistageGrossesse-Custom screening|pl{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow me-small-tbl">
          {{mb_include module=maternite template=depistages/inc_depistage_line_headers}}
          {{foreach from=$depistage_field_customs key=index item=_depistage_field}}
            <tr>
              <td style="text-align: right;">
                <label for="{{$index}}">{{$index}}</label>
              </td>
              {{foreach from=$_depistage_field key=_key item=_field}}
                <td class="text">
                  {{$_field}}
                </td>
              {{/foreach}}
            </tr>
          {{/foreach}}
        </table>
      </fieldset>
    </td>
  </tr>
</table>
