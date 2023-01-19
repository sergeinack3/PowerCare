{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var Sejour = {
    select: function(Numdos, IPP) {
      window.opener.SejourHprimSelector.set(Numdos, IPP);
      window.close();
    }
  }
</script>

<div class="big-info">
  <strong>L'identifiant patient permanent (IPP)</strong> et/ou <strong>le numéro de dossier</strong>
  sont manquant, veuillez les indiquer en choisissant un séjour parmi la liste proposée ou bien
  le rentrer manuellement dans le formulaire en bas.
</div>

<!-- Formulaire de recherche -->
<form name="patientSearch" method="get">
  <input type="hidden" name="m" value="hprim21" />
  <input type="hidden" name="a" value="sejour_hprim_selector" />
  <input type="hidden" name="dialog" value="1" />

  <table class="form">
    <tr>
      <th class="category" colspan="6">{{tr}}common-Selection criteria{{/tr}}</th>
    </tr>

    <tr>
      <th><label for="name" title="Nom du patient à rechercher, au moins les premières lettres">Nom</label></th>
      <td><input name="name" value="{{$name|stripslashes}}" size="30" tabindex="1" /></td>

      <th><label for="nomjf" title="Nom de naissance">Nom de naissance</label></th>
      <td><input name="nomjf" value="{{$nomjf|stripslashes}}" size="30" tabindex="3" /></td>

      <td></td>
    </tr>

    <tr>
      <th><label for="firstName" title="Prénom du patient à rechercher, au moins les premières lettres">Prénom</label></th>
      <td><input name="firstName" value="{{$firstName|stripslashes}}" size="30" tabindex="2" /></td>
  
      <th><label for="naissance" title="Date de naissance">Date de naissance</label></th>
      <td>
        {{mb_include module=patients template=inc_select_date date=$datePat tabindex=4}}
      </td>
      <td><button class="search" type="submit">Rechercher</button></td>
    </tr>
  </table>
</form>

<!-- Liste de patients -->
<table class="tbl">
  <tr>
    <th class="category" colspan="5">Choisissez un patient dans la liste</th>
  </tr>
  <tr>
    <th>Patient</th>
    <th>Date de naissance</th>
    <th>Téléphone</th>
    <th>Mobile</th>
    <th>Actions</th>
  </tr>

  <!-- Recherche exacte -->
  {{foreach from=$patients item=_patient}}
    {{mb_include module=hprim21 template=inc_line_sejour_hprim_selector}}
  {{foreachelse}}
  {{if $name || $firstName}}
  <tr>
    <td class="button empty" colspan="5">
      Aucun résultat exact
    </td>
  </tr>
  {{/if}}
  {{/foreach}}
  <tr>
    <td class="button" colspan="5">
      <button class="cancel" type="button" onclick="window.close()">Annuler</button>
    </td>
  </tr>

  <!-- Recherche phonétique -->
  {{if $patientsSoundex|@count}}
  <tr>
    <th colspan="5">
      <em>Résultats proches</em>
    </th>
  </tr>
  {{/if}}

  {{foreach from=$patientsSoundex item=_patient}}
    {{mb_include module=hprim21 template=inc_line_sejour_hprim_selector}}
  {{/foreach}}
</table>


<form action="?m={{$m}}&tab={{$tab}}" onsubmit="if (checkForm(this)) { Sejour.select(this.numdos.value, {{if $IPP}}null{{else}}this.IPP.value{{/if}}); } return false;">
  <table class="form">
    <tr>
      <th colspan="2" class="category">Saisie manuelle</th>
    </tr>
    <tr>
      <th><label for="IPP">IPP</label></th>
      <td><input class="notNull" name="IPP" type="text" value="{{$IPP}}" tabindex="7" /></td>
    </tr>
    <tr>
      <th><label for="numdos">Numéro de dossier</label></th>
      <td><input class="notNull" name="numdos" type="text" value="" tabindex="8" /></td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
