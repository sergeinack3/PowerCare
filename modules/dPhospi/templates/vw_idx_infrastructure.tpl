{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-chambres', true);
  });
</script>

{{mb_script module=hospi script=infrastructure ajax=1}}
{{mb_script module=hospi script=affectation_uf ajax=1}}

<ul id="tabs-chambres" class="control_tabs">
  <li><a href="#secteurs">{{tr}}CSecteur{{/tr}} {{if $secteurs|@count}}({{$secteurs|@count}}){{/if}}</a></li>
  <li><a href="#services">{{tr}}common-CService-CChambre-CLit{{/tr}} {{if $services|@count}}({{$services|@count}}){{/if}}</a></li>
  <li><a href="#UF">{{tr}}CUniteFonctionnelle{{/tr}}</a></li>
</ul>

<div id="secteurs" style="display: none;">
  {{mb_include template=inc_vw_idx_secteurs}}
</div>

<div id="services" style="display: none;">
  {{mb_include template=inc_vw_idx_services}}
</div>

<div id="UF" style="display: none;">
  {{mb_include template=inc_vw_idx_ufs}}
</div>