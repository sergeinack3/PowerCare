{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Intervention -->
<th>Intervention</th>
<th>Cot�</th>
<th>Anesth�sie</th>
{{if !$_compact}}
    <th>Remarques</th>
    {{if $_materiel}}
        <th>Mat�riel</th>
    {{/if}}
{{else}}
    <th>Rques{{if $_materiel}} / Mat.{{/if}}</th>
{{/if}}
{{if $_extra}}
    <th>Extra</th>
{{/if}}
{{if $_duree}}
    <th>Dur�e</th>
{{/if}}
{{if $_by_prat}}
    <th>Salle</th>
{{/if}}
{{if $_examens_perop}}
    <th>{{tr}}COperation-exam_per_op{{/tr}}</th>
{{/if}}
