{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{tr}}CExamIgs-scoreIGS-court{{/tr}} :
{{if $igs}}
    {{$igs}}
{{else}}
    &ndash;
{{/if}}
/
{{tr}}CChungScore-total-court{{/tr}} :
{{if $sejour->_ref_last_chung_score && $sejour->_ref_last_chung_score->_id}}
    {{$sejour->_ref_last_chung_score->total}}
{{else}}
    &ndash;
{{/if}}
/
{{tr}}CExamGir-score_gir-court{{/tr}} :
{{if $sejour->_ref_last_exam_gir && $sejour->_ref_last_exam_gir->_id}}
    {{$sejour->_ref_last_exam_gir->score_gir}}
{{else}}
    &ndash;
{{/if}}
