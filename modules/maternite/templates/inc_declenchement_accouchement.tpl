{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  afterDeclenchement = function (sejour_id) {
    // On ferme la modale de déclenchement
    Control.Modal.close();

    var callback = window.parent && window.parent.Placement ? window.parent.Placement.refreshEtiquette.curry(sejour_id) : null;

    // On ouvre le formulaire d'intervention
    Operation.editFast(null, sejour_id, callback);
  };
</script>

<div class="small-info">
  Vous êtes sur le point de déclencher l'accouchement de la patiente.
  <br />
  <br />

  Cette action entraîne les actions suivantes :

  <ul>
    <li>Clôture de la consultation</li>
    <li>Fusion éventuelle avec le séjour d'accouchement prévu</li>
    <li>Création ou modification de l'intervention</li>
  </ul>
</div>

<form name="mergeSejours" method="post"
      onsubmit="submitAll(); submitConsultWithChrono(64); onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="dosql" value="do_declenchement_accouchement" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="callback" value="afterDeclenchement" />

  {{if $sejours|@count}}
    <table class="tbl">
      <tr>
        <th class="title" colspan="4">
          Séjour à fusionner
        </th>
      </tr>
      <tr>
        <th>Séjour</th>
        <th>Type d'admission</th>
        <th>Motif complet</th>
        <th>Praticien</th>
      </tr>
      {{foreach from=$sejours item=_sejour key=_sejour_id name=sejour}}
        <tr>
          <td>
            <label>
              <input type="radio" name="sejour_id_merge" value="{{$_sejour->_id}}" {{if $smarty.foreach.sejour.first}}checked{{/if}} />
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
            {{$_sejour}}
          </span>
            </label>
          </td>
          <td>
            {{mb_value object=$_sejour field=type}}
          </td>
          <td>
            {{mb_value object=$_sejour field=libelle}}
          </td>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
          </td>
        </tr>
      {{/foreach}}
    </table>
  {{/if}}

  <table class="form">
    {{mb_include module=maternite template=inc_modalites_accouchement form=mergeSejours}}
  </table>

  <table class="main">
    <tr>
      <td class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit();">
          {{if $sejours|@count >= 1 && $sejour->_id}}
            {{tr}}Merge{{/tr}}
          {{else}}
            {{tr}}Confirm{{/tr}}
          {{/if}}
        </button>

        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>