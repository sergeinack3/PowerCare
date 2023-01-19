{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  Control.Tabs.create('control-tab', true, {afterChange: function(container) {loadTab(container);}});
});

function loadTab(container) {
  if (container === false) {
    return;
  }
  var ajax = 'ajax_thumbnails_version';
  switch (container.id) {
    case 'thumbnail-profile':
      ajax = 'ajax_thumbnails_profiles';
      break;
    case 'thumbnail-rotation':
      ajax = 'ajax_thumbnails_rotation';
      break;
    case 'thumbnail-quality':
      ajax = 'ajax_thumbnails_quality';
      break;
    default:
      break;
  }
  var url = new Url('dPdeveloppement', ajax);
  url.requestUpdate('ajax-result-'+container.id);
}
</script>

<ul id="control-tab" class="control_tabs">
  <li><a href="#thumbnail-version">Versions</a></li>
  <li><a href="#thumbnail-profile">Profils</a></li>
  <li><a href="#thumbnail-rotation">Rotations</a></li>
  <li><a href="#thumbnail-quality">Qualité JPEG</a></li>
</ul>

<div id="thumbnail-profile" style="display: none">
  {{mb_include module=dPdeveloppement template=vw_profile_thumbnail_tester}}
</div>
<div id="thumbnail-version" style="display: none">
  {{mb_include module=dPdeveloppement template=vw_version_thumbnail_tester}}
</div>
<div id="thumbnail-rotation" style="display: none">
  {{mb_include module=dPdeveloppement template=vw_rotation_thumbnail_tester}}
</div>
<div id="thumbnail-quality" style="display: none">
  {{mb_include module=dPdeveloppement template=vw_quality_thumbnail_tester}}
</div>

