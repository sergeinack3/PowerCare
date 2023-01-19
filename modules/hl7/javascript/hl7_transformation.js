/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

HL7_Transformation = {
  viewSegments : function (profil, message_class) {
    new Url("hl7", "ajax_hl7_transformation")
      .addParam("profil"       , profil)
      .addParam("message_class", message_class)
      .requestModal("90%", "90%");

    return false;
  },

  viewFields : function (profil, segment_name, version, extension, message, target, fullpath) {
    new Url("hl7", "ajax_hl7_transformation_fields")
      .addParam("profil"      , profil)
      .addParam("segment_name", segment_name)
      .addParam("version"     , version)
      .addParam("extension"   , extension)
      .addParam("message"     , message)
      .addParam("target"      , target)
      .addParam("fullpath"    , fullpath)
      .requestUpdate("hl7-transformation")
  }
}
