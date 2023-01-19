{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="webcam_image_container" style="background-color: #ccc">
  <div id="webcam_image_consent" style="text-align: center; font-size: 20px; padding-top: 20px;">
    {{tr}}Please accept webcam access{{/tr}}
  </div>
  <div id="webcam_image_backvideo"
       style="text-align: center; font-size: 20px; padding-top: 20px; height: 50px; margin-bottom: -70px; display: none">
    {{tr}}Your browser is out of date{{/tr}}
  </div>
  <video id="webcam_image_video" style="width: 100%;"></video>
  <canvas id="webcam_image_canvas" style="display: none"></canvas>
  <br/>
  <div id="webcam_image_take_picture" style="text-align: center; display:none;">
    <button type="button" class="fas fa-camera" onclick="WebcamImage.takePicture()"
            style="font-size:20px; padding: 10px; height: 40px; margin:auto;">
      {{tr}}Take the photo{{/tr}}
    </button>
  </div>
  <div id="webcam_image_validate_picture" style="text-align:center; display:none">
    <button type="button" class="fas fa-redo" onclick="WebcamImage.play()"
            style="font-size:20px; padding: 10px; height: 40px; margin:auto">
      {{tr}}Retake the photo{{/tr}}
    </button>
    <button type="button" class="fas fa-check" onclick="WebcamImage.validatePicture()"
            style="font-size:20px; padding: 10px; height: 40px; margin:auto;">
      {{tr}}Validate the photo{{/tr}}
    </button>
  </div>
  <form name="uploadFrm" action="?" enctype="multipart/form-data" class="prepared"
        method="post" onsubmit="return onSubmitFormAjax(this)" style="display:none">
    <input type="hidden" name="m" value="files" />
    <input type="hidden" name="a" value="upload_file" />
    <input type="hidden" name="dosql" value="do_file_aed" />
    <input type="hidden" name="callback" value="reloadCallback" />
    <input type="hidden" name="object_class" value="{{$object->_class}}" />
    <input type="hidden" name="object_id" value="{{$object->_id}}" />
    <input type="hidden" name="named" value="1" />
    <input type="hidden" name="_rename" value="{{$rename}}" />
    <div class="inline-upload-files"></div>
  </form>
</div>
