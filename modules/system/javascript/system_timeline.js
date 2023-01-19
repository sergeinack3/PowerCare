/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SystemTimeline = {
  timeline_id: null,

  selectYear: function (year, first_month, element) {
    SystemTimeline.scrollTo(year, first_month, element);
    $$('.years')[0].style.marginLeft = '-1000px';
    $$('.months')[0].style.marginLeft = '0px';
    SystemTimeline.seeMonthYear(year);
    $$('.timeline_menu .carriage_return')[0].innerHTML = year;
  },

  resetFilterDate: function (element) {
    var year = $$('.timeline_icon')[0].dataset.year;
    var month = $$('.timeline_icon')[0].dataset.month;
    SystemTimeline.scrollTo(year, month, element);
    $$('.years')[0].style.marginLeft = '0px';
    $$('.months')[0].style.marginLeft = '1000px';
  },

  showMenuActions: function(id) {
    var element = $(id);
    if (!element) {
      return;
    }
    var parent_layout = element.up().getLayout();
    var action_layout = element.getLayout();
    var viewport = document.viewport.getDimensions();

    if ((parent_layout.get('left') + action_layout.get('width')) > viewport.width) {
      element.absolutize();
      element.setStyle({right: parent_layout.get('right') + 'px'});
    }

    element.show();
  },

  /**
   * Hide a menu of the timeline
   *
   * @param id
   */
  hideMenuActions: function (id) {
    /*Firefox and IE over select bug*/
    if (event && event.relatedTarget) {
      var menu = $(id);
      if (menu) {
        menu.hide();
      }
    }
  },

  scrollTo: function (year, month) {
    var container = $('timeline-' + SystemTimeline.timeline_id);
    var element = $('timeline-year-' + year+ '-'+month);
    var posContainer = Element.cumulativeOffset(container);
    var posElement = Element.cumulativeOffset(element);

    $('timeline-bottom-space').setStyle({height: container.getHeight() + 'px'});
    container.scrollTop = posElement.top - posContainer.top - 10;

    scrollTo(0, posElement.top - posContainer.top - 10);
  },

  seeMonthYear: function (year) {
    $('list_month_by_year').select('div').each(function(e) {
      if (e.hasClassName('month')) {
        e.style.display = 'none';
      }
    });
    $('list_month_by_year').select('div').each(function(e) {
      if (e.hasClassName('month') && e.id.indexOf('month_element_'+year) >= 0) {
        e.style.display = '';
      }
    });
  },

  onScroll: function (container) {
    var posContainer = Element.cumulativeOffset(container);
    var containerBottom = container.scrollTop + container.getHeight();
    $('list_month_by_year').select('div').each(function(elt) {
      if (elt.hasClassName('year_element') && elt.hasClassName('circled')) {
        elt.removeClassName("highlighted");
      }
    });
    $$('div.timeline_icon').each(
      function (element) {
        var posElement = Element.cumulativeOffset(element);
        var innerTop = posElement.top - posContainer.top;
        if (innerTop >= container.scrollTop && innerTop <= containerBottom) {
          var year = element.get("year");
          var month = element.get("month");
          if (month) {
            $('month_element_' + year + '_' + month).addClassName("highlighted");
          }
          if (year) {
            $('year_element_' + year).addClassName("highlighted");
          }
        }
      }
    )
  }
};
