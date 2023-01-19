{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $admin_admission}}
  <script>
    // A la fermeture de la modale, actions qui peuvent être effectuées
    someReload = function () {
      {{if isset($patient_id|smarty:nodefaults)}}
      // Rechargement du dossier patient
      if (window.reloadPatient && "{{$patient_id}}") {
        reloadPatient("{{$patient_id}}");
      }
      {{/if}}
      // Rechargement des pré-admissions
      if (window.reloadPreAdmission) {
        reloadPreAdmission();
      }
      // Rechargement des admissions
      if (window.reloadAdmission) {
        reloadAdmission();
      }
      // Rechargement des sorties
      if (window.reloadSorties) {
        reloadSorties();
      }
      // On relance le periodical updater de l'identito-vigilance
      if (window.IdentitoVigilance) {
        IdentitoVigilance.start(0, 60);
      }
    };

    Main.add(function () {
      if (window.IdentitoVigilance) {
        IdentitoVigilance.stop();
      }
      var div = getForm("editTags").up("div").up("div");
      var cancel_button = div.down("button.close");
      var reload_button = div.down("button.change");

      cancel_button.observe("click", someReload);
      reload_button.observe("click", function () {
        cancel_button.stopObserving("click", someReload);
      });
    });

    trashNDA = function (idex_id) {
      new Url("sante400", "ajax_trash_id400")
        .addParam("idex_id", idex_id)
        .requestUpdate("systemMsg", function () {
          getForm("editTags").up("div").up("div").down("button.change").click();
        });
    }
  </script>
{{/if}}

<form name="editTags" method="get">
  <table class="tbl">
    <tr>
      <th class="title" colspan="{{if !$admin_admission}}5{{else}}6{{/if}}">
        {{tr}}CMbObject-back-identifiants{{/tr}}
      </th>
    </tr>
    <tr>
      {{if $admin_admission}}
        <th></th>
        <th>{{mb_title class=CIdSante400 field=object_class}}</th>
        <th>{{mb_title class=CIdSante400 field=last_update}}</th>
        <th>{{mb_title class=CSejour     field=_NDA}}</th>
        <th>{{mb_title class=CIdSante400 field=tag}}</th>
        <th>{{tr}}CIdSante400-_type{{/tr}}</th>
      {{/if}}
    </tr>
    {{foreach from=$idexs item=_idex}}
      <tr>
        {{if $admin_admission && $sip_active}}
          <td>
            <input type="radio" name="radio[]" {{if $_idex->_id == $idex_id}}checked{{/if}}
                   onchange="trashNDA('{{$_idex->_id}}');"/>
          </td>
        {{else}}
          <td></td>
        {{/if}}
        <td>{{$_idex->object_class}}</td>

        <td>{{$_idex->last_update|date_format:$conf.datetime}}</td>
        <td>{{$_idex->id400}}</td>
        <td>{{$_idex->tag}}</td>
        <td>
          {{if $_idex->_type}}
            <span class="idex-special idex-special-{{$_idex->_type}}">
          {{$_idex->_type}}
        </span>
          {{/if}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="6">{{tr}}CIdSante400.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>

{{if !$admin_admission}}
  <div class="info">
    {{tr}}CIdSante400.cannot_modify_id400{{/tr}}
  </div>
{{/if}}