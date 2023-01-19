{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $coreModules|@count}}
  <div class="big-warning">
    Un ou plusieurs des modules de base <b>ne sont pas à jour</b>.<br />
    Des erreurs risquent de s'afficher et le système ne fonctionnera pas correctement.<br />
    Veuillez les mettre à jour, <b>en commencant par le module Administration</b>, afin de supprimer ces erreurs résultantes et avoir accès aux autres modules
  </div>
  {{mb_include template=inc_modules object=$coreModules installed=true}}

  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    Control.Tabs.create("tabs-modules", true);
    Control.Tabs.setTabCount('installed', '{{$mbmodules.installed|@count}}');
    Control.Tabs.setTabCount('notInstalled', '{{$mbmodules.notInstalled|@count}}');
  });
</script>
<ul id="tabs-modules" class="control_tabs">
  <li><a {{if $upgradable}}class="wrong"{{/if}} href="#installed">{{tr}}CModule-modules-installed{{/tr}}</a></li>
  <li><a href="#notInstalled">{{tr}}CModule-modules-notInstalled{{/tr}}</a></li>
  <li><a href="#assistant">{{tr}}module-system-assistant{{/tr}}</a></li>
</ul>

<div id="installed" style="display: none;">
  {{mb_include template="inc_modules" object=$mbmodules.installed installed=true}}
</div>

<div id="notInstalled" style="display: none;">
  {{mb_include template="inc_modules" object=$mbmodules.notInstalled installed=false}}
</div>

<div id="assistant" style="display: none;">
  <br>
  <a class="button fas fa-external-link-alt" target="_blank" href="status">
    Etat de l'instance
  </a>
</div>
