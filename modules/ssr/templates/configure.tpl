{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true, {
      afterChange: function(container) {
        if (container.id == "CConfigEtab") {
          Configuration.edit('ssr', ['CGroups'], $('CConfigEtab'));
        }
      }
    });
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CBilanSSR">{{tr}}CBilanSSR{{/tr}}</a></li>
  <li><a href="#CFicheAutonomie">{{tr}}CFicheAutonomie{{/tr}}</a></li>
  <li><a href="#CCdARRObject">{{tr}}CCdARRObject{{/tr}}</a></li>
  <li><a href="#CCsARRObject">{{tr}}CCsARRObject{{/tr}}</a></li>
  <li><a href="#CPrestaSSR">{{tr}}CPrestaSSR{{/tr}}</a></li>
  <li><a href="#CReplacement">{{tr}}CReplacement{{/tr}}</a></li>
  <li><a href="#gui">{{tr}}GUI{{/tr}}</a></li>
  <li><a href="#offline">{{tr}}Offline{{/tr}}</a></li>
  <li><a href="#CPrescription">{{tr}}CPrescription{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#tools">{{tr}}Tools{{/tr}}</a></li>
</ul>

<div id="CBilanSSR" style="display: none;">
  {{mb_include module=ssr template=CBilanSSR_configure}}
</div>

<div id="CFicheAutonomie" style="display: none;">
  {{mb_include module=ssr template=CFicheAutonomie_configure}}
</div>

<div id="CCdARRObject" style="display: none;">
  {{mb_include module=ssr template=CCdARRObject_configure}}
</div>

<div id="CCsARRObject" style="display: none;">
  {{mb_include module=ssr template=CCsARRObject_configure}}
</div>

<div id="CPrestaSSR" style="display: none;">
  {{mb_include module=ssr template=CPrestaSSR_configure}}
</div>

<div id="CReplacement" style="display: none;">
  {{mb_include module=ssr template=CReplacement_configure}}
</div>

<div id="gui" style="display: none;">
  {{mb_include module=ssr template=inc_configure_gui}}
</div>

<div id="offline" style="display: none;">
  {{mb_include module=ssr template=inc_configure_offline}}
</div>

<div id="CPrescription" style="display: none">
  {{mb_include module=ssr template=CPrescription_configure}}
</div>

<div id="CConfigEtab" style="display: none"></div>

<div id="tools" style="display: none;">
  {{mb_include module=ssr template=inc_tools}}
</div>