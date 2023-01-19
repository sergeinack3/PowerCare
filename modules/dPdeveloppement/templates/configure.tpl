{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry("ide-integration-tabs", true));
</script>

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  
  <table class="form">
    <col style="width: 50%;" />
    <tr>
      <th class="title" colspan="2">
        Intégration de l'IDE
      </th>
    </tr>

    <tr>
      <td colspan="2">
        <div class="small-info">
          Le <a href="http://plugins.jetbrains.com/plugin/6027" target="_blank">plugin RemoteCall pour PhpStorm</a> permet de
          spécifier une URL pour ouvrir les fichiers, avec l'URL suivante : <code>http://localhost:8091?message=%file%</code>
        </div>
      </td>
    </tr>
    {{mb_include module=system template=inc_config_str var=ide_url size=70}}

    <tr>
      <th class="title" colspan="2">
        Chemin vers le dépôt externe
      </th>
    </tr>
    <tr>
      <td colspan="2">
        <div class="small-info"></div>
      </td>
    </tr>
    {{mb_include module=system template=inc_config_str var=external_repository_path size=70}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
