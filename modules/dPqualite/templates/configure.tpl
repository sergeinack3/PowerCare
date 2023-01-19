{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true);
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#configure-CDocGed">{{tr}}CDocGed{{/tr}}</a></li>
  <li><a href="#configure-CFicheEi">{{tr}}CFicheEi{{/tr}}</a></li>
</ul>

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <div id="configure-CFicheEi" style="display: none;">
    <table class="form">
      {{assign var="class" value="CFicheEi"}}

      <tr>
        <th class="category" colspan="2">{{tr}}config-{{$m}}-{{$class}}{{/tr}}</th>
      </tr>

      {{assign var="var" value="mode_anonyme"}}
      {{mb_include module=system template=inc_config_bool var=$var }}

      <tr>
        <td class="button" colspan="100">
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </div>

  <div id="configure-CDocGed" style="display: none;">
    <table class="form">

      {{assign var="class" value="CDocGed"}}

      <tr>
        <th class="category" colspan="2">{{tr}}config-{{$m}}-{{$class}}{{/tr}}</th>
      </tr>

      {{assign var="var" value="_reference_doc"}}
      <tr>
        <th>
          <label for="{{$m}}[{{$class}}][{{$var}}]" title="{{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}
          </label>
        </th>
        <td>
          <input type="radio" name="{{$m}}[{{$class}}][{{$var}}]" value="1"
                 {{if $conf.$m.$class.$var == "1"}}checked="checked"{{/if}}/>
          <label for="{{$m}}[{{$class}}][{{$var}}]_1">Categorie-Chapitres-Numero</label>
          <br />
          <input type="radio" name="{{$m}}[{{$class}}][{{$var}}]" value="0"
                 {{if $conf.$m.$class.$var == "0"}}checked="checked"{{/if}}/>
          <label for="{{$m}}[{{$class}}][{{$var}}]_0">Chapitres-Categorie-Numero</label>
        </td>
      </tr>

      {{assign var="class" value="CChapitreDoc"}}

      <tr>
        <th class="category" colspan="2">{{tr}}config-{{$m}}-{{$class}}{{/tr}}</th>
      </tr>

      {{assign var="var" value="profondeur"}}
      {{mb_include module=system template=inc_config_str var=$var }}

      <tr>
        <td class="button" colspan="2">
          <button class="modify">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </div>
</form>