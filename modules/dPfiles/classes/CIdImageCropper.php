<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

  use Ox\Core\Autoload\IShortNameAutoloadable;

  /**
   * Crop face picture from global image
   */
class CIdImageCropper implements IShortNameAutoloadable {
  private $image_ratios = array(
    "id_card" => array(
      "start_x" => 0.03,
      "start_y" => 0.2,
      "width" => 0.29,
      "height" => 0.5,
      "new_width" => 200,
      "ratio" => 1.4,
    ),
    "passport" => array(
      "start_x" => 0.02,
      "start_y" => 0.59,
      "width" => 0.28,
      "height" => 0.25,
      "new_width" => 200,
      "ratio" => 1.5,
    ),
    "residence_permit" => array(
      "start_x" => false,
      "ratio" => 0.78,
    )
  );

  public $filepath;
  public $image_resource;
  public $image_face;
  public $file_type;
  public $mime;

  /**
   * CIdImageCropper constructor.
   *
   * @param String $filepath  Image filepath
   * @param String $file_type Image category
   */
  public function __construct($filepath, $file_type)
  {
    $this->filepath = $filepath;
    $this->file_type = $file_type;
    if (!$this->setImageResource()) {
      return false;
    }
    return $this;
  }

  /**
   * Extract face picture from an image
   *
   * @return resource|bool
   */
  public function getFacePicture() {
    if (!isset($this->image_ratios[$this->file_type])
        || gettype($this->image_resource) !== "resource"
        || !$this->image_ratios[$this->file_type]["start_x"]
    ) {
      return false;
    }
    $ratio = $this->image_ratios[$this->file_type];

    $img = $this->image_resource;

    $width  = imagesx($img);
    $height = imagesy($img);
    $start_x = $width * $ratio["start_x"];
    $start_y = $height * $ratio["start_y"];
    $width = $width * $ratio["width"];
    $height = $height * $ratio["height"];

    $new_width = $ratio["new_width"];
    $new_height = $height * ($new_width / $width);

    $returned_image = imagecreatetruecolor($new_width, $new_height);

    // crop the image with the given dimensions
    imagecopyresized(
      $returned_image,
      $img,
      0,
      0,
      $start_x,
      $start_y,
      $new_width,
      $new_height,
      $width,
      $height
    );

    return $this->image_face = $returned_image;
  }

  /**
   * Get the base64 face cropped image
   *
   * @return string
   */
  public function getFaceBase64() {
    if (!$this->getFacePicture()) {
      return false;
    }
    return $this->getBase64($this->image_face);
  }

  /**
   * Get the base64 image
   *
   * @param Resource $image The image to return (default : self:image_resource)
   *
   * @return string
   */
  public function getBase64($image = null) {
    if (!$image) {
      $image = $this->image_resource;
    }

    ob_start();
    $this->genImage($image);
    $image_content =  ob_get_contents();
    ob_end_clean();
    return base64_encode($image_content);
  }

  /**
   * Give the Png, Gif or Jpeg Image resource
   *
   * @return bool|resource
   */
  public function setImageResource() {
    $image_info = getImageSize($this->filepath); // [] if you don't have exif you could use getImageSize()
    if (!isset($image_info["mime"])) {
      return false;
    }
    $this->mime = $image_info["mime"];
    $image_resource = false;
    if ($image_info["mime"] === "image/jpeg") {
      $image_resource = imageCreateFromJpeg($this->filepath);
    }
    elseif ($image_info["mime"] === "image/gif") {
      $image_resource = imageCreateFromGif($this->filepath);
    }
    elseif ($image_info["mime"] === "image/png") {
      $image_resource = imageCreateFromPng($this->filepath);
    }
    return $this->image_resource = $image_resource;
  }

  /**
   * Remove the useless spaces
   *
   * @return bool
   */
  public function cropWhiteSpaces()
  {
    // crop white spaces
    $this->image_resource = imagecropauto($this->image_resource, IMG_CROP_THRESHOLD, 0.9, 16777215);

    // crop black spaces
    $this->image_resource = imagecropauto($this->image_resource, IMG_CROP_THRESHOLD, 0.9, 0);

    // crop gray spaces
    $this->image_resource = imagecropauto($this->image_resource, IMG_CROP_THRESHOLD, 0.7, 6710886);

    return $this->genImage($this->image_resource, $this->filepath);
  }

  /**
   * Resize depending on the image type
   *
   * @return bool
   */
  public function cropByType() {
    $width = imagesx($this->image_resource);
    $height = imagesy($this->image_resource);
    $new_height = $width / $this->image_ratios[$this->file_type]["ratio"];
    $start_y = max(0, $height - $new_height);
    $res = imagecreatetruecolor($width, $new_height);
    imagecopyresized(
      $res,
      $this->image_resource,
      0,
      0,
      0,
      $start_y,
      $width,
      $new_height,
      $width,
      $new_height
    );

    $this->image_resource = $res;

    return $this->genImage($this->image_resource, $this->filepath);
  }

  /**
   * Reduces the size of the picture
   *
   * @return void
   */
  public function scaleDown() {
    $width = imagesx($this->image_resource);
    if ($width > 1024) {
      $this->image_resource = imagescale($this->image_resource, 1024);
    }
  }

  /**
   * Generate Image using current mime
   *
   * @param Resource|null $image    Image to save/gen, default refer to the face image
   * @param String|null   $filename Filepath (in the save case)
   *
   * @return bool
   */
  public function genImage($image = null ,$filename = null) {
    if (!$image) {
      $image = $this->image_face;
    }
    if (!$this->mime) {
      return false;
    }
    if ($this->mime === "image/jpeg") {
      return imagejpeg($image, $filename);
    }
    elseif ($this->mime === "image/gif") {
      return imagegif($image, $filename);
    }
    elseif ($this->mime === "image/png") {
      return imagepng($image, $filename);
    }
    return false;
  }
}