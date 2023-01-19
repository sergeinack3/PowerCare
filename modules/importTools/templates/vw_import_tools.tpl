{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script>
  doRedirectModule = function (module, tab) {
    url = new Url();
    url.addParam("m", module);
    url.addParam("tab", tab);
    url.redirect();
  }
</script>

<div class="small-info">
  Etat du module : <br/>
  <span class="module-actif">Actif : </span> Le module est installé et actif.
  <br/>
  <span class="module-installe">Installé : </span> Le module est installé mais non actif.
  <br/>
  <span class="module-present">Présent : </span> Les fichiers du module sont présents mais le module n'est pas installé.
  <br/>
  <span class="module-absent">Absent : </span> Les fichiers du module sont absents.
</div>
<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th>Module</th>
    <th class="narrow">Etat</th>
    <th class="narrow">Lien</th>
    <th class="narrow">Documentation</th>
  </tr>

  {{foreach from=$modules key=_module_name item=_infos}}
    <tr>
      <td>
        {{if $_infos.etat != "Absent"}}
          <img src="modules/{{$_module_name}}/images/icon.png" width="16"/>
        {{/if}}
      </td>
      <td>
        {{if $_infos.etat != "Absent"}}
          {{tr}}module-{{$_infos.module_name}}-court{{/tr}}
        {{else}}
          {{$_infos.module_name}}

        {{/if}}
      </td>
      <td>
        <span class="
        {{if $_infos.etat == "Actif"}}
           module-actif
        {{else}}
          {{if $_infos.etat == "Installé"}}
            module-installe
          {{else}}
            {{if $_infos.etat == "Présent"}}
              module-present
            {{else}}
              module-absent
            {{/if}}
          {{/if}}
        {{/if}}
            ">{{$_infos.etat}}</span>
      </td>

      <td>
        <button type="button" {{if $_infos.etat != "Actif"}}disabled="disabled"{{/if}} class="link notext"
                onclick="doRedirectModule('{{$_module_name}}', 'vw_import')">Module {{$_infos.module_name}}</button>
      </td>
      <td>
        <button type="button" {{if $_infos.etat != "Actif" || !$_infos.doc_exist}}disabled="disabled"{{/if}} class="fa fa-book notext"
                onclick="doRedirectModule('{{$_module_name}}', 'vw_doc')">Documentation {{$_infos.module_name}}</button>
      </td>
    </tr>
  {{/foreach}}

</table>