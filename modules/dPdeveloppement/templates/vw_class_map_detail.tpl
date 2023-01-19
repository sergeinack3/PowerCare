{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #class_exist {
    margin: 10px;
  }

  #map-detail pre {
    max-height: 100%;
  }
</style>

<div id="class_exist">
  <div class="small-{{$class_exists}}">{{$msg}}</div>
</div>

<h3>Class Map:</h3>
<div id="map-detail">
  {{$map|highlight:json|smarty:nodefaults}}
</div>

<h3>Class Ref:</h3>
<div id="refp-detail">
  {{$ref|highlight:json|smarty:nodefaults}}
</div>