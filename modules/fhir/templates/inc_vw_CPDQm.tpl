{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module="eai" template="inc_form_session_receiver"}}

<form name="request_pdqm" action="?" method="post" onsubmit="return TestFHIR.request(this, '{{$search_type}}')">
  <fieldset class="me-margin-bottom-8 me-align-auto">
    <legend>Informations démographiques</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 200px"><label for="nom" title="Nom du patient à rechercher, au moins les premières lettres">Nom</label>
        </th>
        <td><input tabindex="1" type="text" name="nom" value="" /></td>

        <th><label for="adresse" title="Adresse du patient à rechercher">Adresse</label></th>
        <td><input tabindex="6" type="text" name="adresse" value="" /></td>
      </tr>

      <tr>
        <th><label for="prenom" title="Prénom du patient à rechercher, au moins les premières lettres">Prénom</label></th>
        <td><input tabindex="2" type="text" name="prenom" value="" /></td>

        <th><label for="ville" title="Ville du patient à rechercher">Ville</label></th>
        <td><input tabindex="7" type="text" name="ville" value="" /></td>
      </tr>

      <tr>
        <th><label for="nom_jeune_fille" title="Nom de naissance">Nom de naissance</label></th>
        <td><input tabindex="3" type="text" name="nom_jeune_fille" value="" /></td>


        <th><label for="cp" title="Code postal du patient à rechercher">Code postal</label></th>
        <td><input tabindex="8" type="text" name="cp" value="" /></td>
      </tr>

      <tr>
        <th><label for="sexe" title="Sexe">{{tr}}CPatient-sexe{{/tr}}</label></th>
        <td>
          <label for="sexe_m">{{tr}}CPatient.sexe.m{{/tr}}</label>
          <input tabindex="4" type="radio" name="sexe" value="male" />
          <label for="sexe_f">{{tr}}CPatient.sexe.f{{/tr}}</label>
          <input tabindex="5" type="radio" name="sexe" value="female" />
          <label for="sexe_f">{{tr}}None{{/tr}}</label>
          <input tabindex="6" type="radio" name="sexe" value="" />
        </td>

        <th><label for="Date_Day" title="Date de naissance du patient à rechercher"> Date de naissance </label></th>
        <td> {{mb_include module=patients template=inc_select_date date="--" tabindex=9}} </td>
      </tr>

      <tr>
        <th>{{tr}}CPatient-email{{/tr}}</th>
        <td colspan="3"><input type="text" name="email" value="" /></td>
      </tr>

      <tr>
        <th><label for="id" title="ID">{{tr}}CPatient-patient_id{{/tr}} (_id)</label></th>
        <td>
          <input tabindex="10" type="text" name="id" value="" />
        </td>

        <th style="width: 30%">Identifier</th>
        <td colspan="4">
          <input tabindex="1" type="text" name="person_namespace_id" value="" size="30"
                 placeholder="Espace de noms du domaine" /> |
          <input tabindex="2" type="text" name="person_id_number" value="" size="15" placeholder="ID" />
        </td>
      </tr>
    </table>
  </fieldset>

  <fieldset class="me-align-auto">
    <legend>Informations complémentaires</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 200px">Quels domaines retourner (séparer par des virgules)</th>
        <td>
          <input tabindex="10" type="text" name="identity_domain_oid" value="" size="35"
                 placeholder="OID des domaines" />
        </td>
      </tr>

      <tr>
        <th style="width: 200px">Pagination</th>
        <td>
          <input tabindex="10" type="text" name="count" value="10" size="5" />
        </td>
      </tr>

      <tr>
        <th><label for="response_type" title="Format de la réponse">Format de la réponse</label></th>
        <td>
          <label for="response_type">fhir+json</label>
          <input tabindex="13" type="radio" name="response_type" value="fhir+json" />
          <label for="response_type_xml">fhir+xml</label>
          <input tabindex="14" type="radio" name="response_type" value="fhir+xml" checked/>
        </td>
      </tr>
    </table>
  </fieldset>

  <button class="search singleclick me-primary me-margin-8">
    {{tr}}Search{{/tr}}
  </button>
</form>

<div id="request_{{$search_type}}"></div>

