{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Grossesse.refreshList('{{$parturiente_id}}', '{{$object_guid}}', '{{$grossesse_id}}');
    });
</script>

<table class="main layout">
    <tbody>
        <tr>
            <td style="width: 27%">
                <div id="list_grossesses"></div>
                <div class="me-text-align-right me-margin-top-10">
                    {{if $object_guid}}
                        <button id="button_select_grossesse"
                                type="button"
                                class="link"
                                onclick="Grossesse.bindGrossesse(); Control.Modal.close();">
                            {{tr}}Link{{/tr}}
                        </button>
                        <button type="button"
                                class="unlink"
                                onclick="Grossesse.emptyGrossesses(); Control.Modal.close();">
                            {{tr}}Unlink{{/tr}}
                        </button>
                    {{else}}
                        <button type="button"
                                class="cancel"
                                onclick="Control.Modal.close();">
                            {{tr}}Close{{/tr}}
                        </button>
                    {{/if}}
                </div>
            </td>
            <td>
                <div id="edit_grossesse"></div>
            </td>
        </tr>
    </tbody>
</table>
