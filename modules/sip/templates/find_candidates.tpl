{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="sip" script="SIP" ajax=true}}

<script>
Main.add(function(){
  getForm('find_candidates').quantity_limited_request.addSpinner();
});
</script>

{{mb_include module="hl7" template="inc_form_session_receiver"}}

<form name="find_candidates" action="?m=sip&a=ajax_find_candidates" method="post" onsubmit="return SIP.findCandidates(this)">
  <input type="hidden" name="continue" value="" />
  <input type="hidden" name="cancel" value="" />

  <table class="form">
    <tr>
      <th class="category">Recherche d'un dossier patient sur le SIP</th>
    </tr>

    <tr>
      <td>
        <fieldset>
          <legend>Informations démographiques</legend>

          <table class="form me-no-box-shadow">
            <tr>
              <th style="width: 200px"><label for="nom" title="Nom du patient à rechercher, au moins les premières lettres">Nom</label></th>
              <td><input tabindex="1" type="text" name="nom" value="" /></td>

              <th><label for="adresse" title="Adresse du patient à rechercher">Adresse</label></th>
              <td><input tabindex="5" type="text" name="adresse" value="" /></td>
            </tr>

            <tr>
              <th><label for="prenom" title="Prénom du patient à rechercher, au moins les premières lettres">Prénom</label></th>
              <td><input tabindex="2" type="text" name="prenom" value="" /></td>

              <th><label for="ville" title="Ville du patient à rechercher">Ville</label></th>
              <td><input tabindex="6" type="text" name="ville" value="" /></td>
            </tr>

            <tr>
              <th><label for="nom_jeune_fille" title="Nom de naissance">Nom de naissance</label></th>
              <td><input tabindex="3" type="text" name="nom_jeune_fille" value="" /></td>


              <th><label for="cp" title="Code postal du patient à rechercher">Code postal</label></th>
              <td><input tabindex="7" type="text" name="cp" value="" /></td>
            </tr>

            <tr>
              <th><label for="sexe" title="Sexe">Sexe</label></th>
              <td>
                <select name="sexe" tabindex="4">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  <option value="m">{{tr}}CPatient.sexe.m{{/tr}}</option>
                  <option value="f">{{tr}}CPatient.sexe.f{{/tr}}</option>
                </select>
              </td>

              <th> <label for="Date_Day" title="Date de naissance du patient à rechercher"> Date de naissance </label> </th>
              <td> {{mb_include module=patients template=inc_select_date date="--" tabindex=8}} </td>
            </tr>

            <tr>
              <th>Identifiant du patient</th>
              <td colspan="4">
                <input tabindex="9" type="text" name="person_id_number" value="" size="15" placeholder="ID"/> ^^^
                <input tabindex="10" type="text" name="person_namespace_id" value="" size="25" placeholder="espace de noms du domaine"/> &
                <input tabindex="11" type="text" name="person_universal_id" value="" size="30" placeholder="ID universel du domaine"/> &
                <input tabindex="12" type="text" name="person_universal_id_type" value="" size="30" placeholder="Type de l'ID universel du domaine"/>
                <input tabindex="13" type="text" name="person_identifier_type_code" value="" size="12" placeholder="Type de code"/>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>

    <tr>
      <td>
        <fieldset>
          <legend>Informations sur la venue</legend>

          <table class="form me-no-box-shadow">
            <tr>
              <th style="width: 200px"><label for="admit_class">Type d'admission</label></th>
              <td>
                <select name="admit_class">
                  <option value="">&mdash;</option>
                  {{foreach from=$sejour->_specs.type->_locales key=_type item=_spec}}
                    <option value="{{$_type}}">
                      {{tr}}CSejour.type.{{$_type}}{{/tr}}
                    </option>
                  {{/foreach}}
                </select>
              </td>

              <th><label for="admit_attending_doctor">Nom du praticien responsable</label></th>
              <td><input tabindex="27" type="text" name="admit_attending_doctor" value="" /></td>
            </tr>

            <tr>
              <th><label for="admit_service" title="Service">Service</label></th>
              <td><input tabindex="21" type="text" name="admit_service" value="" /></td>

              <th><label for="admit_referring_doctor">Nom du médecin adressant</label></th>
              <td><input tabindex="25" type="text" name="admit_referring_doctor" value="" /></td>
            </tr>

            <tr>
              <th><label for="admit_room" title="Chambre">Chambre</label></th>
              <td><input tabindex="22" type="text" name="admit_room" value="" /></td>

              <th><label for="admit_attending_doctor">Médecin traitant</label></th>
              <td><input tabindex="24" type="text" name="admit_attending_doctor" /></td>
            </tr>

            <tr>
              <th><label for="admit_bed" title="Lit">Lit</label></th>
              <td><input tabindex="23" type="text" name="admit_bed" value="" /></td>

              <th><label for="admit_consulting_doctor">Nom du praticien consultant</label></th>
              <td><input tabindex="26" type="text" name="admit_consulting_doctor" value="" /></td>
            </tr>

            <tr>
              <th>Identifiant du dossier</th>
              <td colspan="4">
                <input tabindex="30" type="text" name="admit_id_number" value="" size="15" placeholder="ID"/> ^^^
                <input tabindex="31" type="text" name="admit_namespace_id" value="" size="25" placeholder="espace de noms du domaine"/> &
                <input tabindex="32" type="text" name="admit_universal_id" value="" size="30" placeholder="ID universel du domaine"/> &
                <input tabindex="33" type="text" name="admit_universal_id_type" value="" size="30" placeholder="Type de l'ID universel du domaine"/>
                <input tabindex="34" type="text" name="admit_identifier_type_code" value="" size="12" placeholder="Type de code"/>
              </td>
            </tr>
          </table>

        </fieldset>
      </td>
    </tr>

    <tr>
      <td>
        <fieldset>
          <legend>Informations complémentaires</legend>

          <table class="form me-no-box-shadow">
            <tr>
              <th style="width: 200px">Quels domaines retourner</th>
              <td colspan="4">
                <input tabindex="40" type="text" name="domains_returned_namespace_id" value="" size="25" placeholder="espace de noms du domaine"/> &
                <input tabindex="41" type="text" name="domains_returned_universal_id" value="" size="30" placeholder="ID universel du domaine"/> &
                <input tabindex="42" type="text" name="domains_returned_universal_id_type" value="" size="30" placeholder="Type de l'ID universel du domaine"/>
              </td>
            </tr>

            <tr>
              <th><label for="quantity_limited_request" title="Limite des résultats">Limite des résultats recherchés</label></th>
              <td><input tabindex="43" type="text" name="quantity_limited_request" size="5" value="1" /></td>

              <td colspan="2"></td>
            </tr>

            <tr>
              <th><label for="pointer">Pointeur de continuation</label></th>
              <td><input type="text" name="pointer"  value="{{$pointer}}" size="60" readonly /></td>

              <th><label for="query_tag" title="Query tag">Etiquette de requête</label></th>
              <td><input type="text" name="query_tag"  value="{{$query_tag}}" size="60" readonly /></td>
            </tr>
          </table>

        </fieldset>
      </td>
    </tr>

    <tr>
      <td class="button">
        <button class="search singleclick me-primary" onclick="$V(this.form.continue, 0); $V(this.form.cancel, 0);">
          {{tr}}Search{{/tr}}
        </button>
        <button class="right singleclick transaction" {{if !$pointer}}disabled{{/if}} onclick="$V(this.form.continue, 1); $V(this.form.cancel, 0);">
          {{tr}}Continue{{/tr}}
        </button>
        <button class="cancel singleclick transaction" {{if !$pointer}}disabled{{/if}} onclick="$V(this.form.continue, 0); $V(this.form.cancel, 1);">
          Annuler la transaction
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="find_candidates"></div>