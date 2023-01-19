{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module="eai" template="inc_form_session_receiver"}}

<form name="request_pixm" action="?" method="post" onsubmit="return TestFHIR.request(this, '{{$search_type}}')">
  <fieldset class="me-align-auto me-margin-bottom-8">
    <legend>Informations démographiques</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 30%">Identifiant du patient</th>

        <td colspan="4">
          <input tabindex="1" type="text" name="person_namespace_id" value="" size="30"
                 placeholder="OID des noms de domaines" /> |
          <input tabindex="2" type="text" name="person_id_number" value="" size="15" placeholder="ID" />
        </td>
      </tr>
    </table>
  </fieldset>

  <fieldset class="me-align-auto">
    <legend>Informations complémentaires</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 30%">Quels domaines retourner (séparer par des virgules)</th>
        <td colspan="4">
          <input tabindex="3" type="text" name="identity_domain_oid" value="" size="35"
                 placeholder="OID des domaines" />
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
