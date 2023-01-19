{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_pseudonymiser ajax=true}}

{{if $conf.instance_role == 'prod'}}
  <div class="small-error">
    {{tr}}system-Error-Pseudonymise cannot be done on prod{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-pseudonymise-classes");
  });
</script>

<table class="main tbl">
  <tr>
    <th>{{tr}}system-pseudonymise{{/tr}}</th>
  </tr>

  <tr>
    <td>
      <div class="small-info">
        {{tr}}system-msg-Pseudonymise explanations{{/tr}}
      </div>
    </td>
  </tr>

  <tr>
    <td>
      <table class="main">
        <col class="narrow" />

        <tr>
          <td style="vertical-align: top;">
            <ul id="tabs-pseudonymise-classes" class="control_tabs_vertical">
              {{foreach from=$pseudonymise_classes key=_class item=_fields}}
                <li>
                  <a href="#tab-pseudonymise-{{$_class}}">
                    {{tr}}{{$_class}}|pl{{/tr}}
                  </a>
                </li>
              {{/foreach}}
            </ul>
          </td>

          <td>
            {{foreach from=$pseudonymise_classes key=_class item=_fields}}
              <div id="tab-pseudonymise-{{$_class}}" style="display: none;">
                {{assign var=template value="pseudonymise/vw_pseudonymise_$_class"}}
                {{mb_include module=system template=$template}}

                <div style="text-align: center">
                  <button class="lookup" type="button" onclick="ObjectPseudonymiser.displayPseudonymise('{{$_class}}')">{{tr}}system-psuedonymise-action-display{{/tr}}</button>
                </div>
              </div>
            {{/foreach}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>