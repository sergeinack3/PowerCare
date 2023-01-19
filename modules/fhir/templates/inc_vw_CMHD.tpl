{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module="eai" template="inc_form_session_receiver"}}

<form name="request_mhd" action="?" method="post" onsubmit="return TestFHIR.request(this, '{{$search_type}}')">
  <input type="hidden" name="resource_type" value="DocumentReference" />
  <fieldset class="me-align-auto">
    <legend>Find Document Reference - ITI-67</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <td>
          <fieldset>
            <legend>Informations démographiques</legend>

            <table class="form me-no-box-shadow">
              <tr>
                <th style="width: 30%">Identifiant du séjour</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="encounter_id" value="" size="30" placeholder="Identifiant du séjour" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant du patient</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="patient_id" value="" size="30" placeholder="Url du patient" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant du patient.identifier</th>
                <td colspan="4">
                  <input tabindex="1" type="text" name="patient_identifier" value="" size="30" placeholder="Identifiant du patient.identifier" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant de la ressource</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="resource_id" value="" size="30" placeholder="Identifiant de la ressource" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Complément URL</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="complement_url" value="" size="30" placeholder="patient.identifier=toto" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Statut du Document Reference</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="status" value="" size="30" placeholder="Status" />
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
                  <input tabindex="14" type="radio" name="response_type" value="fhir+xml" checked />
                </td>
              </tr>
            </table>

          </fieldset>
        </td>
      </tr>

      <tr>
        <td>
          <button class="search singleclick me-primary">
            {{tr}}Search{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>

<form name="request_mhd" action="?" method="post" onsubmit="return TestFHIR.request(this, '{{$search_type}}')">
  <input type="hidden" name="resource_type" value="DocumentManifest" />

  <fieldset class="me-align-auto me-margin-top-8">
    <legend>Find Document Manifest - ITI-66</legend>

    <table class="form me-no-box-shadow">
      <tr>
        <td>
          <fieldset>
            <legend>Informations démographiques</legend>

            <table class="form me-no-box-shadow">
              <tr>
                <th style="width: 30%">Identifiant du séjour</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="encounter_id" value="" size="30" placeholder="Identifiant du séjour" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant du patient</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="patient_id" value="" size="30" placeholder="Url du patient" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant du patient.identifier</th>
                <td colspan="4">
                  <input tabindex="1" type="text" name="patient_identifier" value="" size="30" placeholder="Identifiant du patient.identifier" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Identifiant de la ressource</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="resource_id" value="" size="30" placeholder="Identifiant de la ressource" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Complément URL</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="complement_url" value="" size="30" placeholder="patient.identifier=toto" />
                </td>
              </tr>
              <tr>
                <th style="width: 30%">Statut du Document Manifest</th>

                <td colspan="4">
                  <input tabindex="1" type="text" name="status" value="" size="30" placeholder="Status" />
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
                  <input tabindex="14" type="radio" name="response_type" value="fhir+xml" checked />
                </td>
              </tr>
            </table>

          </fieldset>
        </td>
      </tr>

      <tr>
        <td>
          <button class="search singleclick me-primary">
            {{tr}}Search{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>


<fieldset class="me-align-auto me-margin-top-8">
  <legend>Fichier à envoyer - ITI-65</legend>
  <form name="get_list_file_from_nda" action="?" method="get" onsubmit="return TestFHIR.getFilesFromNDA(this, '{{$search_type}}')">
    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 30%">NDA du séjour</th>

        <td colspan="4">
          <input type="text" name="nda" value="" size="30" placeholder="Identifiant du NDA" />
        </td>
      </tr>
      <tr>
        <td>
          <button class="search singleclick me-primary">
            {{tr}}Search{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>


<div id="list_files_from_nda"></div>

<div id="request_{{$search_type}}"></div>
