{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=presence value=$rpu->_presence|date_format:$conf.time}}
{{assign var=attente value=$rpu->_attente|date_format:$conf.time}}
{{assign var="img_title" value="(`$attente` / `$presence`)"}}

{{assign var=attente_radio value=$rpu->_ref_last_attentes.radio}}
{{if !$attente_radio->retour && $attente_radio->depart}}
  {{assign var=depart value=$attente_radio->depart|date_format:$conf.time}}
  {{assign var="img_title" value="`$img_title` \nDépart radio : `$depart`"}}
{{/if}}
{{assign var=attente_bio value=$rpu->_ref_last_attentes.bio}}
{{if !$attente_bio->retour && $attente_bio->depart}}
  {{assign var=depart value=$attente_bio->depart|date_format:$conf.time}}
  {{assign var="img_title" value="`$img_title` \nDépart bio : `$depart`"}}
{{/if}}
{{assign var=attente_specialiste value=$rpu->_ref_last_attentes.specialiste}}
{{if !$attente_specialiste->retour && $attente_specialiste->depart}}
  {{assign var=depart value=$attente_specialiste->depart|date_format:$conf.time}}
  {{assign var="img_title" value="`$img_title` \nAttente spécialiste : `$depart`"}}
{{/if}}
{{mb_default var=width value="24"}}

{{assign var=n_part value="fourth"}}
{{if $rpu->_presence < $conf.dPurgences.attente_first_part}}
  {{assign var=n_part value="first"}}
{{elseif $rpu->_presence >= $conf.dPurgences.attente_first_part &&
$rpu->_presence < $conf.dPurgences.attente_second_part}}
  {{assign var=n_part value="second"}}
{{elseif $rpu->_presence >= $conf.dPurgences.attente_second_part &&
$rpu->_presence < $conf.dPurgences.attente_third_part}}
  {{assign var=n_part value="third"}}
{{/if}}

<span style="float: right;" class="me-attente-part me-attente-part-{{$n_part}}" title="{{$img_title}}">
  <img src="images/icons/attente_{{$n_part}}_part.png" width="{{$width}}"/>
</span>