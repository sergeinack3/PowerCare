{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if $can->admin}}
  function filterAllEi(field) {
    /*if (field.name == "selected_user_id") {
      $("tab-incident").select('a[href="#ALL_TERM"] span.user')[0].update(field.options[field.selectedIndex].text);
    }*/

    var url = new Url("dPqualite", "httpreq_vw_allEi");
    url.addElement(field);
    url.requestUpdate('ALL_TERM');
  }

  function annuleFiche(form, annulation) {
    form.annulee.value = annulation;
    form._validation.value = 1;
    form.submit();
  }

  function refusMesures(form) {
    if (form.remarques.value == "") {
      alert("{{tr}}CFicheEi-msg-validdoc{{/tr}}");
      form.remarques.focus();
    } else {
      form.service_date_validation.value = "";
      form._validation.value = 1;
      form.submit();
    }
  }

  function unvalidate(form) {
    $(form).insert(new Element('input', {name: "_unvalidate", type: "hidden", value: "1"})).submit();
  }

  function saveVerifControle(oForm) {
    oForm._validation.value = 1;
    oForm.submit();
  }
  {{/if}}

  function loadListFiches(type, first) {
    if ($(type).empty() || first) {
      var url = new Url("qualite", "httpreq_vw_allEi");
      url.addParam("selected_fiche_id", '{{$selected_fiche_id}}');
      url.addParam("type", type);
      url.addParam("first", first);
      url.addFormData(getForm("filter-ei"));
      url.requestUpdate(type);
    }
  }

  function filterFiches() {
    $$("#tab-incident a").each(function (a) {
      var id = Url.parse(a.href).fragment;
      $(id).update();
      a.select('span').last().update("?");
    });
    loadListFiches(tab.activeContainer.id);
    $$("#tab-incident a").each(function (a) {
      var id = Url.parse(a.href).fragment;
      loadListFiches(id);
    });
    return false;
  }

  function printIncident(ficheId) {
    var url = new Url("qualite", "print_fiche");
    url.addParam("fiche_ei_id", ficheId);
    url.popup(700, 500, "printFicheEi");
  }

  function reloadItems(categorie_id) {
    var url = new Url("qualite", "httpreq_list_items");
    url.addParam("categorie_id", categorie_id);
    url.requestUpdate("items");
  }

  Main.add(function () {
    tab = Control.Tabs.create('tab-incident', true);
    filterFiches();//loadListFiches(tab.activeContainer);
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="filter-ei" method="get" onsubmit="return filterFiches()">
        {{mb_field object=$filterFiche field=elem_concerne style="width: 14em;" emptyLabel="CFicheEi-elem_concerne" onchange="this.form.onsubmit()"}}

        <select name="evenements" style="width: 9em;"
                onchange="this.form.filter_item.value = ''; reloadItems(this.value); this.form.onsubmit()">
          <option value=""> &mdash; Catégorie</option>
          {{foreach from=$listCategories item=category}}
            <option value="{{$category->_id}}" {{if $category->_id==$evenements}}selected{{/if}}>
              {{$category}}
            </option>
          {{/foreach}}
        </select>

        <select name="filter_item" style="width: 6em;" onchange="this.form.onsubmit()" id="items">
          <option value=""> &mdash; Item</option>
          {{foreach from=$items item=_item}}
            <option value="{{$_item->_id}}" {{if $_item->_id==$filter_item}}selected{{/if}}>
              {{$_item}}
            </option>
          {{/foreach}}
        </select>

        {{if !$conf.dPqualite.CFicheEi.mode_anonyme || $modules.dPcabinet->_can->admin}}
          <select name="selected_user_id" style="width: 12em;" onchange="this.form.onsubmit()">
            <option value="">&mdash; {{tr}}_CFicheEi_allusers{{/tr}}</option>
            {{foreach from=$listUsersTermine item=curr_user}}
              <option value="{{$curr_user->user_id}}" {{if $curr_user->user_id==$selected_user_id}}selected{{/if}}>
                {{$curr_user}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}

        <select name="selected_service_valid_user_id" style="width: 11em;" onchange="this.form.onsubmit()">
          <option value="">&mdash; {{tr}}CFicheEi-service_valid_user_id{{/tr}}</option>
          {{foreach from=$listUsersEdit item=curr_user}}
            <option value="{{$curr_user->user_id}}" {{if $curr_user->user_id==$selected_service_valid_user_id}}selected{{/if}}>
              {{$curr_user->_view}}
            </option>
          {{/foreach}}
        </select>

        <button type="button" onclick="this.form.onsubmit();" class="search me-primary">{{tr}}Filter{{/tr}}</button>
      </form>

      <ul id="tab-incident" class="control_tabs full_width me-control-tabs-wraped">
        {{if !$can->admin && $can->edit}}
          <li onmouseup="loadListFiches('ATT_CS')"><a href="#ATT_CS">{{tr}}_CFicheEi_acc-ATT_CS{{/tr}} (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ATT_QUALITE')"><a href="#ATT_QUALITE">{{tr}}_CFicheEi_acc-ATT_QUALITE{{/tr}}
              (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ALL_TERM')"><a href="#ALL_TERM">{{tr}}_CFicheEi_acc-ALL_TERM{{/tr}} (<span></span>)</a></li>
        {{elseif $can->admin}}
          <li onmouseup="loadListFiches('VALID_FICHE')"><a href="#VALID_FICHE">{{tr}}_CFicheEi_acc-VALID_FICHE{{/tr}}
              (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ATT_CS')"><a href="#ATT_CS">{{tr}}_CFicheEi_acc-ATT_CS_adm{{/tr}} (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ATT_QUALITE')"><a href="#ATT_QUALITE">{{tr}}_CFicheEi_acc-ATT_QUALITE_adm{{/tr}}
              (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ATT_VERIF')"><a href="#ATT_VERIF">{{tr}}_CFicheEi_acc-ATT_VERIF{{/tr}} (<span></span>)</a>
          </li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ATT_CTRL')"><a href="#ATT_CTRL">{{tr}}_CFicheEi_acc-ATT_CTRL{{/tr}} (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ALL_TERM')"><a href="#ALL_TERM">{{tr}}CFicheEi.all{{/tr}} (<span></span>)</a></li>
          <li class="linebreak me-display-none"></li>
          <li onmouseup="loadListFiches('ANNULE')"><a href="#ANNULE">{{tr}}_CFicheEi_acc-ANNULE{{/tr}} (<span></span>)</a></li>
        {{/if}}
        <li class="linebreak me-display-none"></li>
        <li onmouseup="loadListFiches('AUTHOR')"><a href="#AUTHOR">{{tr}}_CFicheEi_acc-AUTHOR{{/tr}} (<span></span>)</a></li>
      </ul>

      {{if !$can->admin && $can->edit}}
        <div id="ATT_CS" class="me-no-align"></div>
        <div id="ATT_QUALITE" class="me-no-align"></div>
        <div id="ALL_TERM" class="me-no-align"></div>
      {{elseif $can->admin}}
        <div id="VALID_FICHE" class="me-no-align"></div>
        <div id="ATT_CS" class="me-no-align"></div>
        <div id="ATT_QUALITE" class="me-no-align"></div>
        <div id="ATT_VERIF" class="me-no-align"></div>
        <div id="ATT_CTRL" class="me-no-align"></div>
        <div id="ALL_TERM" class="me-no-align"></div>
        <div id="ANNULE" class="me-no-align"></div>
      {{/if}}

      <div id="AUTHOR" class="me-no-align"></div>
    </td>

    <td class="halfPane">
      {{if $fiche->_id}}
        <form name="ProcEditFrm" method="post" onsubmit="return checkForm(this)">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_ficheEi_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="annulee" value="{{$fiche->annulee|default:"0"}}" />
          <input type="hidden" name="fiche_ei_id" value="{{$fiche->fiche_ei_id}}" />
          <input type="hidden" name="_validation" value="0" />
          <input type="hidden" name="service_date_validation" value="{{$fiche->service_date_validation}}" />

          <table class="form">
            {{mb_include module=qualite template=inc_incident_infos}}

            {{if $can->admin && !$fiche->date_validation && !$fiche->annulee}}
              <tr>
                <th>{{mb_label object=$fiche field="degre_urgence"}}</th>
                <td>{{mb_field object=$fiche field="degre_urgence" emptyLabel="Choose"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$fiche field="gravite"}}</th>
                <td>{{mb_field object=$fiche field="gravite" emptyLabel="Choose"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$fiche field="vraissemblance"}}</th>
                <td>{{mb_field object=$fiche field="vraissemblance" emptyLabel="Choose"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$fiche field="plainte"}}</th>
                <td>{{mb_field object=$fiche field="plainte" emptyLabel="Choose"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$fiche field="commission"}}</th>
                <td>{{mb_field object=$fiche field="commission" emptyLabel="Choose"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$fiche field="service_valid_user_id"}}</th>
                <td>
                  <select name="service_valid_user_id" class="notNull {{$fiche->_props.service_valid_user_id}}">
                    <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                    {{mb_include module=mediusers template=inc_options_mediuser list=$listUsersEdit selected=$_user->_id}}
                  </select>
                </td>
              </tr>
              <tr>
                <td colspan="2" class="button">
                  <input type="hidden" name="valid_user_id" value="{{$app->user_id}}" />
                  <button class="edit" type="button"
                          onclick="window.location.href='?m={{$m}}&tab=vw_incident&fiche_ei_id={{$fiche->fiche_ei_id}}';">
                    {{tr}}Edit{{/tr}}
                  </button>
                  <button class="modify" type="submit">
                    {{tr}}button-CFicheEi-transmit{{/tr}}
                  </button>
                  <button class="cancel" type="button" onclick="annuleFiche(this.form,1);" title="{{tr}}_CFicheEi_cancel{{/tr}}">
                    {{tr}}Cancel{{/tr}}
                  </button>
                </td>
              </tr>
            {{/if}}

            {{if $fiche->service_valid_user_id && $fiche->service_valid_user_id==$user && !$fiche->service_date_validation}}
              <tr>
                <th colspan="2" class="category">
                  {{tr}}_CFicheEi_validchefserv{{/tr}}
                </th>
              </tr>
              {{if $fiche->remarques}}
                <tr>
                  <th><strong>{{tr}}_CFicheEi_invalidBy{{/tr}}</strong></th>
                  <td class="text">
                    {{$fiche->_ref_qualite_valid}}
                  </td>
                </tr>
                <tr>
                  <th><strong>{{tr}}CFicheEi-remarques-court{{/tr}}</strong></th>
                  <td class="text" style="color: #f00;">
                    <strong>{{$fiche->remarques|nl2br}}</strong>
                  </td>
                </tr>
              {{/if}}
              <tr>
                <th>
                  <label for="service_actions"
                         title="{{tr}}CFicheEi-service_actions-desc{{/tr}}">{{tr}}CFicheEi-service_actions{{/tr}}</label>
                </th>
                <td>
                  <textarea name="service_actions"
                            class="notNull {{$fiche->_props.service_actions}}">{{$fiche->service_actions}}</textarea>
                </td>
              </tr>
              <tr>
                <th>
                  <label for="service_descr_consequences"
                         title="{{tr}}CFicheEi-service_descr_consequences-desc{{/tr}}">{{tr}}CFicheEi-service_descr_consequences{{/tr}}</label>
                </th>
                <td>
                  <textarea name="service_descr_consequences"
                            class="notNull {{$fiche->_props.service_descr_consequences}}">{{$fiche->service_descr_consequences}}</textarea>
                </td>
              </tr>
              <tr>
                <td colspan="2" class="button">
                  <input type="hidden" name="remarques" value="" />
                  <button class="modify" type="submit">
                    {{tr}}button-CFicheEi-transmit{{/tr}}
                  </button>
                </td>
              </tr>
            {{/if}}
            {{if $can->admin && $fiche->service_date_validation}}
              {{if !$fiche->qualite_date_validation}}
                <tr>
                  <td colspan="2" class="button">
                    <script>
                      Main.add(function () {
                        Calendar.regField(getForm("ProcEditFrm").qualite_date_controle);
                      });
                    </script>

                    <input type="hidden" name="qualite_user_id" value="{{$app->user_id}}" />
                    <input type="hidden" name="qualite_date_controle" value="" />
                    <button class="modify" type="submit">
                      {{tr}}button-CFicheEi-valid{{/tr}}
                    </button>
                    <button class="cancel" type="button" onclick="refusMesures(this.form);">
                      {{tr}}button-CFicheEi-refus{{/tr}}
                    </button>
                    <button class="tick" type="button"
                            onclick="this.form.qualite_date_controle.value = '{{$today|iso_date}}'; this.form.submit()">
                      {{tr}}button-CFicheEi-classer{{/tr}}
                    </button>
                  </td>
                </tr>
                <tr>
                  <th>
                    <label for="remarques" title="{{tr}}CFicheEi-remarques-desc{{/tr}}">
                      {{tr}}CFicheEi-remarques{{/tr}}
                    </label>
                  </th>
                  <td>
                    <textarea name="remarques" class="{{$fiche->_props.remarques}}"></textarea>
                  </td>
                </tr>
              {{else}}

                {{if !$fiche->qualite_date_verification && !$fiche->qualite_date_controle}}
                  <tr>
                    <th>{{mb_label object=$fiche field="qualite_date_verification"}}</th>
                    <td>
                      <input type="hidden" name="qualite_date_verification" value="{{$today|iso_date}}" />
                      <button type="button" class="tick"
                              onclick="this.form.qualite_date_verification.name = 'qualite_date_controle'; this.form.submit();">
                        {{tr}}button-CFicheEi-classer{{/tr}}
                      </button>
                      <script>
                        Main.add(function () {
                          Calendar.regField(getForm("ProcEditFrm").qualite_date_verification);
                        });
                      </script>
                    </td>
                  </tr>
                {{elseif !$fiche->qualite_date_controle}}
                  <tr>
                    <th>{{mb_label object=$fiche field="qualite_date_controle"}}</th>
                    <td>
                      <input type="hidden" name="qualite_date_controle" value="{{$today|iso_date}}" class="date" />
                    </td>
                  </tr>
                {{/if}}

                {{if !$fiche->qualite_date_verification || !$fiche->qualite_date_controle}}
                  <tr>
                    <td colspan="2" class="button">
                      <button class="modify" type="button" onclick="saveVerifControle(this.form);">
                        {{tr}}Save{{/tr}}
                      </button>
                    </td>
                  </tr>
                {{/if}}
              {{/if}}
            {{/if}}

            {{if $can->edit && ($fiche->annulee || $fiche->date_validation)}}
              <tr>
                <td colspan="2" class="button">
                  {{if $fiche->annulee}}
                    <button class="change" type="button" onclick="annuleFiche(this.form,0);"
                            title="{{tr}}button-CFicheEi-retablir{{/tr}}">
                      {{tr}}button-CFicheEi-retablir{{/tr}}
                    </button>
                  {{else}}
                    <button class="print" type="button" onclick="printIncident({{$fiche->fiche_ei_id}});">
                      {{tr}}Print{{/tr}}
                    </button>
                    {{if $can->admin && !$fiche->qualite_user_id && !$fiche->qualite_date_validation && !$fiche->qualite_date_verification}}
                      <button class="change" type="button" onclick="unvalidate(this.form);">
                        Dé-valider
                      </button>
                    {{/if}}
                  {{/if}}
                </td>
              </tr>
            {{/if}}

          </table>
        </form>
      {{else}}
        <div class="small-info">
          Veuillez séléctionner un incident
        </div>
      {{/if}}
    </td>
  </tr>
</table>