<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * User agent graph
 */
class CUserAgentGraph implements IShortNameAutoloadable {
  static function getBrowserNameSeries($browsers) {
    $series = array(
      "title"   => "CUserAgent-browser_name",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($browsers as $_browser) {
      $series["data"][] = array(
        "label" => $_browser["browser_name"],
        "data"  => $_browser["total"]
      );
    }

    return $series;
  }

  static function getBrowserVersionSeries($versions) {
    $browsers = array();
    foreach ($versions as $_version) {
      if (!isset($browsers[$_version["browser_name"]])) {
        $browsers[$_version["browser_name"]] = array();
      }

      $browsers[$_version["browser_name"]][$_version["browser_version"]] = $_version["total"];
    }

    $series = array(
      "title"   => "CUserAgent-browser_version",
      "data"    => array(),
      "options" => array()
    );

    $ticks = array();
    $i     = 0;
    foreach ($browsers as $_browser => $_version) {
      $i += 1.5;

      $ticks[] = array(
        "0" => $i,
        "1" => "<strong>$_browser</strong>"
      );
    }
    $max = $i + 1;

    $series["options"] = array(
      "xaxis"  => array(
        "position" => "bottom",
        "min"      => 0,
        "max"      => $max,
        "ticks"    => $ticks
      ),
      "yaxes"  => array(
        "0" => array(
          "position"     => "left",
          "tickDecimals" => false
        ),
        "1" => array(
          "position" => "right",
        )
      ),
      "legend" => array(
        "show" => false
      ),
      "series" => array(
        "stack" => true
      ),
      "grid"   => array(
        "hoverable" => true
      )
    );

    $i = 1;
    foreach ($browsers as $_versions) {
      foreach ($_versions as $_version => $_total) {
        $datum   = array();
        $datum[] = array(
          "0" => $i,
          "1" => $_total
        );

        $series["data"][] = array(
          "data"  => $datum,
          "yaxis" => 1,
          "label" => "$_version",
          "bars"  => array(
            "show"    => true,
          )
        );
      }

      $i += 1.5;
    }

    return $series;
  }

  static function getPlatformNameSeries($platforms) {
    $series = array(
      "title"   => "CUserAgent-platform_name",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($platforms as $_platform) {
      $series["data"][] = array(
        "label" => $_platform["platform_name"],
        "data"  => $_platform["total"]
      );
    }

    return $series;
  }

  static function getDeviceTypeSeries($devices) {
    $series = array(
      "title"   => "CUserAgent-device_type",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($devices as $_device) {
      $series["data"][] = array(
        "label" => $_device["device_type"],
        "data"  => $_device["total"]
      );
    }

    return $series;
  }

  static function getScreenSizeSeries($screens) {
    $series = array(
      "title"   => "CUserAuthentication-screen_width",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($screens as $_screen) {
      $series["data"][] = array(
        "label" => $_screen["screen_width"],
        "data"  => $_screen["total"]
      );
    }

    return $series;
  }

  static function getPointingMethodSeries($methods) {
    $series = array(
      "title"   => "CUserAgent-pointing_method",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($methods as $_method) {
      $series["data"][] = array(
        "label" => $_method["pointing_method"],
        "data"  => $_method["total"]
      );
    }

    return $series;
  }

  static function getNbConnectionsSeries($connections) {
    $series = array(
      "title"   => "CUserAuthentication",
      "data"    => array(),
      "options" => array(
        "series" => array(
          "pie" => array(
            "show"  => true,
            "label" => array(
              "show"      => true,
              "threshold" => 0.02
            )
          )
        ),
        "legend" => array(
          "show" => false
        ),
        "grid"   => array(
          "hoverable" => true
        )
      )
    );

    foreach ($connections as $_connection) {
      $series["data"][] = array(
        "label" => $_connection["datetime_login"],
        "data"  => $_connection["total"]
      );
    }

    return $series;
  }
}
