{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="idinterpreter-loading" style="width: 99%; text-align: center;" class="loading">
  <span>{{tr}}Loading in progress{{/tr}}</span>
</div>

<div id="idinterpreter-result" style="display:none">
  <form name="idinterpreter-result" method="get" onsubmit="return IdInterpreter.submitFields(this);">
    <table class="tbl form" style="width: 300px;">
      <tr>
        <th class="title" colspan="3">{{tr}}CIdInterpreter.extracted_data{{/tr}}</th>
        <th class="title">{{tr}}CIdInterpreter.initial_image{{/tr}}</th>
      </tr>
      <tr>
        <th class="narrow"></th>
        <th>{{tr}}common-Label{{/tr}}</th>
        <th>{{tr}}common-Value{{/tr}}</th>
        <td rowspan="9" id="idinterpreter-show-container">
          <img id="idinterpreter-show-file" style="max-height : 100%; max-width: 400px">
        </td>
      </tr>
      <tr>
        <td><input type="checkbox" name="patient_prenom" value="prenom" checked disabled /></td>
        <td>{{mb_label class=CPatient field=prenom}}</td>
        <td>{{mb_field class=CPatient field=prenom canNull=true}}</td>
      </tr>
      <tr>
        <td><input type="checkbox" name="patient_nom_jeune_fille" value="nom_jeune_fille" checked disabled /></td>
        <td>{{mb_label class=CPatient field=nom_jeune_fille}}</td>
        <td>{{mb_field class=CPatient field=nom_jeune_fille canNull=true}}</td>
      </tr>
      <tr>
        <td><input type="checkbox" name="patient_sexe" value="sexe" checked disabled /></td>
        <td>{{mb_label class=CPatient field=sexe}}</td>
        <td>{{mb_field class=CPatient field=sexe canNull=true}}</td>
      </tr>
      <tr>
        <td><input type="checkbox" name="patient_naissance" value="naissance" checked disabled /></td>
        <td>{{mb_label class=CPatient field=naissance}}</td>
        <td>{{mb_field class=CPatient field=naissance canNull=true}}</td>
      </tr>
      <tr>
        <td><input type="checkbox" name="patient_image" value="image"/></td>
        <td>{{tr}}CMediusers-ID photo{{/tr}}</td>
        <td><img src="" alt="" style="width: 200px;" id="idinterpreter-image"/></td>
      </tr>
      <tr>
        <td class="button" colspan="4">
            <button class="import">{{tr}}CIdInterpreter.report_data{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<form name="idinterpreter-update-files" enctype="multipart/form-data" class="prepared"
      method="post" style="display:none">
  <input type="hidden" name="m" value="files" />
  <input type="hidden" name="dosql" value="do_file_aed" />
  <input type="hidden" name="object_class" value="CPatient" />
  <input type="hidden" name="object_id" value="{{$patient_id}}" />
  <input type="hidden" name="named" value="1" />
  <div></div>
</form>
