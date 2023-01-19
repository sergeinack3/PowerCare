{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=vaccination ajax=$ajax}}

<div id="choice_nb_etiquette">
    <form method="post" name="choice_nb_etiquette">
        <input type="hidden" name="object_class" value="CInjection"/>
        <input type="hidden" name="injection_id" value="{{$injection_id}}"/>

        <table class="form me-no-box-shadow">
            <tr>
                <td>
                    <label>{{tr}}CVaccination-nb_etiquette{{/tr}}</label>
                    <input type="number" value="1" name="nb_etiquette" size="4"/>
                </td>
            </tr>
            <tr>
                <td class="me-text-align-center">
                    <button type="button" class="me-primary" onclick="Vaccination.printEtiquette(this.form)">
                        {{tr}}CVaccination-action-print{{/tr}}
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>
