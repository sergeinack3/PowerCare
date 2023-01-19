{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=export_mediusers ajax=true}}

<div class="small-info">
  <h2 align="center">{{tr}}CMediusers-export-xml{{/tr}}</h2>
  Pour chaque "{{tr}}CMediusers{{/tr}}" de l'établissement sélectionné les éléments suivants seront exportés :
  <ul>
    <li><strong>{{tr}}CUser{{/tr}}</strong> sauf profils (CUser)</li>
    <li><strong>{{tr}}CFunctions{{/tr}}</strong> (CFunctions)</li>
    <li><strong>{{tr}}CDiscipline{{/tr}}</strong> (CDiscipline)</li>
    <li><strong>{{tr}}CSpecCPAM{{/tr}}</strong> (CSpecCPAM)</li>
    <li><strong>{{tr}}CSpecialtyAsip{{/tr}}</strong> (CSpecialtyAsip)</li>
    <li><strong>{{tr}}CAffectationUniteFonctionnelle{{/tr}}</strong> (CAffectationUniteFonctionnelle)</li>
    <li><strong>{{tr}}CUniteFonctionnelle{{/tr}} liée à une affectation</strong> (CUniteFonctionnelle)</li>
    <li><strong>{{tr}}CIdSante400|pl{{/tr}}</strong> (CIdSante400)</li>
  </ul>

  <br/>

  Si "{{tr}}CUser-template{{/tr}}" est sélectionné, uniquement les profils d'utilisateurs seront exportés.

  <br/>
  <br/>

  Pour l'export de profils aucune référence n'est exportée, seuls les champs de base du profil le sont (nom du profil, type du profil, ...)

  <br/><br/>

  De plus il est possible d'exporter pour chaque {{tr}}CMediusers{{/tr}} ou "Profil" :
  <ul>
    <li><strong>{{tr}}CPermModule{{/tr}}</strong> (CPermModule)</li>
    <li><strong>{{tr}}CPermObject{{/tr}}</strong> si la permission n'est pas sur un objet précis (CPermObject)</li>
    <li><strong>{{tr}}CPreferences{{/tr}}</strong> (CPreferences)</li>
    <li><strong>Permissions fonctionnelles</strong> (CPreferences)</li>
  </ul>
  <br/>
</div>

<hr/>

<form name="export_mediusers" method="get">
  <table class="main form">
      <tr>
        <th><label for="etablissement">{{tr}}{{if $cabinet}}CFunctions{{else}}CGroups{{/if}}{{/tr}}</label></th>
        <td>
          {{if $cabinet}}
            <input type="hidden" readonly name="etablissement" value="{{$current_group->_id}}"/>

            <select name="function">
              {{foreach from=$functions item=_function}}
                <option value="{{$_function->_id}}">
                  {{$_function}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            <select name="etablissement">
              {{foreach from=$etabs item=_etab}}
                <option value="{{$_etab->_id}}" {{if $current_group->_id == $_etab->_id}}selected{{/if}}>
                  {{$_etab->text}}
                </option>
                {{foreachelse}}
                <option value="">{{tr}}CGroups.none{{/tr}}</option>
              {{/foreach}}
            </select>
          {{/if}}
        </td>
      </tr>
      <tr>
        <th>{{tr}}CMediusers{{/tr}}</th>
        <td>
          <input type="radio" name="profile" value="0" checked/>
          <input type="radio" name="profile" value="1"/>
         {{tr}}CUser-template{{/tr}}
        </td>
      </tr>
      <tr>
        <th><label for="perms">{{tr}}CMediusers-export-perms{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="perms" value="1"/>
        </td>
      </tr>
      <tr>
        <th><label for="prefs">{{tr}}CMediusers-export-prefs{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="prefs" value="1"/>
        </td>
      </tr>
      <tr>
        <th><label for="default_prefs">{{tr}}CMediusers-export-default-prefs{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="default_prefs" value="1"/>
        </td>
      </tr>
      <tr>
        <th><label for="perms_functionnal">{{tr}}CMediusers-export-perms-functionnal{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="perms_functionnal" value="1"/>
        </td>
      </tr>
      <tr>
        <th><label for="planning">{{tr}}CMediusers-export-planning{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="planning" value="1"/>
        </td>
      </tr>
      <tr>
        <th><label for="tarification">{{tr}}CMediusers-export-tarification{{/tr}}</label></th>
        <td>
          <input type="checkbox" name="tarification" value="1"/>
        </td>
      </tr>

      <tr>
        <td class="button" colspan="2" align="center">
            <button type="button" class="fas fa-external-link-alt" onclick="ExportMediusers.submitFormExport(this.form);">{{tr}}Export{{/tr}}</button>
        </td>
      </tr>
  </table>
</form>

<div id="wait-export-mediusers"></div>
<div id="result-export-mediusers"></div>
