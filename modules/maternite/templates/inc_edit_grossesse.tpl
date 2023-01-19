{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$grossesse->_id}}
<div id="edit_dossier_perinat">
    {{/if}}

    {{mb_default var=standalone    value=0}}
    {{mb_default var=with_buttons  value=0}}
    {{mb_default var=creation_mode value=1}}
    {{mb_default var=operation_id  value=""}}

    {{mb_script module=patients   script=pat_selector ajax=1}}
    {{mb_script module=admissions script=admissions   ajax=1}}
    {{mb_script module=maternite  script=dossierMater ajax=1}}

    {{assign var=dossier_perinatal value="maternite CGrossesse audipog"|gconf}}

    <script>
        PatSelector.init = function () {
            this.sForm = "editFormGrossesse";
            this.sId = "parturiente_id";
            this.sView = "_patient_view";
            this.sSexe = "_patient_sexe";
            this.parturiente = "1";
            this.pop();
        };

        Main.add(function () {
            {{if $dossier_perinatal && !$creation_mode}}
            DossierMater.reloadGrossesse('{{$grossesse->_id}}');
            {{/if}}
        });
    </script>

    <table class="main layout">
        <tr>
            <td class="" id="edit_grossesse">
                {{if !"maternite CGrossesse audipog"|gconf || $creation_mode}}
                    {{mb_include module=maternite template=inc_create_grossesse}}
                {{/if}}
            </td>
        </tr>
    </table>

    {{if !$grossesse->_id}}
</div>
{{/if}}
