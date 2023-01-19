/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SejourTimeline = {
  sejour_id: 0,
  /**
   * Scroll to date
   *
   * @param date
   */
  scrollToDate: function(year, month) {
    var container = $('sejour-timeline');
    var element = $('timeline-year-' + year+ '-'+month);
    var posContainer = Element.cumulativeOffset(container);
    var posElement = Element.cumulativeOffset(element);
    $('timeline-bottom-space').setStyle({height : container.getHeight() + 'px'});
    container.scrollTop = posElement.top - posContainer.top - 10;
  },
  /**
   * Select the year
   *
   * @param year
   * @param first_month
   * @param element
   */
  selectYear: function(year, first_month, element) {
    SejourTimeline.scrollToDate(year, first_month);
    $$('.years')[0].style.marginLeft = '-1000px';
    $$('.months')[0].style.marginLeft = '0px';
    SejourTimeline.seeMonthYear(year);
    $$('.timeline_menu .arrow-left')[0].innerHTML = year;
  },
  /**
   * See the months of the year
   *
   * @param year
   */
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
  /**
   * Scroll into the timeline
   */
  onScroll: function() {
    var container = $('sejour-timeline');
    var posContainer = Element.cumulativeOffset(container);
    var containerBottom = container.scrollTop + container.getHeight();
    $('list_month_by_year').select('div').each(function(elt) {
      if (elt.hasClassName('year_element') && elt.hasClassName('circled')) {
        elt.removeClassName("highlighted");
      }
    });
    $$('div.timeline_icon').each(
      function(element) {
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
  },
  /**
   * Toggle events
   *
   * @param category
   * @param event
   */
  toggleEvents: function(category, event, sejour_id) {
    var url = new Url('soins', 'sejour_timeline');
    url.addParam('sejour_id', sejour_id);
    url.addParam('refresh', 1);
    url.addParam('category', category);
    if (event) {
      url.addParam('event', event);
    }
    url.requestUpdate('container_timeline_sejour');
  },
  /**
   * Show the sub menu
   *
   * @param category
   */
  showSubMenu: function(category) {
    var visibleSubMenu = $$('div#timeline-menu-events > div[data-visible="1"]');
    if (visibleSubMenu.length) {
      visibleSubMenu.each(function(subMenu) {
        subMenu.hide();
        subMenu.writeAttribute('data-visible', 0);
      });
    }
    var arrows = $$('div.timeline_menu_arrow');
    arrows.each(function(arrow) {
      arrow.hide();
    });

    var subMenu = $('timeline-menu-events-' + category);
    subMenu.show();
    subMenu.writeAttribute('data-visible', 1);
    $('arrow-' + category).show();
  },
  /**
   * Reset the filter date
   */
  resetFilterDate: function () {
    var year = $$('.timeline_icon')[0].dataset.year;
    var month = $$('.timeline_icon')[0].dataset.month;
    SejourTimeline.scrollToDate(year, month);
    $$('.years')[0].style.marginLeft = '0px';
    $$('.months')[0].style.marginLeft = '1000px';
  }
};