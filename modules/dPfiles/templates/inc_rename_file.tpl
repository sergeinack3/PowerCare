{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file ajax=true}}

<script>
    Main.add(function() {
        let input = getForm("editFileName").file_name;

        input.focus();
        input.caret(0, $V(input).lastIndexOf("."));
    });
</script>

<form name="editFileName" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
    {{mb_class object=$file}}
    {{mb_key   object=$file}}

    <table class="form">
        <tr>
            <td>
                {{me_form_field file=$file mb_field="file_name"}}
                    {{mb_field object=$file field="file_name" size=40}}
                {{/me_form_field}}
            </td>
            <td>
                <button type="button"
                        class="tick notext"
                        onclick="if (File.checkFileName($V(getForm('editFileName').file_name))) { this.form.onsubmit(); }">
                    {{tr}}Validate{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
