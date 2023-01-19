{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value='editFrm'}}
{{mb_default var=onchange value=""}}
{{mb_default var=plage_synchronized value=false}}
{{mb_default var=compact value=false}}

{{mb_script module=cabinet script=edit_consultation ajax=true}}
{{assign var=isCabinet value=$isCabinet|default:0}}


<tr>
  {{if !$compact}}
    <th>{{mb_label class=CConsultation field="categorie_id"}}</th>
  {{/if}}
  <td>
    <script>
      listCat = {{$listCat|@json}};

      reloadIcone = function (cat_id, updateFields) {
        var img = $('iconeBackground');
        var span = $('nb_groupe_seance');
        var groupe_seance_limit = $('groupe_seance_limit');

        var form = getForm('{{$form}}');
        if (!img) {
          return;
        }

        if (!listCat[cat_id]) {
          img.hide();
          span.hide();
        }
        else {
          img.show().src = "./modules/dPcabinet/images/categories/" + listCat[cat_id]['nom_icone'];

          if (listCat[cat_id]['seance'] == 1) {
            {{if !$categorie_id}}
              var url = new Url("dPcabinet", "ajax_count_groupe_seance");
              url.addParam("patient_id", $V(form.patient_id));
              url.addParam("cat_id", cat_id);
              url.requestJSON(function (data) {
                var nb_consult = data['nb_consult'];
                var cerfa      = data['cerfa_entente_prealable'];
                var isCabinet        = data['isCabinet'];

                var max_seances = listCat[cat_id]['max_seances'];
                var anticipation = listCat[cat_id]['anticipation'];

                span.show().innerHTML = (nb_consult == max_seances) ? "(" + nb_consult + "/" + max_seances + ") <div class='small-warning' style='display: inline-block;'>" + $T('CConsultationCategorie-msg-Maximum number of sessions reached for this patient') + "</div>" : "(" + nb_consult + "/" + max_seances + ")";

                var seuil = max_seances - anticipation;

                if (isCabinet && nb_consult >= seuil && nb_consult < max_seances && !cerfa) {
                  Consultation.checkSessionThreshold($V(form.consultation_id));
                }
                else if (isCabinet && nb_consult == max_seances && !cerfa) {
                  $$('select[name="categorie_id"] option[value="'+ cat_id +'"]')[0].disabled = true;
                  Consultation.checkSessionThreshold($V(form.consultation_id));
                }
              });
            {{else}}
              var nb_consult = listCat[cat_id]['nb_consult'];

              var max_seances  = listCat[cat_id]['max_seances'];
              var anticipation = listCat[cat_id]['anticipation'];
              var cerfa        = listCat[cat_id]['cerfa_entente_prealable'];
              var isCabinet    = {{$isCabinet}};

              span.show().innerHTML = "(" + nb_consult + "/" + max_seances + ")";

              var seuil = max_seances - anticipation;

              if (isCabinet && nb_consult >= seuil && $V(form.consultation_id) && !cerfa && nb_consult < max_seances) {
                Consultation.checkSessionThreshold($V(form.consultation_id));
              }
              else if (isCabinet && nb_consult == max_seances && !cerfa) {
                Consultation.checkSessionThreshold($V(form.consultation_id));
              }
            {{/if}}
          }
          else {
            span.hide();
          }

          if (updateFields) {
            $V(form.duree, listCat[cat_id]['duree']);
            if (listCat[cat_id]['commentaire']) {
              $V(form.rques, ($V(form.rques) ? $V(form.rques) + '\n' : '') + listCat[cat_id]['commentaire']);
            }
          }
        }
        $V(form.duree, listCat);
      };

      Main.add(function () {
        reloadIcone('{{$categorie_id}}', false);
        /* Renseignements des catégories pour les RDV multiples */
        var select = getForm('{{$form}}').elements['categorie_id'];
        if (select) {
          var cat_id = $V(getForm('{{$form}}').categorie_id);
          {{foreach from=1|range:$app->user_prefs.NbConsultMultiple-1 item=j}}
          var select_rdv = getForm('{{$form}}').elements['categorie_id_{{$j}}'];
          if (select_rdv) {
            select_rdv.childElements().each(function (option) {
              if ($V(option) != '') {
                option.remove();
              }
            });

            select.childElements().each(function (option) {
              select_rdv.insert(DOM.option({value: option.value, selected: option.value == cat_id}, option.innerHTML));
            });
          }
          {{/foreach}}
        }
      });
    </script>

    {{if !empty($categories|smarty:nodefaults)}}
      {{if $compact}}
        <div class="me-margin-top-4 me-form-group dirty">
      {{/if}}
    <select {{if $plage_synchronized}}disabled{{/if}} name="categorie_id" style="width: 15em;" onchange="reloadIcone(this.value, true);{{$onchange}}">
      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      {{foreach from=$categories item="categorie"}}
      <option class="categorieConsult" {{if $categorie_id == $categorie->_id}}selected{{/if}}
              style="background-image:url(./modules/dPcabinet/images/categories/{{$categorie->nom_icone|basename}});
                background-repeat:no-repeat;" value="{{$categorie->_id}}">{{$categorie->_view}} {{if $compact}} ({{if $categorie->function_id}}{{$categorie->_ref_function->_view}}{{elseif $categorie->praticien_id}}{{$categorie->_ref_praticien->_view}}{{/if}}){{/if}}
      </option>
      {{/foreach}}
    </select>
    <img id="iconeBackground" />
    <span id="nb_groupe_seance" title="{{tr}}CConsultationCategorie-Number of consultation in this session group|pl{{/tr}}"></span>

      {{if $compact}}
        {{mb_label class=CConsultation field="categorie_id"}}
        </div>
      {{/if}}
    {{else}}
    <div class="empty me-field-content">
      {{tr}}CConsultation-categorie_id.none{{/tr}}
    </div>
    {{/if}}
  </td>
</tr>
