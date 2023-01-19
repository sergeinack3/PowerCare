{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$dialog}}
  <script>
    function printRecherche() {
      var form = getForm("typeVue");
      var url = new Url("hospi", "vw_recherche");
      url.addElement(form.typeVue);
      url.addElement(form.selPrat);
      url.addElement(form.date_recherche);
      url.popup(800, 700, "Planning");
    }

    selectServices = function () {
      var url = new Url("hospi", "ajax_select_services");
      url.addParam("view", "etat_lits");
      url.addParam("ajax_request", 0);
      url.requestModal(null, null, {maxHeight: "90%"});
    };

    Main.add(function () {
      Calendar.regField(getForm("typeVue").date_recherche);
    });
  </script>
  <form name="typeVue" action="?m={{$m}}" method="get">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />

    {{if $typeVue}}
      <button type="button" class="button print" style="float: right;" onclick="printRecherche()">{{tr}}Print{{/tr}}</button>
      <select name="selPrat" onchange="this.form.submit()" style="float: right;">
        <option value="">&mdash; Tous les praticiens</option>
        {{mb_include module=mediusers template=inc_options_mediuser selected=$selPrat list=$listPrat}}
      </select>
    {{/if}}
    <select name="typeVue" onchange="this.form.submit()">
      <option value="0" {{if $typeVue == 0}}selected{{/if}}>Afficher les lits disponibles</option>
      <option value="1" {{if $typeVue == 1}}selected{{/if}}>Afficher les patients présents</option>
      {{if "dPurgences"|module_active}}
        <option value="2" {{if $typeVue == 2}}selected{{/if}}>Afficher les lits bloqués pour les urgences</option>
      {{/if}}
    </select>

    <button type="button" onclick="selectServices();" class="search">Services</button>

    <input type="hidden" name="date_recherche" class="dateTime" value="{{$date_recherche}}" onchange="this.form.submit()" />
  </form>
{{/if}}

<table class="tbl main">
  {{if $typeVue == 0}}
  <tr>
    <th class="title" colspan="6">
      <button type="button" class="print not-printable notext me-tertiary" style="float: left;" onclick="this.up('table').print()"></button>
      <div class="me-float-right">
        <div class="small-info etat_des_lits_information">
          {{tr}}CLit-help information about sex and gender{{/tr}}
        </div>
      </div>
      {{$date_recherche|date_format:$conf.datetime}} : {{$libre|@count}} lit(s) disponible(s)
    </th>
  </tr>
  <tr>
    <th>{{tr}}CService{{/tr}}</th>
    <th>{{tr}}CChambre{{/tr}}</th>
    <th class="narrow">
      <label title="Sexe de l'autre patient dans la chambre">
        {{mb_label class=CPatient field=sexe}}
      </label>
    </th>
    <th class="narrow">
      {{mb_label class=CPatient field=_age}}
    </th>
    <th>{{tr}}CLit{{/tr}}</th>
    <th>Fin de disponibilité</th>
  </tr>
  {{foreach from=$libre item=curr_lit}}
    {{assign var=chambre_id value=$curr_lit.chambre_id}}
    <tr>
      <td class="text">
        <span onmouseover="ObjectTooltip.createDOM(this, 'sejour-hover-{{$curr_lit.service_id}}');">{{$curr_lit.service}}</span>
      </td>
      <td class="text">
        {{$curr_lit.chambre}}
        {{if $curr_lit.caracteristiques != ""}}
          <div class="compact">
            {{$curr_lit.caracteristiques}}
          </div>
        {{/if}}
      </td>
      {{if isset($autre_sexe_chambre.$chambre_id|smarty:nodefaults)}}
        {{assign var=chambre_autre_sexe value=$autre_sexe_chambre.$chambre_id}}
        <td>
          {{tr}}CPatient.sexe.{{$chambre_autre_sexe.sexe}}{{/tr}}
        </td>
        <td>
          {{if $chambre_autre_sexe.patient}}
            {{mb_value object=$chambre_autre_sexe.patient field=_age}}
          {{/if}}
        </td>
      {{else}}
        <td>
        </td>
        <td>
        </td>
      {{/if}}
      <td class="text">{{$curr_lit.lit}}</td>
      <td class="text">{{$curr_lit.limite|date_format:"%A %d %B %Y à %Hh%M"}}
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">{{tr}}CLit.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  {{elseif $typeVue == 1}}
  <tr>
    <th class="title" colspan="9">
      <button type="button" class="print not-printable notext" style="float: left;" onclick="this.up('table').print()"></button>
      {{if $selPrat}}
        Dr {{$listPrat.$selPrat->_view}} -
      {{/if}}
      {{$date_recherche|date_format:$conf.datetime}} : {{$listAff.Aff|@count}} patient(s) placé(s)
      {{if $listAff.NotAff|@count}}- {{$listAff.NotAff|@count}} patient(s) non placé(s){{/if}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label class=CSejour field=patient_id}}</th>
    <th>{{mb_label class=CSejour field=praticien_id}}</th>
    <th>{{mb_title class=CAffectation field=lit_id}}</th>
    <th colspan="2">
      {{tr}}CAffectation{{/tr}} /
      {{mb_title class=CAffectation field=_duree}}
    </th>
    <th>Motif</th>
  </tr>

  {{if $listAff.Aff|@count == 0 && $listAff.NotAff|@count == 0}}
  <tr>
    <td colspan="9" class="empty">{{tr}}CLit.none{{/tr}}</td>
  </tr>
</table>
  {{mb_return}}
  {{/if}}

  {{foreach from=$listAff key=_type_aff item=_liste_aff}}
    {{foreach from=$_liste_aff item=_affectation}}
      {{if $_type_aff == "Aff"}}
        {{assign var=_sejour value=$_affectation->_ref_sejour}}
      {{else}}
        {{assign var=_sejour value=$_affectation}}
      {{/if}}
      {{assign var=_patient   value=$_sejour->_ref_patient}}
      {{assign var=_praticien value=$_sejour->_ref_praticien}}
      <tr>
        <td class="text">
          {{if $canPlanningOp->read && !$dialog}}
            <a class="action" style="float: right" title="Modifier le dossier administratif"
               href="?m=patients&tab=vw_edit_patients&patient_id={{$_patient->_id}}">
              <img src="images/icons/edit.png" alt="modifier" />
            </a>
            <a class="action" style="float: right" title="Modifier le séjour"
               href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$_sejour->_id}}">
              {{me_img src="edit.png" icon="edit" class="me-primary" alt="modifier"}}
            </a>
          {{/if}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
          {{$_patient}}
        </span>
        </td>
        <td class="text">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_praticien}}
        </td>
        {{if $_type_aff == "Aff"}}
          <td class="text">{{$_affectation->_view}}</td>
          <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_guid}}')">
          {{mb_include module=system template=inc_interval_datetime from=$_affectation->entree to=$_affectation->sortie}}
        </span>
          </td>
        {{else}}
          <td class="text empty">Non placé</td>
          <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
          {{mb_include module=system template=inc_interval_datetime from=$_sejour->entree to=$_sejour->sortie}}
        </span>
          </td>
        {{/if}}
        <td>{{$_affectation->_duree}}</td>

        <td class="text">
          {{if $_sejour->libelle}}
            {{$_sejour->libelle}}
          {{else}}
            {{foreach from=$_sejour->_ref_operations item=_operation}}
              {{mb_include module=planningOp template=inc_vw_operation operation=$_operation}}
            {{/foreach}}
          {{/if}}
        </td>

      </tr>
    {{/foreach}}
  {{/foreach}}
{{elseif $typeVue == 2}}
  <tr>
    <th class="title" colspan="5">
      <button type="button" class="print not-printable notext" style="float: left;" onclick="this.up('table').print()"></button>
      {{$date_recherche|date_format:$conf.datetime}} : {{$occupes|@count}} lit(s) bloqué(s) pour les urgences
    </th>
  </tr>
  <tr>
    <th>{{tr}}CService{{/tr}}</th>
    <th>{{tr}}CChambre{{/tr}}</th>
    <th>{{tr}}CLit{{/tr}}</th>
    <th>Fin de d'indisponibilité</th>
  </tr>
  {{foreach from=$occupes item=_affectation}}
    <tr>
      <td class="text">{{$_affectation->_ref_lit->_ref_chambre->_ref_service}}</td>
      <td class="text">{{$_affectation->_ref_lit->_ref_chambre}}</td>
      <td class="text">{{$_affectation->_ref_lit}}</td>
      <td class="text">{{$_affectation->sortie|date_format:"%A %d %B %Y à %Hh%M"}}
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">{{tr}}CLit.none{{/tr}} disponible</td>
    </tr>
  {{/foreach}}
{{/if}}
</table>

{{if $typeVue == 0}}
  {{foreach from=$services item=_service}}
    <table id="sejour-hover-{{$_service->_id}}" style="display:none" class="tbl">
      <tr>
        <th colspan="2" class="title">{{mb_value object=$_service field=nom}}</th>
      </tr>
      <tr>
        <td>{{mb_label object=$_service field=tel}}</td>
        <td>
          {{if $_service->tel}}
            {{mb_value object=$_service field=tel}}
          {{else}}
            <span class="empty">{{tr}}None{{/tr}}</span>
          {{/if}}
        </td>
      </tr>
    </table>
  {{/foreach}}
{{/if}}