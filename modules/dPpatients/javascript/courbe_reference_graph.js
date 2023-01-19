/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CourbeReference = {
  /**
   * Show the reference curve
   *
   * @param patientId
   * @param graph_name
   * @param constantName
   * @param select_graph
   * @returns {Url}
   */
  showModalGraph: function (patientId, graph_name, constantName, select_graph) {
    return new Url('patients', 'ajax_courbe_reference_graph')
      .addParam('patient_id', patientId)
      .addParam('graph_name', graph_name)
      .addParam('constantName', constantName)
      .addParam('select_graph', select_graph)
      .requestModal(1000, 750);
  },

  /**
   * Set draw arrows
   *
   * @param datas
   * @returns {*}
   */
  drawBTC: function (datas) {
    datas[0].bandwidth.drawBandwidth = CourbeReference.bandwidthBTC;
    return datas;
  }
};

/**
 * Draw arrows
 *
 * @param ctx
 * @param bandwidth
 * @param x
 * @param y1
 * @param y2
 * @param color
 */
CourbeReference.bandwidthBTC = function (ctx, bandwidth, x, y1, y2, color) {
  var offset = 3;
  var middle = (y1 + 2 * y2) / 3;

  ctx.beginPath();
  ctx.strokeStyle = color;
  ctx.lineWidth = 1.5;
  ctx.lineCap = "square";

  // Main line
  ctx.moveTo(x, y1);
  ctx.lineTo(x, y2);

  // Upper arrow
  ctx.moveTo(x - offset, y1 - offset);
  ctx.lineTo(x, y1);
  ctx.lineTo(x + offset, y1 - offset);

  // Lower arrow
  ctx.moveTo(x - offset, y2 + offset);
  ctx.lineTo(x, y2);
  ctx.lineTo(x + offset, y2 + offset);

  // Middle (y1 = systole, y2 = diastole)
  ctx.moveTo(x - offset, middle);
  ctx.lineTo(x + offset, middle);

  ctx.stroke();
};
