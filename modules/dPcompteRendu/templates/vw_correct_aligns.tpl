{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  correctAlign = function(compte_rendu_id) {
    var form = getForm("doCorrectAlign");
    $V(form.compte_rendu_id, compte_rendu_id);
    onSubmitFormAjax(form, function() { urlCorrectAlign.refreshModal(); });
  };

  openModele = function(compte_rendu_id) {
    new Url("compteRendu", "addedit_modeles")
      .addParam("compte_rendu_id", compte_rendu_id)
      .modal({
        width:  "95%",
        height: "90%",
        onClose: function() {
          urlCorrectAlign.refreshModal();
        },
        closeOnEscape: false,
        waitingText: true
      });
  }
</script>

<form name="doCorrectAlign" method="post">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_correct_align" />
  <input type="hidden" name="compte_rendu_id" />
</form>

<table class="tbl">
  <tr>
    <th class="title" colspan="3">Liste des modèles ({{$compte_rendus|@count}} / {{$count}})</th>
  </tr>

  <tr>
    <th class="narrow"></th>
    <th style="width: 50%;">Nom</th>
    <th>Propriétaire</th>
  </tr>

  {{foreach from=$compte_rendus item=_compte_rendu}}
  <tr>
    <td>
      <button type="button" class="tick" onclick="correctAlign('{{$_compte_rendu->_id}}')">Traiter</button>
    </td>
    <td>
      <a href="#1" onclick="openModele('{{$_compte_rendu->_id}}');">{{mb_value object=$_compte_rendu field=nom}}</a>
    </td>
    <td>
      {{if $_compte_rendu->user_id}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_compte_rendu->_ref_user}}
      {{elseif $_compte_rendu->function_id}}
        {{mb_include module=mediusers template=inc_vw_function function=$_compte_rendu->_ref_function}}
      {{else}}
        {{mb_include module=etablissement template=inc_vw_group group=$_compte_rendu->_ref_group}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="3">
      {{tr}}CCompteRendu.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>