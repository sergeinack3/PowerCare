{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  aProtocoles[{{$protocole->_id}}] = {
    {{mb_include module=planningOp template=inc_js_protocole nodebug=true}}
  };
  
  ProtocoleSelector.set(aProtocoles[{{$protocole->_id}}]);
  Control.Modal.close();
</script>
