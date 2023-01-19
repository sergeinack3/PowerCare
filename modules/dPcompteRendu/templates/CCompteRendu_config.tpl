{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" action="?m={{$m}}&{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}

  <table class="form me-no-align">
    <col style="width: 50%" />
    {{assign var="class" value="CCompteRendu"}}

    <tr>
      <th class="category" colspan="2">{{tr}}config-dPcompteRendu-CCompteRendu-other_params{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_enum var="default_font"
         values="Arial|Carlito|Comic Sans MS|Courier New|Georgia|Lucida Sans Unicode|Symbol|Tahoma|Times New Roman|Trebuchet MS|Verdana|ZapfDingBats"}}

    {{mb_include module=system template=inc_config_str var="default_fonts"}}

    {{mb_include module=system template=inc_config_str var="font_dir"}}

    <tr>
      <th>
        <button type="button" class="search" onclick="correctAligns();">{{tr}}CCompteRendu-correct_align{{/tr}}</button>
      </th>
      <td></td>
    </tr>

    <tr>
      <th class="category" colspan="2">
        Horodatage pour les aides à la saisie
      </th>
    </tr>

    {{mb_include module=system template=inc_config_str var="timestamp"}}

    <tr>
      <td></td>
      <td>
        <div>
          <script>
            var timestamp = getForm("editConfig")["dPcompteRendu[CCompteRendu][timestamp]"];
            var reloadfield = function() {
              var user_split = User.view.split(" ");
              var field = DateFormat.format(new Date(), timestamp.value).replace(/%p/g, user_split[1]);
              field = field.replace(/%n/g, user_split[0]);
              field = field.replace(/%i/g, user_split[1].charAt(0) + ". " + user_split[0].charAt(0) + ". ");
              $("preview").innerHTML = field;
            };
            var addfield = function(name) {
              timestamp.value += name + " ";
              reloadfield();
              };
            Main.add(function() {
              (timestamp.up()).insert({bottom: "<div style='display: inline;' id='preview'></div>"});
              timestamp.observe("keyup", reloadfield);
              reloadfield();
            });
          </script>
          <table>
            {{foreach from=$horodatage item=_horodatage key=field}}
            <tr>
              <td>
                <a href="#1" onclick="addfield('{{$_horodatage}}');">{{$_horodatage}}</a>
              </td>
              <td>
                {{tr}}config-dPcompteRendu-CCompteRendu-{{$field}}{{/tr}}
              </td>
            </tr>
            {{/foreach}}
          </table>
        </div>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>