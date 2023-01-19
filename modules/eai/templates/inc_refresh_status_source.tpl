{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=accessibility value=0}}

{{foreach from=$_actor->_ref_exchanges_sources item=_exchange_source}}
  {{if !$_actor|instanceof:'Ox\Interop\Webservices\CSenderSOAP' && !$_actor|instanceof:'Ox\Interop\Hl7\CSenderMLLP' && !$_actor|instanceof:'Ox\Interop\Dicom\CDicomSender'}}
    {{mb_include module=system template=inc_img_status_source exchange_source=$_exchange_source
    actor_actif=$_actor->actif actor_parent_class=$_actor->_parent_class accessibility=$accessibility}}
  {{elseif !$_actor->actif && $_actor|instanceof:'Ox\Interop\Eai\CInteropReceiver'}}
    <i class="fa fa-circle id="{{$_actor->_guid}}"></i>
  {{/if}}
{{/foreach}}
