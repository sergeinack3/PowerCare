{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de lits Mediboard.</h2>

<div class="small-info">
  Le fichier d'import doit être au format ISO, les champs déparés par des <strong>;</strong> et les textes entourés par
  <strong>"</strong>
  <br /><br />
  <ul>
    <li><strong>service</strong> : {{tr}}CService-nom{{/tr}}</li>
    <li><strong>chambre</strong> : {{tr}}CChambre-nom{{/tr}}</li>
    <li><strong>lit</strong> : {{tr}}CLit-nom{{/tr}}</li>
    <li>lit_complet : {{tr}}CLit-nom_complet{{/tr}}</li>
    <li>prestas : Nom des prestations à ajouter au lit séparés par |</li>
  </ul>
  <br />

  <strong>Attention</strong> :
  <ul>
    <li>Le champ "service" est obligatoire</li>
    <li>Le champ "chambre" est obligatoire</li>
    <li>Le champ "lit" est obligatoire</li>
  </ul>
  <br />
</div>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <label for="import_file" style="margin-left: 10px;">{{tr}}File{{/tr}}</label>
  : <input id="import_file" type="file" name="import" />

  <button type="submit" class="import" style="margin-left: 10px;">{{tr}}Import{{/tr}}</button>
</form>

{{if $results|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="10">{{$results|@count}} lits trouvés</th>
    </tr>
    <tr>
      <th>Etat</th>
      <th>Service</th>
      <th>Chambre</th>
      <th>Lit</th>
      <th>Nom complet</th>
      <th>Prestations</th>
    </tr>
    {{foreach from=$results item=_lit}}
      {{if array_key_exists('error', $_lit)}}
        {{assign var=error value=true}}
      {{else}}
        {{assign var=error value=false}}
      {{/if}}
      <tr>
        <td class="text {{if $error}}error{{else}}ok{{/if}}">
          {{if $error}}
            {{$_lit.error}}
          {{else}}
            OK
          {{/if}}
        </td>
        <td>{{$_lit.service}}</td>
        <td>{{$_lit.chambre}}</td>
        <td>{{$_lit.lit}}</td>
        <td>{{$_lit.lit_complet}}</td>
        <td>{{$_lit.prestas}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

