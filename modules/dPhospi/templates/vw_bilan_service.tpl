{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

</td>
</tr>
</table>
{{if !$offline}}
<script>
  check_categ = true;
  Main.add(function () {
    oCatField = new TokenField(document.filter_prescription.token_cat);

    var cats = {{$cats|@json}};
    $$('input[type=checkbox]').each(function (oCheckbox) {
      if (cats.include(oCheckbox.value)) {
        updateCount(oCheckbox.className);
        oCheckbox.checked = true;
      }
    });

    getForm("filter_prescription")._dateTime_min.observe("ui:change", resetPeriodes);
    getForm("filter_prescription")._dateTime_max.observe("ui:change", resetPeriodes);

    toggleMedInj($("stup").checked);
  });

  function updateCount(name) {
    var nb_elt = $('nb_elt_' + name);
    if (nb_elt) {
      nb_elt.update(parseInt(nb_elt.innerHTML) + 1);
    }
  }

  var groups = {{$all_groups|@json}};

  function preselectCat(cat_group_id) {
    // On efface la selection de toutes les checkbox
    // (sauf par patient et seulement les présents)
    $$('input[type=checkbox]').each(function (oCheckbox) {
      if (oCheckbox.name != "_present_only_vw" && oCheckbox.name != "by_patient") {
        var elt = $('nb_elt_' + oCheckbox.className);
        if (elt) {
          elt.update("0");
        }
        oCheckbox.checked = false;
        oCatField.remove(oCheckbox.value);
      }
    });

    if (!cat_group_id) {
      return;
    }

    // Selection des checkbox en fonction du groupe selectionné
    group = groups[cat_group_id];
    group.each(function (item_id) {
      var item = $(item_id);
      item.checked = true;
      item.onclick();
      updateCount(item.className);
    });
  }

  function resetPeriodes() {
    getForm("filter_prescription").select('input[name=periode]').each(function (e) {
      e.checked = false;
    });
  }

  selectChap = function (name_chap, oField) {
    $$('input.' + name_chap).each(function (oCheckbox) {
      oCheckbox.checked = check_categ;
      if (check_categ) {
        oField.add(oCheckbox.value);
      }
      else {
        oField.remove(oCheckbox.value);
      }
    });
  }

  var periodes = {{$conf.dPprescription.CPrisePosologie.heures|@json}};
  selectPeriode = function (element) {
    var form = getForm("filter_prescription");
    var start = form.elements._dateTime_min;
    var end = form.elements._dateTime_max;

    var startDate = Date.fromDATETIME($V(start));
    var endDate = Date.fromDATETIME($V(start));

    if (element.value == 'matin' || element.value == 'soir' || element.value == 'nuit') {
      startDate.setHours(periodes[element.value].min);

      var dayOffset = 0;
      if (periodes[element.value].max < periodes[element.value].min) {
        dayOffset = 1;
      }
      endDate.setDate(startDate.getDate() + dayOffset);
      endDate.setHours(periodes[element.value].max);
    }
    else {
      startDate.setHours(0);
      startDate.setMinutes(0);
      startDate.setSeconds(0);
      endDate.setTime(startDate.getTime() + 24 * 60 * 60 * 1000 - 1000);
    }

    form._dateTime_min_da.value = startDate.toLocaleDateTime();
    form._dateTime_max_da.value = endDate.toLocaleDateTime();

    startDate = startDate.toDATETIME(true);
    endDate = endDate.toDATETIME(true);

    $V(start, startDate, false);
    $V(end, endDate, false);
  };

  toggleMedInj = function (state) {
    var med = $('med');
    var perf = $('perf');
    var inj = $('inj');
    var aerosol = $('aerosol');

    med.disabled = perf.disabled = inj.disabled = aerosol.disabled = state;

    if (state) {
      med.checked = perf.checked = inj.checked = aerosol.checked = !state;
      oCatField.remove("med");
      oCatField.remove("perf");
      oCatField.remove("inj");
      oCatField.remove("aerosol");
    }
  }
</script>
<form name="filter_prescription" method="get" class="not-printable">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="a" value="vw_bilan_service" />
  <input type="hidden" name="token_cat" value="{{$token_cat}}" />
  <input type="hidden" name="dialog" value="1" />
  <input type="hidden" name="do" value="1" />
  <table class="form">
    <tr>
      <th class="title" colspan="5">Bilan du service {{if !$mode_urgences}}{{$service->_view}}{{/if}}</th>
    </tr>
    <tr>
      <th class="category" colspan="4">Paramètres d'impression</th>
    </tr>
    <tr>
      <th>{{tr}}date.From_long{{/tr}}</th>
      <td>
        {{mb_field object=$prescription field="_dateTime_min" canNull="false" form="filter_prescription" register="true"}}
        <label><input type="radio" name="periode" value="matin" onclick="selectPeriode(this)"
                      {{if $periode=='matin'}}checked{{/if}} /> Matin</label>
        <label><input type="radio" name="periode" value="soir" onclick="selectPeriode(this)" {{if $periode=='soir'}}checked{{/if}} />
          Soir</label>
        <label><input type="radio" name="periode" value="nuit" onclick="selectPeriode(this)" {{if $periode=='nuit'}}checked{{/if}} />
          Nuit</label>
        <label><input type="radio" name="periode" value="today" onclick="selectPeriode(this)"
                      {{if $periode=='today'}}checked{{/if}} /> Aujourd'hui</label>
      </td>
      <th>{{tr}}date.To_long{{/tr}}</th>
      <td>
        {{mb_field object=$prescription field="_dateTime_max" canNull="false" form="filter_prescription" register="true"}}
      </td>
    </tr>
    <tr>
      <th>
        <label for="by_patient">
          Impression par patient
        </label>
      </th>
      <td colspan="3">
        <input type="checkbox" name="by_patient" value="true" {{if $by_patient}}checked{{/if}} />
      </td>
    </tr>
    <tr>
      <th>
        <label for="_present_only_vw">
          {{tr}}CPatient.present_only{{/tr}}
        </label>
      </th>
      <td colspan="3">
        <input type="checkbox" name="_present_only_vw" {{if $_present_only}}checked{{/if}}
               onchange="this.checked ? $V(this.form._present_only, 1) : $V(this.form._present_only, 0)" />
        <input type="hidden" name="_present_only" value="{{$_present_only}}" />
      </td>
    </tr>
    {{if $plan_soins_active}}
    <tr>
      <th class="category" colspan="4">Pré-sélection des catégories</th>
    </tr>
    <tr>
      <td colspan="4" class="text" style="text-align: center;">
        {{if $cat_groups|@count}}
        <select name="cat_group_id" onchange="preselectCat(this.value);">
          <option value="">&mdash; Groupe de catégories</option>
          {{foreach from=$cat_groups item=_cat_group}}
          <option value="{{$_cat_group->_id}}" {{if $_cat_group->_id == $cat_group_id}}selected{{/if}}>
            {{$_cat_group->libelle}}
          </option>
          {{/foreach}}
        </select>
        {{else}}
        <div class="small-info">Aucun groupe de catégories n'est disponible. <br />Pour pouvoir utiliser des pré-sélections de
          catégories, il faut tout d'abord les paramétrer dans le module "Prescription", onglet "Paramétrer", volet "Groupe de catégories"
        </div>
        {{/if}}
      </td>
    </tr>
    {{/if}}
    <tr>
      <th class="category" colspan="4">Sélection des catégories</th>
    </tr>
    <tr>
      <td colspan="4">
        <table>
          <tr>
            <td>
              <strong><label for="trans">Transmissions</label></strong>
            </td>
            <td>
              <input type="checkbox" value="trans" name="trans" onchange="oCatField.toggle(this.value, this.checked);" />
            </td>
          </tr>
          {{if $plan_soins_active}}
          <tr>
            <td>
              <a href="#1"
                 onclick="Modal.open('detail_med', {width: '60%', height: '40%'})"><strong>{{tr}}CPrescription._chapitres.med{{/tr}}</strong></a>
            </td>
            <td>
              <span id="nb_elt_med">0</span> / 4
              <div style="display: none;" id="detail_med">
                <table class="tbl">
                  <tr>
                    <th class="title">Produits médicamenteux</th>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <input type="checkbox" class="med" value="med" id="med" name="med"
                               onclick="oCatField.toggle(this.value, this.checked);" /> Médicaments
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <input type="checkbox" class="med" value="perf" id="perf" name="perf"
                               onclick="oCatField.toggle(this.value, this.checked);" /> {{tr}}CPrescription._chapitres.perf{{/tr}}
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <input type="checkbox" class="med" value="inj" id="inj" name="inj"
                               onclick="oCatField.toggle(this.value, this.checked);" /> {{tr}}CPrescription._chapitres.inj{{/tr}}
                        <br />
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <input type="checkbox" class="med" value="aerosol" id="aerosol" name="aerosol"
                               onclick="oCatField.toggle(this.value, this.checked);" /> {{tr}}CPrescription._chapitres.aerosol{{/tr}}
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <input type="checkbox" class="med" value="stup" id="stup" name="stup"
                               onclick="oCatField.toggle(this.value, this.checked); toggleMedInj(this.checked);" /> {{tr}}CPrescription._chapitres.stup{{/tr}}
                      </label>
                    </td>
                  </tr>
                </table>
                <div style="margin: auto;">
                  <button type="button" class="save"
                          onclick="check_categ=true; Control.Modal.close(); $('nb_elt_med').update($('detail_med').select('input:checked').length)">
                    Sélection des catégories
                  </button>
                </div>
              </div>
            </td>
          </tr>
          {{/if}}

          {{if $plan_soins_active}}
            {{foreach from=$categories item=categories_by_chap key=name name="foreach_cat"}}
            {{if $categories_by_chap|@count}}
            <tr>
              <td>
                <a href="#1"
                   onclick="Modal.open('detail_{{$name}}')"><strong>{{tr}}CCategoryPrescription.chapitre.{{$name}}{{/tr}}</strong></a>
              </td>
              <td>
                <span id="nb_elt_{{$name}}">0</span> / {{$categories_by_chap|@count}}
                <div style="display: none;" id="detail_{{$name}}">
                  <table class="form">
                    <tr>
                      <th class="title" colspan="4">
                        <button type="button" onclick="selectChap('{{$name}}', oCatField); check_categ = !check_categ;"
                                class="tick" style="float: right;">Tous
                        </button>
                        {{tr}}CCategoryPrescription.chapitre.{{$name}}{{/tr}}
                      </th>
                    </tr>
                    <tr>
                      {{foreach from=$categories_by_chap item=categorie name=foreach_cat}}
                      {{if $smarty.foreach.foreach_cat.index % 4 == 0}}
                    </tr>
                    <tr>
                      {{/if}}
                      <td {{if $smarty.foreach.foreach_cat.last}}colspan="{{$smarty.foreach.foreach_cat.index}}"{{/if}}
                          class="text">
                        <label title="{{$categorie->_view}}">
                          <input class="{{$name}}" type="checkbox" id="{{$categorie->_id}}" value="{{$categorie->_id}}"
                                 onclick="oCatField.toggle(this.value, this.checked);" /> {{$categorie->_view}}<br />
                        </label>
                      </td>
                      {{/foreach}}
                    </tr>
                    <tr>
                      <td class="button" colspan="4">
                        <button type="button" class="save"
                                onclick="check_categ = true; Control.Modal.close(); $('nb_elt_{{$name}}').update($('detail_{{$name}}').select('input:checked').length)">
                          Sélection des catégories
                        </button>
                      </td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
            {{/if}}
            {{/foreach}}
          {{/if}}
        </table>
      </td>
    </tr>
    {{if $plan_soins_active}}
    <tr>
      <th colspan="4" class="category">Options</th>
    </tr>
    <tr>
      <th colspan="2" style="">
        Afficher les lignes inactives
      </th>
      <td colspan="2">
        <label>
          <input type="radio" name="show_inactive" value="1" {{if $show_inactive=='1'}}checked{{/if}} /> Oui
        </label>
        <label>
          <input type="radio" name="show_inactive" value="0" {{if $show_inactive=='0'}}checked{{/if}} /> Non
        </label>
      </td>
    </tr>
    {{/if}}
    <tr>
      <td style="text-align: center" colspan="4">
        <button class="tick">Filtrer</button>
        {{if $lines_by_patient|@count || $trans_and_obs|@count}}
        <button class="print" type="button" onclick="window.print()">Imprimer les résultats</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
{{/if}}

{{if $offline}}
<button class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>
<h4>Impression du {{$dtnow|date_format:$conf.longdate}} à {{$dtnow|date_format:$conf.time}}</h4>
{{/if}}

{{if $trans_and_obs|@count}}
<br />
<table class="tbl">
  <tr>
    <th colspan="9" class="title">Transmissions {{if !$mode_urgences}}-
      {{$service->_view}} - du {{$dateTime_min|date_format:$conf.datetime}} au {{$dateTime_max|date_format:$conf.datetime}}
      {{/if}}
    </th>
  </tr>
  {{foreach from=$trans_and_obs key=patient_id item=_trans_and_obs_by_patient}}
  {{foreach from=$_trans_and_obs_by_patient item=_trans_and_obs_by_date name=foreach_trans_date}}
  {{foreach from=$_trans_and_obs_by_date item=_trans_and_obs name=foreach_trans}}

  {{if $smarty.foreach.foreach_trans_date.first && $smarty.foreach.foreach_trans.first}}
  {{if $_trans_and_obs|instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale' || $_trans_and_obs|instanceof:'Ox\Mediboard\Hospi\CObservationMedicale'}}
  {{assign var=sejour value=$_trans_and_obs->_ref_sejour}}
  {{else}}
  {{assign var=sejour value=$_trans_and_obs->_ref_context}}
  {{/if}}
  {{assign var=patient value=$sejour->_ref_patient}}
  {{assign var=operation value=$sejour->_ref_last_operation}}
  <tr>
    <th colspan="9" class="text">
      {{if !$mode_urgences}}
      <span style="float: left; text-align: left;">
                  {{foreach from=$sejour->_ref_affectations item=_affectation}}
                    <strong
                      {{if $_affectation->entree < $dateTime_min &&
                      ($_affectation->sortie < $dateTime_min || $_affectation->sortie > $dateTime_max)}}
                      style="color: #666"
                      {{/if}}>
                        {{$_affectation}}
                      </strong>

{{if $sejour->_ref_affectations|@count > 1}}
                    <small
                      {{if $_affectation->entree < $dateTime_min &&
                      ($_affectation->sortie < $dateTime_min || $_affectation->sortie > $dateTime_max)}}
                      style="color: #666"
                      {{/if}}>
                       (du {{$_affectation->entree|date_format:$conf.date}} au {{$_affectation->sortie|date_format:$conf.date}})
                     </small>
                  {{/if}}

                    <br />
                  {{/foreach}}
                </span>
      {{/if}}
      <span style="float: right">
                DE: {{$sejour->entree|date_format:$conf.date}}<br />
                DS: {{$sejour->sortie|date_format:$conf.date}}
              </span>
      <strong>{{$patient->_view}}</strong>
      Né(e) le {{mb_value object=$patient field=naissance}} - ({{$patient->_age}}) -
      ({{$patient->_ref_constantes_medicales->poids}} kg)
      <br />
      {{if $operation->_id}}
      {{$operation->_ref_chir->_view}} - {{tr}}dPplanningOp-COperation
      of{{/tr}} {{$operation->_ref_plageop->date|date_format:$conf.date}} -
      <strong>(I{{if $operation->_compteur_jour >=0}}+{{/if}}{{$operation->_compteur_jour}})
        - {{mb_label object=$operation field="cote"}} {{mb_value object=$operation field="cote"}}</strong>
      {{/if}}
    </th>
  </tr>
  <tr>
    <th class="element text" colspan="9" style="text-align: left">
      <strong>{{$operation->libelle}}</strong>
      {{if !$operation->libelle}}
      {{foreach from=$operation->_ext_codes_ccam item=curr_ext_code}}
      <strong>{{$curr_ext_code->code}}</strong>
      :
      {{$curr_ext_code->libelleLong}}
      <br />
      {{/foreach}}
      {{/if}}
    </th>
  </tr>
  {{/if}}
  <tr id="{{$_trans_and_obs->_guid}}"
      {{if $_trans_and_obs|instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale'}}class="{{$_trans_and_obs->_cible}}"{{/if}}
    {{if $_trans_and_obs|instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale' && $_trans_and_obs->degre == 'high'}}
      style="font-weight: bold;"
    {{/if}}>
    {{mb_include module=hospi template=inc_line_suivi _suivi=$_trans_and_obs show_patient=false readonly=true nodebug=true}}
  </tr>
  {{/foreach}}
  {{/foreach}}
  {{/foreach}}
</table>
<br />
{{/if}}


{{foreach from=$lines_by_patient key=key1 item=_lines_by_chap name=foreach_chapitres}}
<table class="tbl" {{if !$smarty.foreach.foreach_chapitres.first || $trans_and_obs|@count}}style="page-break-before: always;"{{/if}}>
  {{if !$by_patient}}
  <tr>
    <th colspan="6" class="title">{{tr}}CPrescription._chapitres.{{$key1}}{{/tr}} {{if !$mode_urgences}}-
      {{$service->_view}} - du {{$dateTime_min|date_format:$conf.datetime}} au {{$dateTime_max|date_format:$conf.datetime}}{{/if}}</th>
  </tr>
  {{/if}}

  {{foreach from=$_lines_by_chap key=key2 item=_lines_by_patient name="foreach_lines"}}
  {{foreach from=$_lines_by_patient key=key3 item=prises_by_dates name="foreach_prises"}}

  {{if $by_patient}}
  {{if !$mode_urgences}}
  {{assign var=lit value=$lits.$key1}}
  {{/if}}
  {{assign var=sejour value=$sejours.$key2}}
  {{else}}
  {{assign var=lit value=$lits.$key2}}
  {{assign var=sejour value=$sejours.$key3}}
  {{/if}}

  {{assign var=patient value=$sejour->_ref_patient}}
  {{assign var=operation value=$sejour->_ref_last_operation}}

  {{if !$by_patient || ($by_patient && $smarty.foreach.foreach_prises.first)}}
  <tr>
    <td colspan="6"><br /></td>
  </tr>
  <tr>
    <th colspan="6" class="text title">
          <span style="float: left">
            {{if !$mode_urgences}}
              <strong>{{$lit}}</strong>
            {{/if}}
          </span>
      <span style="float: right">
            DE: {{$sejour->entree|date_format:$conf.date}}<br />
            DS: {{$sejour->sortie|date_format:$conf.date}}
          </span>
      <strong>{{$patient->_view}}</strong>
      Né(e) le {{mb_value object=$patient field=naissance}} - ({{$patient->_age}}) -
      ({{$patient->_ref_constantes_medicales->poids}} kg)
      <br />
      {{tr}}dPplanningOp-COperation of{{/tr}} {{$operation->_ref_plageop->date|date_format:$conf.date}} -
      <strong>(I{{if $operation->_compteur_jour >=0}}+{{/if}}{{$operation->_compteur_jour}})
        - {{mb_label object=$operation field="cote"}} {{mb_value object=$operation field="cote"}}</strong>
    </th>
  </tr>
  <tr>
    <th class="element text" colspan="6" style="text-align: left">
      <strong>{{$operation->libelle}}</strong>
      {{if !$operation->libelle}}
      {{foreach from=$operation->_ext_codes_ccam item=curr_ext_code}}
      <strong>{{$curr_ext_code->code}}</strong>
      :
      {{$curr_ext_code->libelleLong}}
      <br />
      {{/foreach}}
      {{/if}}
    </th>
  </tr>
  {{/if}}

  {{if $by_patient}}
  <tr>
    <th colspan="6">{{tr}}CPrescription._chapitres.{{$key3}}{{/tr}}</th>
  </tr>
  {{/if}}

  {{foreach from=$prises_by_dates key=date item=prises_by_hour name="foreach_date"}}
  <tr>
    <th style="border: none;" class="section narrow"><strong>{{$date|date_format:$conf.date}}</strong>
    </td>
    <th style="border: none; width: 250px;">Libellé</th>
    <th style="border: none; width:  50px;">Prévues</th>
    <th style="border: none; width:  50px;">Effectuées</th>
    <th style="border: none; width: 150px;" colspan="2">Unité adm.</th>
  </tr>
  {{foreach from=$prises_by_hour key=hour item=prises_by_type  name="foreach_hour"}}
  {{assign var=_date_time value="$date $hour:00:00"}}
  {{foreach from=$prises_by_type key=line_class item=prises name="foreach_unite"}}
  {{if $line_class == "CPrescriptionLineMix"}}
  {{foreach from=$prises key=prescription_line_mix_id item=lines}}
  {{assign var=prescription_line_mix value=$list_lines.$line_class.$prescription_line_mix_id}}
  {{if !$prescription_line_mix->conditionnel || $prescription_line_mix->_active_dates.$date.$hour || $show_inactive}}
  <tr>
    <td>{{$hour}}h</td>
    <td colspan="5" class="text">
      <strong>{{$prescription_line_mix->_view}}</strong> {{if $prescription_line_mix->conditionnel}}
      -<strong>Conditionnel</strong>{{/if}}
      {{if $prescription_line_mix->commentaire}}
      <br />
      {{$prescription_line_mix->commentaire}}
      {{/if}}
    </td>
  </tr>
  {{if !$prescription_line_mix->signature_prat && "planSoins general show_unsigned_med_msg"|gconf}}
  <tr>
    <td></td>
    <td class="text">
      <ul>
        {{foreach from=$lines key=perf_line_id item=_perf}}
        {{assign var=perf_line value=$list_lines.CPrescriptionLineMixItem.$perf_line_id}}
        <li>{{$perf_line->_ucd_view}}</li>
        {{/foreach}}
      </ul>
    </td>
    <td colspan="3">
      <div class="small-warning">
        Ligne non signée
      </div>
    </td>
    <td></td>
  </tr>
  {{else}}
  {{foreach from=$lines key=perf_line_id item=_perf}}
  {{assign var=perf_line value=$list_lines.CPrescriptionLineMixItem.$perf_line_id}}
  <tr>
    <td></td>
    <td class="text">
      <em>
        {{$perf_line->_ucd_view}}
      </em>
      {{if array_key_exists('prevu', $_perf) && array_key_exists('administre', $_perf) && $_perf.prevu == $_perf.administre}}
      {{me_img_title src="tick.png" icon="tick" class="me-success" alt="Administrations effectuées"}}
        Administrations effectuées
      {{/me_img_title}}
      {{/if}}
    </td>
    <td style="text-align: center;">
      {{if array_key_exists('prevu', $_perf)}}
      {{$_perf.prevu}}
      {{/if}}
    </td>
    <td style="text-align: center;">
      {{if array_key_exists('administre', $_perf)}}
      {{$_perf.administre}}
      {{/if}}
    </td>
    <td style="text-align: center;" colspan="2">
      {{$perf_line->_unite_reference_libelle}}
    </td>
  </tr>
  {{/foreach}}
  {{/if}}
  {{/if}}
  {{/foreach}}
  {{else}}
  {{foreach from=$prises key=line_id item=quantite}}
  {{assign var=line value=$list_lines.$line_class.$line_id}}
  {{if !$line->conditionnel || $line->_active_dates.$date.$hour || $show_inactive}}
  <tr>
    <td>{{$hour}}h</td>
    <td style="width: 250px;" class="text">{{$line->_view}} {{if $line->conditionnel}} -
      <strong>Conditionnel</strong>
      {{/if}}
      {{if $line->commentaire}}
      <br />
      {{$line->commentaire}}
      {{/if}}
      {{if array_key_exists('prevu', $quantite) && array_key_exists('administre', $quantite) && $quantite.prevu == $quantite.administre}}
      {{me_img_title src="tick.png" icon="tick" class="me-success" alt="Administrations effectuées"}}
        Administrations effectuées
      {{/me_img_title}}
      {{/if}}
    </td>

    {{if !$line->signee && $line->_class == "CPrescriptionLineMedicament" && "planSoins general show_unsigned_med_msg"|gconf}}
    <td colspan="4">
      <div class="small-warning">
        Ligne non signée
      </div>
    </td>
    {{else}}
    <td
      style="width: 50px; text-align: center;">{{if array_key_exists('prevu', $quantite)}}{{$quantite.prevu}}{{else}} -{{/if}}</td>
    <td
      style="width: 50px; text-align: center;">{{if array_key_exists('administre', $quantite)}}{{$quantite.administre}}{{else}}-{{/if}}</td>
    <td style="width: 150px; text-align: center;" class="text" colspan="2">
      {{if $line_class=="CPrescriptionLineMedicament"}}
      {{$line->_unite_reference_libelle}}
      {{else}}
      {{$line->_unite_prise}}
      {{/if}}
    </td>
    {{/if}}
  </tr>
  {{/if}}
  {{/foreach}}
  {{/if}}
  {{/foreach}}
  {{/foreach}}
  {{/foreach}}

  {{if $offline && $by_patient && $smarty.foreach.foreach_prises.last}}
  <tr>
    <th class="title" colspan="6">
      Fin du dossier pour le patient {{$patient->_view}}
    </th>
  </tr>
  {{/if}}
  {{/foreach}}
  {{/foreach}}
</table>
{{/foreach}}

{{if !$trans_and_obs|@count && !$lines_by_patient|@count && $token_cat}}
<h2>Aucun résultat</h2>
{{/if}}
<table>
  <tr>
    <td>
