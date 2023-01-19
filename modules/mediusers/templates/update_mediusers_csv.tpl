{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import d'utilisateurs Mediboard.</h2>

<div class="small-info">
  Veuillez indiquez les champs suivants dans un fichier CSV (<strong>au format ISO</strong>) dont les champs sont séparés par
  <strong>;</strong> et les textes par <strong>"</strong> :
  <ul>
    <li>Nom *</li>
    <li>Prénom *</li>
    <li>Adeli</li>
  </ul>
  <em>* : champs obligatoires</em>
</div>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{if $results|@count}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="3">{{$results|@count}} utilisateurs trouvés</th>
    </tr>
    <tr>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Etat</th>
    </tr>
    {{foreach from=$results item=_user}}
    <tr>
      <td class="narrow">{{$_user.lastname}}</td>
      <td class="narrow">{{$_user.firstname}}</td>
      <td class="text {{if !$_user.error}}ok{{else}}error{{/if}}">
        {{if $_user.error}}
          {{$_user.error}}
        {{/if}}
      </td>
    </tr>
    {{/foreach}}
  </table>
{{/if}}