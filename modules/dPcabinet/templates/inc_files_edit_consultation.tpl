{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}{{$object_consult->_class}}{{/tr}}</legend>
  <div id="documents">
    <script type="text/javascript">
      Document.register('{{$object_consult->_id}}','{{$object_consult->_class}}','{{$consult->_praticien_id}}','documents');
    </script>
  </div>
</fieldset>

<fieldset>
  <legend>{{tr}}CFile{{/tr}} - {{tr}}{{$consult->_class}}{{/tr}}</legend>
  <div id="files">
    <script type="text/javascript">
      File.register('{{$consult->_id}}','{{$consult->_class}}', 'files');
    </script>
  </div>
</fieldset>

{{if 'forms'|module_active}}
  <fieldset>
    <legend>{{tr}}CExClass|pl{{/tr}}</legend>

    {{unique_id var=unique_id_rdv_forms}}

    <script>
      Main.add(function() {
        ExObject.loadExObjects("{{$consult->_class}}", "{{$consult->_id}}", "{{$unique_id_rdv_forms}}", 0.5);
      });
    </script>

    <div id="{{$unique_id_rdv_forms}}"></div>
  </fieldset>
{{/if}}

<fieldset>
  <legend>{{tr}}CDevisCodage{{/tr}}</legend>
  {{mb_script module=ccam script=DevisCodage ajax=1}}
  <script>
    Main.add(function() {
      DevisCodage.list('{{$consult->_class}}', '{{$consult->_id}}');
    });
  </script>
  <div id="view-devis"></div>
</fieldset>
