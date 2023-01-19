{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$naissance->_id && !$sejour->grossesse_id}}
  <div class="small-info">
    {{tr}}CNaissance-Grossesse mandatory on sejour{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

{{mb_script module=patients script=pat_selector ajax=true}}
{{mb_script module=patients script=autocomplete ajax=true}}
{{mb_script module=patients script=patient ajax=true}}

{{assign var=uf_medicale_id value="maternite CNaissance uf_medicale_id"|gconf}}

<script>
  Main.add(function () {
    window.save_num_naissance = "{{$naissance->num_naissance}}";
    var form = getForm("newNaissance");
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CMediusers");
    url.addParam('show_view', true);
    url.addParam("limit", 500);
    url.addParam("input_field", "_prat_autocomplete");
    url.autoComplete(form.elements._prat_autocomplete, null, {
      minChars:           2,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var prat_id = selected.getAttribute('id').split('-')[2];
        $V(field.form['praticien_id'], prat_id);

        preselectUf(prat_id);
      },
      callback:           function (input, queryString) {
        queryString += "&where[users_mediboard.actif]=1";
        queryString += "&where_complex[users_mediboard.type]=IN('3', '13')";
        if (form._only_pediatres.checked) {
          queryString += "&where[users_mediboard.spec_cpam_id]=12";
        }
        return queryString;
      }
    });
    InseeFields.initCPVille("newNaissance", "cp_naissance", "lieu_naissance", "_code_insee", '_pays_naissance_insee',null);
  });

  toggleNumNaissance = function (num_semaines) {
    $V(num_semaines.form.num_naissance, $V(num_semaines) == "inf_15" ? "" : window.save_num_naissance);
  };

  PatSelector.init = function () {
    this.sForm = "newNaissance";
    this.sId = "bebe_id";
    this.sNom = "nom";
    this.sPrenom = "prenom";
    this.sSexe = "sexe";
    this.sNaissance = "naissance";
    this.pop();
  };

  preselectUf = function (chir_id) {
    {{if !$ufs.medicale|@count || $uf_medicale_id}}
    return;
    {{/if}}

    if (!chir_id) {
      return;
    }

    new Url("planningOp", "ajax_get_ufs_ids")
      .addParam("chir_id", chir_id)
      .requestJSON(function (ids) {
        var field = getForm("newNaissance").uf_medicale_id;
        $V(field, "");

        [ids.principale_chir, ids.principale_cab, ids.secondaires].each(
          function (_ids) {
            if ($V(field)) {
              return;
            }

            if (!_ids || !_ids.length) {
              return;
            }

            var i = 0;

            while (!$V(field) && i < _ids.length) {
              $V(field, _ids[i]);
              i++;
            }
          }
        );

        {{if "dPplanningOp CSejour required_uf_med"|gconf === "obl" && "dPplanningOp CSejour only_ufm_first_second"|gconf}}
        var form = field.form;

        for (var i = 0; i < form.uf_medicale_id.options.length; i++) {
          var _option = form.uf_medicale_id.options[i];
          var _option_value = parseInt(_option.value);

          var statut = !(
            (ids.secondaires && ids.secondaires.indexOf(_option_value) != -1)
            || (ids.principale_chir && ids.principale_chir.indexOf(_option_value) != -1)
            || (ids.principale_cab && ids.principale_cab.indexOf(_option_value) != -1)
          );

          _option.writeAttribute("disabled", statut);
        }
        {{/if}}
      });
  };
</script>

<form name="newNaissance" method="post" action="?" onsubmit="return Naissance.checkNaissance(this)">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="provisoire" value="{{$provisoire}}" />
  {{mb_field object=$naissance field=operation_id hidden=true}}
  {{if !$naissance->_id && !$provisoire}}
    <input type="hidden" name="bebe_id" />
  {{/if}}
  <input type="hidden" name="praticien_id" {{if !$provisoire}}class="notNull"{{/if}}
    {{if $naissance->_id}}
      value="{{$sejour->praticien_id}}"
    {{/if}} />

  <input type="hidden" name="dosql" value="do_create_naissance_aed" />

  {{if $callback}}
    <input type="hidden" name="callback" value="{{$callback}}" />
  {{/if}}
  
  {{if $naissance}}
    {{mb_key object=$naissance}}
  {{/if}}

  {{if $parturiente}}
    {{mb_key object=$parturiente}}
  {{/if}}

  {{if $constantes}}
    {{mb_key object=$constantes}}
  {{/if}}

  <table class="main layout">
    <tr>
      <td colspan="2">
        <table class="tbl me-no-align me-no-box-shadow me-no-bg">
          <tr>
            <th class="me-h6 me-no-bg">
              {{mb_include module=system template=inc_object_history object=$naissance}}
              Informations sur la naissance
            </th>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>
            Enfant
            {{if !$naissance->_id && !$provisoire}}
              <button type="button" class="search notext" onclick="PatSelector.init();">{{tr}}Search{{/tr}}</button>
            {{/if}}
          </legend>
          <table class="form me-no-box-shadow me-small-form">
            <tr>
              <th>{{mb_label object=$patient field="sexe"}}</th>
              <td>{{mb_field object=$patient field="sexe" typeEnum="radio" class=$provisoire|ternary:"":"notNull"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$patient field="naissance"}}</th>
              <td>{{mb_field object=$patient field="naissance" form="newNaissance" register="true"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$patient field="nom"}}</th>
              <td>{{mb_field object=$patient field="nom"}}</td>
            </tr>
            {{if !$provisoire}}
              <div id="NamesMatchWarning" style="display: none" class="small-warning">{{tr}}CPatient.first_birth_name_warning{{/tr}}</div>
              <tr>
                <th>
                    {{mb_label object=$patient field="prenom"}}
                </th>
                <td>
                    {{mb_field object=$patient field="prenom" onchange="Patient.checkBirthNameMatchesNames(true,'newNaissance');"}}
                </td>
              </tr>
              <tr>
                <th>
                    {{mb_label object=$patient field="prenoms"}}
                </th>
                <td>
                    {{mb_field object=$patient field=prenoms onchange="Patient.checkBirthNameMatchesNames(true,'newNaissance');"}}
                </td>
              </tr>
              <tr>
                <th>
                    {{mb_label object=$patient field="lieu_naissance"}}
                </th>
                <td>
                    {{mb_field object=$patient field="lieu_naissance" class="notNull"}}
                </td>
              </tr>
              <tr>
                  {{mb_field object=$patient field="commune_naissance_insee" readonly=true hidden="hidden" class="trait-strict"}}
                <th>
                    {{mb_label object=$patient field="_code_insee"}}
                </th>
                <td>
                    {{mb_field object=$patient field="_code_insee" class="notNull"}}
                </td>
              </tr>
              <tr>
                <th>
                    {{mb_label object=$patient field="cp_naissance"}}
                </th>
                <td>
                    {{mb_field object=$patient field="cp_naissance" class="notNull"}}
                </td>
              </tr>
              <tr>
                <th>
                    {{mb_label object=$patient field="_pays_naissance_insee"}}
                </th>
                <td>
                    {{mb_field object=$patient field="_pays_naissance_insee" class="notNull"}}
                </td>
              </tr>
            {{/if}}
            {{if "dPpatients CPatient tutelle_mandatory"|gconf}}
              <tr>
                <th>
                    {{mb_label object=$patient field="tutelle"}}
                </th>
                <td>
                    {{mb_field object=$patient field="tutelle" typeEnum=radio}}
                </td>
              </tr>
            {{/if}}
            <tr>
              <th>{{mb_label object=$naissance field=sejour_maman_id}}</th>
              <td>
                <select name="sejour_maman_id">
                    {{foreach from=$sejours_maman item=_sejour_maman}}
                      <option value="{{$_sejour_maman->_id}}" {{if $naissance->sejour_maman_id == $_sejour_maman->_id}}selected{{/if}}>
                          {{$_sejour_maman}}
                      </option>
                    {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$naissance field="rques"}}</th>
              <td colspan="3">{{mb_field object=$naissance field="rques" form="newNaissance"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$naissance field="type_allaitement"}}</th>
              <td>{{mb_field object=$naissance field="type_allaitement" emptyLabel="None" form="newNaissance"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$sejour field=praticien_id}}</th>
              <td>
                <input type="text" name="_prat_autocomplete" {{if $naissance->_id}}value="{{$sejour->_ref_praticien}}"{{/if}} />
                <label>
                  <input type="checkbox" name="_only_pediatres" checked />
                    {{tr}}CNaissance-only_pediatres{{/tr}}
                </label>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$sejour field=service_id}}</th>
              <td>
                <select name="service_id" class="{{$sejour->_props.service_id}}" style="width: 15em">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                    {{foreach from=$services item=_service}}
                      <option value="{{$_service->_id}}" {{if $sejour->service_id == $_service->_id}}selected{{/if}}>
                          {{$_service}}
                      </option>
                    {{/foreach}}
                </select>
              </td>
            </tr>
            {{if $ufs.soins|@count}}
              <tr>
                <th>{{mb_label object=$sejour field="uf_soins_id"}}</th>
                <td>
                  <select name="uf_soins_id" style="width: 15em"
                          class="ref {{if "dPplanningOp CSejour required_uf_soins"|gconf === "obl"}}notNull{{/if}}">
                    <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
                      {{foreach from=$ufs.soins item=uf}}
                        <option value="{{$uf->_id}}"
                                {{if $sejour->uf_soins_id === $uf->_id}}
                        selected
                                {{/if}}>
                            {{$uf}}
                        </option>
                      {{/foreach}}
                  </select>
                </td>
              </tr>
            {{/if}}
            {{if $ufs.medicale|@count}}
              <tr>
                <th>{{mb_label object=$sejour field="uf_medicale_id"}}</th>
                <td>
                  <select name="uf_medicale_id" style="width: 15em"
                          class="ref {{if "dPplanningOp CSejour required_uf_med"|gconf === "obl"}}notNull{{/if}}">
                    <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
                      {{foreach from=$ufs.medicale item=uf}}
                        <option value="{{$uf->_id}}"
                                {{if ((!$uf_medicale_id || $naissance->_id) && $sejour->uf_medicale_id === $uf->_id)
                        || (!$naissance->_id && $uf_medicale_id === $uf->_id)}}
                        selected
                                {{/if}}>
                            {{$uf}}
                        </option>
                      {{/foreach}}
                  </select>
                </td>
              </tr>
            {{/if}}
            {{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
            {{if $use_charge_price_indicator != "no"}}
              <tr>
                <th>{{mb_label object=$sejour field="charge_id"}}</th>
                <td>
                  <select class="ref{{if $use_charge_price_indicator == "obl"}} notNull{{/if}}" name="charge_id">
                    <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
                      {{foreach from=$cpi_list item=_cpi name=cpi}}
                        <option value="{{$_cpi->_id}}"
                                {{if $sejour->charge_id == $_cpi->_id ||
                                (!$sejour->_id && $smarty.foreach.cpi.first && "dPplanningOp CSejour select_first_traitement"|gconf)}}
                                  selected
                                {{/if}}
                                data-type="{{$_cpi->type}}" data-type_pec="{{$_cpi->type_pec}}"
                                data-hospit_de_jour="{{$_cpi->hospit_de_jour}}">
                            {{$_cpi|truncate:50:"...":false}}
                        </option>
                      {{/foreach}}
                  </select>
                </td>
              </tr>
            {{/if}}
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset>
          <legend>Naissance</legend>
          <table class="form me-no-box-shadow me-small-form">
            <tr>
              <th>{{mb_label object=$naissance field="num_naissance"}}</th>
              <td>
                {{assign var=num_readonly value=true}}
                {{if $naissance->_id}}
                  {{assign var=num_readonly value=null}}
                {{/if}}
                {{mb_field object=$naissance field="num_naissance" size="6" increment="true" form="newNaissance" step="1" readonly=$num_readonly}}
              </td>
            </tr>
            {{if !$provisoire}}
              <tr>
                <th>{{mb_label object=$naissance field="hors_etab"}}</th>
                <td>{{mb_field object=$naissance field="hors_etab"}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field="_heure"}}</th>
                <td>{{mb_field object=$naissance field="_heure" form="newNaissance" register="true"}}</td>
              </tr>
            {{/if}}
            <tr>
              <th>{{mb_label object=$naissance field="rang"}}</th>
              <td>{{mb_field object=$naissance field="rang" size="2" increment="true" form="newNaissance" step="1"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$naissance field="by_caesarean"}}</th>
              <td>{{mb_field object=$naissance field="by_caesarean"}}</td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>Interruption</legend>
          <table class="form me-no-box-shadow me-small-form">
            <tr>
              <th>{{mb_label object=$naissance field="interruption"}}</th>
              <td>{{mb_field object=$naissance field="interruption" emptyLabel="None"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$naissance field="num_semaines"}}</th>
              <td>
                <select name="num_semaines" onchange="toggleNumNaissance(this);">
                  <option value="">{{tr}}None{{/tr}}</option>
                  {{foreach from=$naissance->_specs.num_semaines->_list item=_num_semaines}}
                    <option value="{{$_num_semaines}}"
                            {{if $_num_semaines == $naissance->num_semaines}}selected{{/if}}
                      {{if $_num_semaines == "sup_15"}}
                        style="
                        {{if $naissance->num_semaines == "sup_15"}}
                          background: red;
                        {{else}}
                          display: none;
                        {{/if}}
                          "
                      {{/if}}
                    >{{tr}}CGrossesse.num_semaines.{{$_num_semaines}}{{/tr}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    {{if !$provisoire}}
      <tr>
        <td colspan="2">
          <table class="form me-small-form me-margin-4">
            <tr>
              <th class="category" colspan="2">{{tr}}CConstantesMedicales{{/tr}}</th>
            </tr>
            <tr>
              <th class="halfPane">{{mb_label object=$constantes field=_poids_g}}</th>
              <td>{{mb_field object=$constantes field=_poids_g size="3"}} {{$list_constantes._poids_g.unit}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$constantes field=taille}}</th>
              <td>{{mb_field object=$constantes field=taille size="3"}} {{$list_constantes.taille.unit}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$constantes field=perimetre_cranien}}</th>
              <td>{{mb_field object=$constantes field=perimetre_cranien size="3"}} {{$list_constantes.perimetre_cranien.unit}}</td>
            </tr>
          </table>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2">
        <table class="form me-small-form me-no-bg me-no-box-shadow">
          <tr>
            <td class="button" colspan="4">
              {{if $naissance->_id}}
                <button type="submit" class="submit">{{tr}}Modify{{/tr}}</button>
              {{else}}
                <button type="submit" class="submit singleclick">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
