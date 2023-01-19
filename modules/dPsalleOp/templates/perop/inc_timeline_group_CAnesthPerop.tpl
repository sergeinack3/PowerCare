{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="height: 70px;">
  {{if !$readonly}}
  <div style="float: right;">
    <button class="new notext compact not-printable me-tertiary"
            onclick="return SurveillancePerop.editEvenementPerop('CAnesthPerop-0', '{{$interv->_id}}', null, this.up('.surveillance-timeline-container'), null, '{{$type}}')"></button>
    <button class="new-lightning notext compact not-printable me-tertiary" onclick="return SurveillancePerop.quickEvenementPerop('{{$interv->_id}}', this.up('.surveillance-timeline-container'), '{{$type}}')"></button>
  </div>
  {{/if}}
    {{tr}}CAnesthPerop{{/tr}}
</div>
