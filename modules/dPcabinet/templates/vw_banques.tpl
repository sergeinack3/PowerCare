{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete ajax=1}}
{{mb_script module=cabinet  script=banque_edit  ajax=1}}

<table class="main layout">
    <tr>
        <td style="width: 50%;">
            <button class="button new" onclick="BanqueEdit.edit(null, null, false);">
                {{tr}}CBanque-title-create{{/tr}}
            </button>

            <table class="tbl">
                <tr>
                    <th class="category">{{mb_title class=CBanque field=nom}}</th>
                    <th class="category">{{mb_title class=CBanque field=description}}</th>
                </tr>
                {{foreach from=$banks item=_bank}}
                    <tr>
                        <td>
                            <a href="#" onclick="BanqueEdit.edit({{$_bank->_id}}, this, false)">{{$_bank->nom}}</a>
                        </td>
                        <td class="text">
                            {{mb_value object=$_bank field=description}}
                        </td>
                    </tr>
                {{/foreach}}
            </table>
        </td>
        <td id="banque_edit_container">
        </td>
    </tr>
</table>
