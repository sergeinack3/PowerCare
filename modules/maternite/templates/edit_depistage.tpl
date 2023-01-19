{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("Depistage-{{$depistage->_guid}}"),
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  refreshDespistages = function () {
    DossierMater.refresh();
    Control.Modal.close();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    includeForms();
    DossierMater.prepareAllForms();
  });
</script>

<form name="Depistage-{{$depistage->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$depistage}}
  {{mb_key   object=$depistage}}
  <input type="hidden" name="grossesse_id" value="{{$depistage->grossesse_id}}" />
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="_rewrite_custom" value="0" />
  <table class="main layout">
    <tr>
      <td colspan="2">
        <table class="form me-no-align me-no-box-shadow me-no-bg me-small-form">
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="save" onclick="submitAllForms(refreshDespistages);">
                {{tr}}common-action-Save and close{{/tr}}
              </button>
              <button type="button" class="close" onclick="Control.Modal.close();">
                {{tr}}Close{{/tr}}
              </button>
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=date}}</th>
            <td>{{mb_field object=$depistage field=date form=Depistage-`$depistage->_guid` register=true}}</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="halfPane">
        <table class="form me-small-form">
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-Immuno-hematology{{/tr}}</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=groupe_sanguin}}</th>
            <td>
              {{mb_field object=$depistage field=groupe_sanguin style="width: 12em;" emptyLabel="CDepistageGrossesse.groupe_sanguin."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rhesus}}</th>
            <td>{{mb_field object=$depistage field=rhesus style="width: 12em;" emptyLabel="CDepistageGrossesse.rhesus."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rhesus_bb}}</th>
            <td>{{mb_field object=$depistage field=rhesus_bb style="width: 12em;" emptyLabel="CDepistageGrossesse.rhesus."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rai}}</th>
            <td>{{mb_field object=$depistage field=rai style="width: 12em;" emptyLabel="CDepistageGrossesse.rai."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=test_kleihauer}}</th>
            <td>{{mb_field object=$depistage field=test_kleihauer emptyLabel="common-undefined"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=val_kleihauer}}</th>
            <td>{{mb_field object=$depistage field=val_kleihauer}} /ml</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_immuno}}</th>
            <td>{{mb_field object=$depistage field=rques_immuno}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-Serology{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=syphilis}}</th>
            <td>{{mb_field object=$depistage field=syphilis style="width: 12em;" emptyLabel="CDepistageGrossesse.syphilis."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=TPHA}}</th>
            <td>{{mb_field object=$depistage field=TPHA style="width: 12em;" emptyLabel="CDepistageGrossesse.cmvm."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=toxoplasmose}}</th>
            <td>
              {{mb_field object=$depistage field=toxoplasmose style="width: 12em;" emptyLabel="CDepistageGrossesse.toxoplasmose."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rubeole}}</th>
            <td>{{mb_field object=$depistage field=rubeole style="width: 12em;" emptyLabel="CDepistageGrossesse.rubeole."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=hepatite_b}}</th>
            <td>{{mb_field object=$depistage field=hepatite_b style="width: 12em;" emptyLabel="CDepistageGrossesse.hepatite_b."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=hepatite_c}}</th>
            <td>{{mb_field object=$depistage field=hepatite_c style="width: 12em;" emptyLabel="CDepistageGrossesse.hepatite_c."}}</td>
          </tr>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=vih}}</th>
            <td>{{mb_field object=$depistage field=vih style="width: 12em;" emptyLabel="CDepistageGrossesse.vih."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=parvovirus}}</th>
            <td>{{mb_field object=$depistage field=parvovirus style="width: 12em;" emptyLabel="CDepistageGrossesse.vih."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=cmvg}}</th>
            <td>{{mb_field object=$depistage field=cmvg style="width: 12em;" emptyLabel="CDepistageGrossesse.cmvg."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=cmvm}}</th>
            <td>{{mb_field object=$depistage field=cmvm style="width: 12em;" emptyLabel="CDepistageGrossesse.cmvm."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=varicelle}}</th>
            <td>{{mb_field object=$depistage field=varicelle style="width: 12em;" emptyLabel="CDepistageGrossesse.cmvm."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=htlv}}</th>
            <td>{{mb_field object=$depistage field=htlv style="width: 12em;" emptyLabel="CDepistageGrossesse.htlv."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_serologie}}</th>
            <td>{{mb_field object=$depistage field=rques_serologie}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">
              {{tr}}CDepistageGrossesse-Biochemistry{{/tr}} -
              {{tr}}CDepistageGrossesse-Hematology and Hemostasis{{/tr}}
            </th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=nfs_hb}}</th>
            <td>{{mb_field object=$depistage field=nfs_hb}} g/dl</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=gr}}</th>
            <td>{{mb_field object=$depistage field=gr}} /mm&sup3;</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=gb}}</th>
            <td>{{mb_field object=$depistage field=gb}} g/L</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=vgm}}</th>
            <td>{{mb_field object=$depistage field=vgm}} fL</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=ferritine}}</th>
            <td>{{mb_field object=$depistage field=ferritine}} µg/l</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=glycemie}}</th>
            <td>{{mb_field object=$depistage field=glycemie}} g/l</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=electro_hemoglobine_a1}}</th>
            <td>{{mb_field object=$depistage field=electro_hemoglobine_a1}} %</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=electro_hemoglobine_a2}}</th>
            <td>{{mb_field object=$depistage field=electro_hemoglobine_a2}} %</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=electro_hemoglobine_s}}</th>
            <td>{{mb_field object=$depistage field=electro_hemoglobine_s}} %</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=tp}}</th>
            <td>{{mb_field object=$depistage field=tp}} %</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=tca}}</th>
            <td>{{mb_field object=$depistage field=tca}} s</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=fg}}</th>
            <td>{{mb_field object=$depistage field=fg}} g/L</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=nfs_plaquettes}}</th>
            <td>{{mb_field object=$depistage field=nfs_plaquettes}} (x1000)/mm&sup3;</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=depistage_diabete}}</th>
            <td>{{mb_field object=$depistage field=depistage_diabete}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$depistage field=rques_biochimie}}</th>
            <td>{{mb_field object=$depistage field=rques_biochimie}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-urine{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=albuminerie}}</th>
            <td>{{mb_field object=$depistage field=albuminerie}} g/L</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=glycosurie}}</th>
            <td>{{mb_field object=$depistage field=glycosurie}} g/L</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=albuminerie_24}}</th>
            <td>{{mb_field object=$depistage field=albuminerie_24}} g/L</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=cbu}}</th>
            <td>{{mb_field object=$depistage field=cbu}}</td>
          </tr>
        </table>
      </td>
      <td class="halfPane">
        <table class="form me-small-form">
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-Vasculo-Renal Assessment{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=acide_urique}}</th>
            <td>{{mb_field object=$depistage field=acide_urique}} mg/24h</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=asat}}</th>
            <td>{{mb_field object=$depistage field=asat}} UI/l</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=alat}}</th>
            <td>{{mb_field object=$depistage field=alat}} UI/l</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=phosphatase}}</th>
            <td>{{mb_field object=$depistage field=phosphatase}} UI/l</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=brb}}</th>
            <td>{{mb_field object=$depistage field=brb}} {{mb_field object=$depistage field=unite_brb}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=sel_biliaire}}</th>
            <td>{{mb_field object=$depistage field=sel_biliaire}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=creatininemie}}</th>
            <td>{{mb_field object=$depistage field=creatininemie}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_bacteriologie}}</th>
            <td>{{mb_field object=$depistage field=rques_bacteriologie}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-1er trimestre{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=marqueurs_seriques_t21}}</th>
            <td>{{mb_field object=$depistage field=marqueurs_seriques_t21}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=dpni}}</th>
            <td>{{mb_field object=$depistage field=dpni emptyLabel="common-undefined"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=dpni_rques}}</th>
            <td>{{mb_field object=$depistage field=dpni_rques}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=pappa}}</th>
            <td>{{mb_field object=$depistage field=pappa}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=hcg1}}</th>
            <td>{{mb_field object=$depistage field=hcg1}} mUI/ml</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_t1}}</th>
            <td>{{mb_field object=$depistage field=rques_t1}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-2nd trimestre{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=afp}}</th>
            <td>{{mb_field object=$depistage field=afp}} ng/l</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=hcg2}}</th>
            <td>{{mb_field object=$depistage field=hcg2}} mUI/ml</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=estriol}}</th>
            <td>{{mb_field object=$depistage field=estriol}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_t2}}</th>
            <td>{{mb_field object=$depistage field=rques_t2}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}General{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=amniocentese}}</th>
            <td>{{mb_field object=$depistage field=amniocentese}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=pvc}}</th>
            <td>{{mb_field object=$depistage field=pvc}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_hemato}}</th>
            <td>{{mb_field object=$depistage field=rques_hemato}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CDepistageGrossesse-prelevement_vaginal{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=strepto_b}}</th>
            <td>{{mb_field object=$depistage field=strepto_b emptyLabel="common-undefined"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=parasitobacteriologique}}</th>
            <td>{{mb_field object=$depistage field=parasitobacteriologique emptyLabel="common-undefined"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$depistage field=rques_vaginal}}</th>
            <td>{{mb_field object=$depistage field=rques_vaginal}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}common-Other|pl{{/tr}}</th>
          </tr>
          <tr>
            <th class="section">{{tr}}common-Label{{/tr}}</th>
            <th class="section">{{tr}}common-Value{{/tr}}</th>
          </tr>
          {{foreach from=$depistage->_libelle_customs key=index item=_libelle}}
            <tr>
              <th>
                <input type="hidden" name="_depistage_custom_ids[{{$index}}]" value="{{$depistage->_depistage_custom_ids[$index]}}" />
                <input type="text" name="_libelle_customs[{{$index}}]" value="{{$_libelle}}" class="autocomplete" />
              </th>
              <td>
                <textarea name="_valeur_customs[{{$index}}]">{{$depistage->_valeur_customs[$index]}}</textarea>
              </td>
            </tr>
            <script>
              Main.add(function () {
                var element = $('Depistage-{{$depistage->_guid}}__libelle_customs[{{$index}}]');
                var url = new Url("maternite", "ajax_depistage_custom_autocomplete");
                url.autoComplete(element, null, {
                  minChars:     3,
                  method:       "get",
                  valueElement: element,
                  dropdown:     true
                });
              });
            </script>
          {{/foreach}}
        </table>
      </td>
    </tr>
  </table>
</form>
