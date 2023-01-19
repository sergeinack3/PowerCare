{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
function printRapport() {
  var form = document.selectFrm;

  var url = new Url;
  url.setModuleAction("dPgestionCab", "print_rapport");
  url.addElement(form._date_min);
  url.addElement(form._date_max);
  url.addElement(form.libelle);
  url.addElement(form.rubrique_id);
  url.addElement(form.mode_paiement_id);
  url.popup(700, 550, "Rapport");
}
</script>

<table class="main">
  <tr>
  
    <!-- Modification d'une fiche -->
    <td class="halfpane">
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$gestioncab}}
        {{mb_key object=$gestioncab}}
        <input type="hidden" name="function_id" value="{{$gestioncab->function_id}}" />
      {{if $gestioncab->gestioncab_id}}
      <a class="button new" href="?m={{$m}}&gestioncab_id=0">Créer une nouvelle fiche</a>
      {{/if}}
      <table class="form">
        <tr>
          {{if $gestioncab->_id}}
          <th class="title modify" colspan="2">
            {{mb_include module=system template=inc_object_idsante400 object=$gestioncab}}
            {{mb_include module=system template=inc_object_history object=$gestioncab}}
            Modification de la fiche '{{$gestioncab}}'
          </th>
          {{else}}
          <th class="title me-th-new" colspan="2">Création d'une nouvelle fiche</th>
          {{/if}}
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="libelle"}}</th>
          <td>{{mb_field object=$gestioncab field="libelle"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="date"}}</th>
          <td>{{mb_field object=$gestioncab field="date" form="editFrm" register=true}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="rubrique_id"}}</th>
          <td>
            <select name="rubrique_id">
             <optgroup label="{{$etablissement}}">
            {{foreach from=$listRubriques item=rubrique}}
              <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $gestioncab->rubrique_id}}selected="selected"{{/if}}>
                {{$rubrique->nom}}
              </option>
            {{/foreach}}
            </optgroup>
            <optgroup label="{{$fonction}}">
            {{foreach from=$listRubriquesFonction item=rubrique}}
              <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $gestioncab->rubrique_id}}selected="selected"{{/if}}>
                {{$rubrique->nom}}
              </option>
            {{/foreach}}
            </optgroup>
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="montant"}}</th>
          <td>{{mb_field object=$gestioncab field="montant"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="mode_paiement_id"}}</th>
          <td>
            <select name="mode_paiement_id">
            <optgroup label="{{$etablissement}}">
            {{foreach from=$listModesPaiement item=mode}}
              <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $gestioncab->mode_paiement_id}}selected="selected"{{/if}}>
                {{$mode->nom}}
              </option>
            {{/foreach}}
            </optgroup>
            <optgroup label="{{$fonction}}">
            {{foreach from=$listModePaiementFonction item=mode}}
              <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $gestioncab->mode_paiement_id}}selected="selected"{{/if}}>
                {{$mode->nom}}
              </option>
            {{/foreach}}
            </optgroup>
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="rques"}}</th>
          <td>{{mb_field object=$gestioncab field="rques"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$gestioncab field="num_facture"}}</th>
          <td>{{mb_field object=$gestioncab field="num_facture"}}</td>
        </tr>
        <tr>
          <td class="button" colspan="5">
            {{if $gestioncab->gestioncab_id}}
            <button class="modify" type="submit">Modifier</button>
            <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'la fiche',objName:'{{$gestioncab->_view|smarty:nodefaults|JSAttribute}}'})">
              Supprimer
            </button>
            {{else}}
            <button class="submit" type="submit">Créer</button>
            {{/if}}
          </td>
        </tr>
      </table>
      </form>
    </td>
    
    <!-- Recherche de fiches -->
    <td class="halfpane">
      <form name="selectFrm" action="?" method="get" onSubmit="return checkForm(this)">
      <input type="hidden" name="m" value="{{$m}}" />
      <table class="tbl">
        <tr>
          <th class="title" colspan="5">Recherche de fiches</th>
        </tr>
        <tr>
          <td>{{mb_label object=$filter field="_date_min"}}</td>
          <td>{{mb_field object=$filter field="_date_min" form="selectFrm" canNull="false" register=true}}</td>
          <td class="button" colspan="3">
            <button type="submit" class="print" onclick="printRapport()">Imprimer</button>
          </td>
        </tr>
        <tr>
          <td>{{mb_label object=$filter field="_date_max"}}</td>
          <td>{{mb_field object=$filter field="_date_max" form="selectFrm"  canNull="false" register=true}}</td>
          <td class="button" colspan="3">
            <button type="submit" class="search">Afficher</button>
          </td>
        </tr>
        <tr>
          <th class="category">Date</th>
          <th>{{mb_label object=$filter field="libelle"}}
      <br />
            {{mb_field object=$filter field="libelle" canNull="true"}}
          </th>
          <th class="category">
            {{mb_label object=$filter field="rubrique_id"}}
            <br />
            <select name="rubrique_id" onchange="this.form.submit()">
              <option value="">&mdash; toutes</option>
              <optgroup label="{{$etablissement}}">
              {{foreach from=$listRubriques item=rubrique}}
                <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $filter->rubrique_id}}selected="selected"{{/if}}>
                  {{$rubrique->nom}}
                </option>
              {{/foreach}}
              </optgroup>
              <optgroup label="{{$fonction}}">
              {{foreach from=$listRubriquesFonction item=rubrique}}
                <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $filter->rubrique_id}}selected="selected"{{/if}}>
                  {{$rubrique->nom}}
                </option>
              {{/foreach}}
              </optgroup>
            </select>
          </th>
          <th class="category">
            {{mb_label object=$filter field="mode_paiement_id"}}
            <br />
            <select name="mode_paiement_id" onchange="this.form.submit()">
              <option value="">&mdash; tous</option>
              <optgroup label="{{$etablissement}}">
              {{foreach from=$listModesPaiement item=mode}}
                <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $filter->mode_paiement_id}}selected="selected"{{/if}}>
                  {{$mode->nom}}
                </option>
              {{/foreach}}
              </optgroup>
              <optgroup label="{{$fonction}}">
              {{foreach from=$listModePaiementFonction item=mode}}
                <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $filter->mode_paiement_id}}selected="selected"{{/if}}>
                  {{$mode->nom}}
                </option>
              {{/foreach}}
              </optgroup>
            </select>
          </th>
          <th class="category">Montant</th>
        </tr>
        {{foreach from=$listGestionCab item=fiche}}
        <tr>
          <td>
            <a href="?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{mb_value object=$fiche field="date"}}
            </a>
          </td>
          <td>
            <a href="?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{mb_value object=$fiche field="libelle"}}
            </a>
          </td>
          <td>
            <a href="?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{mb_value object=$fiche->_ref_rubrique field="nom"}}
            </a>
          </td>
          <td>
            <a href="?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{mb_value object=$fiche->_ref_mode_paiement field="nom"}}
            </a>
          </td>
          <td>
            <a href="?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{mb_value object=$fiche field="montant"}}
            </a>
          </td>
        </tr>
        {{/foreach}}
      </table>
      </form>
    </td>
  </tr>
</table>