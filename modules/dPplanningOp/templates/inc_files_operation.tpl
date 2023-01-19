{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ext_cabinet_id value=""}}

{{assign var=object value=$operation}}
<div style="float: left; width: 50%;" id="files-{{$object->_guid}}">
  <script type="text/javascript">
    File.register('{{$object->_id}}','{{$object->_class}}', 'files-{{$object->_guid}}', undefined, null, {ext_cabinet_id: '{{$ext_cabinet_id}}'});
  </script>
</div>

{{assign var=object value=$operation->_ref_sejour}}
<div style="float: left; width: 50%;" id="files-{{$object->_guid}}">
  <script type="text/javascript">
    File.register('{{$object->_id}}','{{$object->_class}}', 'files-{{$object->_guid}}', undefined, null, {ext_cabinet_id: '{{$ext_cabinet_id}}'});
  </script>
</div>
