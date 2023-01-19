{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<fieldset>
  <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}{{$reunion_object->_class}}{{/tr}}</legend>
  <div id="documents">
    <script type="text/javascript">
      Document.register('{{$reunion_object->_id}}','{{$reunion_object->_class}}','{{$consult->_praticien_id}}','documents');
    </script>
  </div>
</fieldset>

<fieldset>
  <legend>{{tr}}CFile{{/tr}} - {{tr}}{{$reunion_object->_class}}{{/tr}}</legend>
  <div id="files">
    <script type="text/javascript">
      File.register('{{$reunion_object->_id}}','{{$reunion_object->_class}}', 'files');
    </script>
  </div>
</fieldset>
