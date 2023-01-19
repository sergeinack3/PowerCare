{{mb_script module=api script=api}}

  <script>
    Main.add(function () {
      Control.Tabs.create('tabs_configure', true, {
        afterChange: function (container) {
          if (container.id === "CConfigAPI") {
            Configuration.edit('api', ['CGroups'], $('CConfigAPI'));
          }
        }
      });
    });
  </script>

  <ul id="tabs_configure" class="control_tabs small">
    {{foreach from=$configuration key=_tab item=_api}}
      <li><a href="#config-{{$_tab}}">{{tr}}{{$_tab}}{{/tr}}</a></li>
    {{/foreach}}
    <li><a href="#config-configuration">{{tr}}config-configuration{{/tr}}</a></li>
    <li><a href="#CConfigAPI">{{tr}}config-Api configuration{{/tr}}</a></li>
  </ul>


  {{foreach from=$configuration key=_tab item=_api}}
    <div id="config-{{$_tab}}" style="display: none;">
      {{mb_include module=api template=inc_config_api}}
    </div>
  {{/foreach}}

  <div id="config-configuration" style="display: none;">
    {{mb_include module=api template=inc_configure_idex}}
  </div>

  <div id="CConfigAPI" style="display: none;"></div>