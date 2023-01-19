{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<strong>{{mb_label object=$selOp field=type_anesth}}</strong> :
{{if $selOp->type_anesth}}
  {{mb_value object=$selOp field=type_anesth}}
{{else}}
  <div class="me-inline-block empty">{{tr}}common-Not specified{{/tr}}</div>
{{/if}}

&mdash;

<strong>{{tr}}CConsultAnesth-cormack{{/tr}}</strong> :
{{if $consult_anesth->cormack}}
  {{mb_value object=$consult_anesth field="cormack"}}
  {{if $consult_anesth->com_cormack}}
    (<span title="{{$consult_anesth->com_cormack}}">{{$consult_anesth->com_cormack|truncate:50:"...":false}}</span>)
  {{/if}}
{{else}}
  <div class="me-inline-block empty">{{tr}}common-Not specified{{/tr}}</div>
{{/if}}
