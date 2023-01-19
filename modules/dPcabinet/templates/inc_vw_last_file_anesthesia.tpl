{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$print}}
  <script>
    Main.add(function () {
      var dimensions = document.viewport.getDimensions();
      var last_file = $('last_file_anesthesia');
      last_file.setStyle(
        {
          height: dimensions.height - last_file.cumulativeOffset().top - 10 + 'px',
          width:  dimensions.width - last_file.cumulativeOffset().left - 25 + 'px',
        }
      );

      if ($('cpa_{{$operation->_guid}}')) {
        $('cpa_{{$operation->_guid}}').hide();
      }

      if ($('vpa_{{$operation->_guid}}')) {
        $('vpa_{{$operation->_guid}}').hide();
      }
    });
  </script>
{{/if}}

<div id="last_file_anesthesia" style="padding-top: 5px;">
  <iframe class="document-view" style="width : 100%; height: {{if !$print}}100%{{else}}700px{{/if}};"
          src="?m=files&raw=thumbnail&document_guid={{$last_file->_class}}-{{$last_file->_id}}&thumb=0&disposition=0"></iframe>
</div>
