{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Control.Tabs.create('tabs-configure', true, {
            afterChange: function (container) {
                if (container.id == "jfse-configuration") {
                    Configuration.edit('jfse', ['CGroups'], $('jfse-configuration'));
                }
            }
        });
    });
</script>

<ul id="tabs-configure" class="control_tabs">
    <li><a href="#jfse-versions">{{tr}}Version|pl{{/tr}}</a></li>
    <li><a href="#jfse-http-source">{{tr}}CSourceHTTP{{/tr}}</a></li>
    <li><a href="#jfse-configuration">{{tr}}CConfiguration{{/tr}}</a></li>
</ul>

<div id="jfse-versions" style="display: none;">
    {{mb_include module=jfse template=version/index}}
</div>

<div id="jfse-http-source" style="display: none;">
    {{mb_include module=system template=inc_config_exchange_source source=$source}}
</div>

<div id="jfse-configuration" style="display: none"></div>
