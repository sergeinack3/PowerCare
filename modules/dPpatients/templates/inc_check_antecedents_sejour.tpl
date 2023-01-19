{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  deleteElements = function (form) {
    var antecedent_ids = [];
    $$('input[name="antecedent_ids"]:checked').each(function (checkbox) {
      antecedent_ids.push(checkbox.value);
    });

    var codes_cim10 = [];
    $$('input[name="cim10"]:checked').each(function (checkbox) {
      codes_cim10.push(checkbox.value);
    });

    var codes_cim10_sejour = [];
    $$('input[name="cim10_sejour"]:checked').each(function (checkbox) {
      codes_cim10_sejour.push(checkbox.value);
    });

    var url = new Url('patients', 'do_delete_antecedents_sejour', 'dosql');
    url.addParam('antecedent_ids[]', antecedent_ids, true);
    url.addParam('codes_cim10[]', codes_cim10, true);
    url.addParam('codes_cim10_sejour[]', codes_cim10_sejour, true);
    url.addParam('dossier_medical_id', '{{$dossier_medical->_id}}');
    url.addParam('dossier_medical_sejour_id', '{{$dossier_medical_sejour->_id}}');
    url.requestUpdate('systemMsg', {
      method:        'post',
      getParameters: {m: 'patients', dosql: 'do_delete_antecedents_sejour'},
      onComplete:    function () {
        Control.Modal.close();
        if (window.DossierMedical) {
          DossierMedical.reloadDossierPatient();
          DossierMedical.reloadDossierSejour();
        }
        if (window.reloadAtcd) {
          reloadAtcd();
        }
        if (window.reloadAtcdMajeur) {
          reloadAtcdMajeur();
        }
        if (window.reloadAtcdOp) {
          reloadAtcdOp();
        }
      }
    });

    return false;
  };
</script>

<form name="deleteElementsForm" method="post" action="?" onsubmit="return false;">
  <table class="form">
    <tr>
      <th class="title" colspan="2">Suppression des éléments liés à l'antécédent</th>
    </tr>
    <tr>
      <th class="title" colspan="2">
        Dossier patient
      </th>
    </tr>
    <tr>
      <th colspan="2" class="category">{{tr}}CAntecedent{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <input type="checkbox" name="antecedent_ids" value="{{$antecedent->_id}}" checked>
      </td>
      <td>
        {{$antecedent}}
      </td>
    </tr>
    {{if count($antecedent->_codes_cim10)}}
      <tr>
        <th colspan="2" class="category">Codes CIM10</th>
      </tr>
      {{foreach from=$antecedent->_codes_cim10 item=_code}}
        <tr>
          <td><input type="checkbox" name="cim10" value="{{$_code}}" checked></td>
          <td>{{$_code}}</td>
        </tr>
      {{/foreach}}
    {{/if}}
    {{if $dossier_medical_sejour->_id}}
      <tr>
        <th class="title" colspan="2">
          Eléments significatifs du séjour
        </th>
      </tr>
      <tr>
        <th colspan="2" class="category">{{tr}}CAntecedent{{/tr}}</th>
      </tr>
      <tr>
        <td>
          <input type="checkbox" name="antecedent_ids" value="{{$antecedent->_antecedent_sejour->_id}}" checked>
        </td>
        <td>
          {{$antecedent->_antecedent_sejour}}
        </td>
      </tr>
      {{if count($antecedent->_codes_cim10_sejour)}}
        <tr>
          <th colspan="2" class="category">Codes CIM10</th>
        </tr>
        {{foreach from=$antecedent->_codes_cim10_sejour item=_code}}
          <tr>
            <td><input type="checkbox" name="cim10_sejour" value="{{$_code}}" checked></td>
            <td>{{$_code}}</td>
          </tr>
        {{/foreach}}
      {{/if}}
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="trash" onclick="deleteElements();">Supprimer les éléments sélectionnés</button>
      </td>
    </tr>
  </table>
</form>