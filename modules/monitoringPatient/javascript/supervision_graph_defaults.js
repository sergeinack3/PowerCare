/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

window.SupervisionGraph = window.SupervisionGraph || {};

Object.extend(SupervisionGraph, {
  /**
   * Default series
   */
  defaultSeries: {
    points:    {
      show:   true,
      radius: 3
    },
    bandwidth: {
      active:        true,
      drawBandwidth: function (ctx, bandwidth, x, y1, y2, color, isOverlay) {
        var width = 2;
        var offset = width / 2;

        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = width;
        ctx.moveTo(x, y1);
        ctx.lineTo(x, y2);
        ctx.stroke();
        ctx.beginPath();
        if (isOverlay) {
          ctx.strokeStyle = "rgba(255,255,255," + bandwidth.highlight.opacity + ")";
        } else {
          ctx.strokeStyle = color;
        }
        ctx.lineWidth = 10;
        ctx.moveTo(x, y1 - offset);
        ctx.lineTo(x, y1 + offset);
        ctx.moveTo(x, y2 - offset);
        ctx.lineTo(x, y2 + offset);
        ctx.stroke();
      }
    }
  },

  formatTrack: function (item) {
    var x = item.datapoint[0],
      y = item.datapoint[1];

    if (item.series.bandwidth && item.series.bandwidth.show) {
      y += " / " + item.series.data[item.dataIndex][2];
    }

    var date = new Date();
    date.setTime(x);

    var label = y + " " + (item.series.unit || "");
    var point = item.series.data[item.dataIndex];
    if (point.label) {
      label = point.label;
    }

    var d = printf(
      "%02d/%02d %02d:%02d:%02d",
      date.getDate(),
      date.getMonth() + 1,
      date.getHours(),
      date.getMinutes(),
      date.getSeconds()
    );

    return "<big style='font-weight:bold'>#{value}</big><hr />#{label}<br />#{date}<br />#{user}".interpolate({
      value: label,
      label: item.series.label,
      date:  d,
      user:  point.user
    });
  }
});
