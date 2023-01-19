{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="sip" script="SIP" ajax=true}}

{{mb_include module="hl7" template="inc_form_session_receiver"}}

<form name="patient_identity_consumer" method="post" action="?m=sip&a=ajax_patient_identity_consumer" onsubmit="return SIP.patient_identity_consumer(this)">
  <table class="form">
    <tr>
      <th class="category">Rapprochement entre identités</th>
    </tr>

    <tr>
      <td>
        <fieldset>
          <legend>Informations démographiques</legend>

          <table class="form">
            <tr>
              <th style="width: 200px">Identifiant du patient</th>

              <td colspan="4">
                <input tabindex="9" type="text" name="person_id_number" value="" size="15" placeholder="ID"/> ^^^
                <input tabindex="10" type="text" name="person_namespace_id" value="" size="30" placeholder="Espace de noms du domaine"/> &
                <input tabindex="11" type="text" name="person_universal_id" value="" size="30" placeholder="ID universel du domaine"/> &
                <input tabindex="12" type="text" name="person_universal_id_type" value="" size="30" placeholder="Type de l'ID universel du domaine"/>
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

          <table class="form">
            <tr>
              <th style="width: 200px">Quels domaines retourner</th>

              <td colspan="4">
                <input tabindex="40" type="text" name="domains_returned_namespace_id" value="" size="30" placeholder="Espace de noms du domaine"/> &
                <input tabindex="41" type="text" name="domains_returned_universal_id" value="" size="30" placeholder="ID universel du domaine"/> &
                <input tabindex="42" type="text" name="domains_returned_universal_id_type" value="" size="35" placeholder="Type de l'ID universel du domaine"/>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>

    <tr>
      <td class="button">
        <button class="search singleclick">
          {{tr}}Search{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="patient_identity_consumer"></div>