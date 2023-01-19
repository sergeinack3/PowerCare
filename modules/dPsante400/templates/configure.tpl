{{*
* @package Mediboard\Sante400
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-configure', true);
    if (tabs.activeLink.key == "CConfigEtab") {
      Configuration.edit('dPsante400', 'CGroups', $('CConfigEtab'));
    }
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CIdSante400">{{tr}}CIdSante400{{/tr}}</a></li>
  <li><a href="#CIncrementer">{{tr}}CIncrementer{{/tr}}</a></li>
  <li onmousedown="Configuration.edit('dPsante400', 'CGroups', $('CConfigEtab'))">
    <a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a>
  </li>
  <li><a href="#mouvements">Mouvements</a></li>
  <li><a href="#massReplace">{{tr}}mod-dPsante400-find_and_replace_label{{/tr}}</a></li>
</ul>

<div id="CIdSante400" style="display: none;" class="me-no-border">
    {{mb_include template=CIdSante400_configure}}
</div>

<div id="CIncrementer" style="display: none;" class="me-no-border">
    {{mb_include template=CIncrementer_configure}}
</div>

<div id="CConfigEtab" style="display: none" class="me-no-border">
</div>

<div id="mouvements" style="display: none;" class="me-no-border">
    {{mb_include template=mouvements_configure}}
</div>

<div id="massReplace" style="display: none;" class="me-no-border">
    {{mb_include template=mass_replace_configure}}
</div>
