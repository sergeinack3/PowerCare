{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  setMedecinTraitant = function (medecin_id, view) {
    var form = getForm('traitant-edit-{{$patient_id}}');
    $V(form.medecin_traitant, medecin_id);
    $V(form._view, view);
    Control.Modal.close();
  };
</script>

{{if $medecins|is_array}}
  <table class="tbl">
    <tr>
      <th>{{mb_title class=CMedecin field=nom}}</th>
      <th>{{mb_title class=CMedecin field=prenom}}</th>
      <th>{{mb_title class=CMedecin field=sexe}}</th>
      <th>{{mb_title class=CMedecin field=adresse}}</th>
      <th>{{mb_title class=CMedecin field=type}}</th>
      <th>{{mb_title class=CMedecin field=disciplines}}</th>
      <th>{{mb_title class=CMedecin field=tel}}</th>
      <th>{{mb_title class=CMedecin field=fax}}</th>
      <th>{{mb_title class=CMedecin field=email}}</th>
      <th class="narrow"></th>
    </tr>
    {{foreach from=$medecins item=_medecin}}
      <tr>
        <td>{{mb_value object=$_medecin field=nom}}</td>
        <td>{{mb_value object=$_medecin field=prenom}}</td>
        <td>{{mb_value object=$_medecin field=sexe}}</td>
        <td>{{mb_value object=$_medecin field=adresse}}</td>
        <td>{{mb_value object=$_medecin field=type}}</td>
        <td>{{mb_value object=$_medecin field=disciplines}}</td>
        <td>{{mb_value object=$_medecin field=tel}}</td>
        <td>{{mb_value object=$_medecin field=fax}}</td>
        <td>{{mb_value object=$_medecin field=email}}</td>
        <td class="narrow">
          <form name="CMedecin-{{$_medecin->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, setMedecinTraitant.curry('{{$_medecin->_id}}', '{{$_medecin->_view}}'));">
            {{mb_class object=$_medecin}}
            {{mb_key object=$_medecin}}
            {{mb_field object=$_medecin field=user_id hidden=true}}
            <button type="button" class="tick notext" onclick="this.form.onsubmit();">{{tr}}Select{{/tr}}</button>
          </form>
        </td>
      </tr>
    {{/foreach}}
  </table>
{{else}}
  <script>
    Main.add(function () {
      setMedecinTraitant('{{$medecins->_id}}', '{{$medecins->_view}}');
    });
  </script>
  <div class="small-info">
    {{tr}}CMedecin-msg-link_to_user{{/tr}}
  </div>
{{/if}}
