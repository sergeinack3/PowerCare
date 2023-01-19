/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var DateFormat = Class.create();
Object.extend(DateFormat, {
    CODE_LOCALES: {'fr': 'fr_FR', 'de': 'de_DE', 'fr-be': 'fr_BE', 'fr-nl': 'nl_BE'},
    MONTH_NAMES: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    DAY_NAMES: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    LZ: function (x) {
        return (x < 0 || x > 9 ? "" : "0") + x;
    },
    compareDates: function (date1, dateformat1, date2, dateformat2) {
        var d1 = DateFormat.parseFormat(date1, dateformat1);
        var d2 = DateFormat.parseFormat(date2, dateformat2);
        if (d1 == 0 || d2 == 0) {
            return -1;
        } else if (d1 > d2) {
            return 1;
        }
        return 0;
    },
    format: function (date, format) {
        if (!date) {
            return;
        }

        format += "";
        var result = "",
            i = 0,
            c = "",
            token = "",
            y = date.getFullYear() + "",
            M = date.getMonth() + 1,
            d = date.getDate(),
            E = date.getDay(),
            H = date.getHours(),
            m = date.getMinutes(),
            s = date.getSeconds(),
            h = (H == 0 ? 12 : (H > 12 ? H - 12 : H));

        // Convert real date parts into formatted versions
        var value = {
            y: y + '', // Année
            yy: y.substring(2, 4), // Année sur 2 chiffres
            yyyy: y, // Année sur 4 chiffres
            M: M, // Mois sur un chiffre quand < à 10
            MM: DateFormat.LZ(M), // Mois sur deux chiffres
            MMM: DateFormat.MONTH_NAMES[M - 1], // Nom du mois
            NNN: DateFormat.MONTH_NAMES[M + 11], // Nom du mois en abbrégé
            d: d, // Numéro du jour dans le mois sur un chiffre quand < à 10
            dd: DateFormat.LZ(d), // Numéro du jour dans le mois
            E: DateFormat.DAY_NAMES[E + 7], // Nom du jour en abbrégé
            EE: DateFormat.DAY_NAMES[E], // Nom du jour
            H: H, // Heure sur 24h sur un chiffre quand < à 10
            HH: DateFormat.LZ(H), // Heure sur 24h
            h: h, // Heure sur 12h sur un chiffre quand < à 10
            hh: DateFormat.LZ(h), // Heure sur 12h
            K: H % 12, // Heure sur 12h sur 1 chiffre quand < à 10
            KK: DateFormat.LZ(H % 12), // Heure sur 12h sur 2 chiffres
            k: H + 1, // Heure sur 12h sur 1 chiffre plus 1
            kk: DateFormat.LZ(H + 1), // Heure sur 12h sur 2 chiffres pours 1
            a: H > 11 ? 'PM' : 'AM', // Méridien
            m: m, // Minutes sur 1 chiffre quand < à 10
            mm: DateFormat.LZ(m), // Minutes
            s: s, // Secondes sur 1 chiffre quand < à 10
            ss: DateFormat.LZ(s) // Secondes
        };

        while (i < format.length) {
            c = format.charAt(i);
            token = "";
            while ((format.charAt(i) == c) && (i < format.length)) {
                token += format.charAt(i++);
            }
            if (value[token] != null) {
                result += value[token];
            } else {
                result += token;
            }
        }
        return result.htmlDecode();
    },
    _isInteger: function (val) {
        return parseInt(val) == val;
    },
    _getInt: function (str, i, minlength, maxlength) {
        // A possible replacement of this function, to be tested
        var sub = str.substring(i, i + maxlength);
        if (!sub) {
            return null;
        }
        return sub + '';
    },
    parseFormat: function (val, format) {
        val = val + "";
        format = format + "";
        var i_val = 0;
        var i_format = 0;
        var c = "";
        var token = "";
        var token2 = "";
        var x, y;
        var now = new Date();
        var year = now.getYear();
        var month = now.getMonth() + 1;
        var date = 1;
        var hh = now.getHours();
        var mm = now.getMinutes();
        var ss = now.getSeconds();
        var ampm = "";

        while (i_format < format.length) {
            // Get next token from format string
            c = format.charAt(i_format);
            token = "";

            while ((format.charAt(i_format) == c) && (i_format < format.length)) {
                token += format.charAt(i_format++);
            }

            // Extract contents of value based on format token
            if (token == "yyyy" || token == "yy" || token == "y") {
                if (token == "yyyy") {
                    x = 4;
                }
                y = 4;
                if (token == "yy") {
                    x = 2;
                }
                y = 2;
                if (token == "y") {
                    x = 2;
                }
                y = 4;
                year = DateFormat._getInt(val, i_val, x, y);
                if (year == null) {
                    return 0;
                }
                i_val += year.length;
                if (year.length == 2) {
                    if (year > 70) {
                        year = 1900 + (year - 0);
                    } else {
                        year = 2000 + (year - 0);
                    }
                }
            } else if (token == "MMM" || token == "NNN") {
                month = 0;
                for (var i = 0; i < DateFormat.MONTH_NAMES.length; i++) {
                    var month_name = DateFormat.MONTH_NAMES[i];
                    if (val.substring(i_val, i_val + month_name.length).toLowerCase() == month_name.toLowerCase()) {
                        if (token == "MMM" || (token == "NNN" && i > 11)) {
                            month = i + 1;
                            if (month > 12) {
                                month -= 12;
                            }
                            i_val += month_name.length;
                            break;
                        }
                    }
                }
                if ((month < 1) || (month > 12)) {
                    return 0;
                }
            } else if (token == "EE" || token == "E") {
                for (var i = 0; i < DateFormat.DAY_NAMES.length; i++) {
                    var day_name = DateFormat.DAY_NAMES[i];
                    if (val.substring(i_val, i_val + day_name.length).toLowerCase() == day_name.toLowerCase()) {
                        i_val += day_name.length;
                        break;
                    }
                }
            } else if (token == "MM" || token == "M") {
                month = DateFormat._getInt(val, i_val, token.length, 2);
                if (month == null || (month < 1) || (month > 12)) {
                    return 0;
                }
                i_val += month.length;
            } else if (token == "dd" || token == "d") {
                date = DateFormat._getInt(val, i_val, token.length, 2);
                if (date == null || (date < 1) || (date > 31)) {
                    return 0;
                }
                i_val += date.length;
            } else if (token == "hh" || token == "h") {
                hh = DateFormat._getInt(val, i_val, token.length, 2);
                if (hh == null || (hh < 1) || (hh > 12)) {
                    return 0;
                }
                i_val += hh.length;
            } else if (token == "HH" || token == "H") {
                hh = DateFormat._getInt(val, i_val, token.length, 2);
                if (hh == null || (hh < 0) || (hh > 23)) {
                    return 0;
                }
                i_val += hh.length;
            } else if (token == "KK" || token == "K") {
                hh = DateFormat._getInt(val, i_val, token.length, 2);
                if (hh == null || (hh < 0) || (hh > 11)) {
                    return 0;
                }
                i_val += hh.length;
            } else if (token == "kk" || token == "k") {
                hh = DateFormat._getInt(val, i_val, token.length, 2);
                if (hh == null || (hh < 1) || (hh > 24)) {
                    return 0;
                }
                i_val += hh.length;
                hh--;
            } else if (token == "mm" || token == "m") {
                mm = DateFormat._getInt(val, i_val, token.length, 2);
                if (mm == null || (mm < 0) || (mm > 59)) {
                    return 0;
                }
                i_val += mm.length;
            } else if (token == "ss" || token == "s") {
                ss = DateFormat._getInt(val, i_val, token.length, 2);
                if (ss == null || (ss < 0) || (ss > 59)) {
                    return 0;
                }
                i_val += ss.length;
            } else if (token == "a") {
                if (val.substring(i_val, i_val + 2).toLowerCase() == "am") {
                    ampm = "AM";
                } else if (val.substring(i_val, i_val + 2).toLowerCase() == "pm") {
                    ampm = "PM";
                } else {
                    return 0;
                }
                i_val += 2;
            } else {
                if (val.substring(i_val, i_val + token.length) != token) {
                    return 0;
                } else {
                    i_val += token.length;
                }
            }
        }
        // If there are any trailing characters left in the value, it doesn't match
        if (i_val != val.length) {
            return 0;
        }
        // Is date valid for month?
        if (month == 2) {
            // Check for leap year
            if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) { // leap year
                if (date > 29) {
                    return 0;
                }
            } else if (date > 28) {
                return 0;
            }
        }
        if ((month == 4) || (month == 6) || (month == 9) || (month == 11)) {
            if (date > 30) {
                return 0;
            }
        }
        // Correct hours value
        if (hh < 12 && ampm == "PM") {
            hh = hh - 0 + 12;
        } else if (hh > 11 && ampm == "AM") {
            hh -= 12;
        }
        return new Date(year, month - 1, date, hh, mm, ss);
    },
    parse: function (val, format) {
        if (format) {
            return DateFormat.parseFormat(val, format);
        } else {
            var preferEuro = (arguments.length == 2) ? arguments[1] : false;
            var generalFormats = ['y-M-d', 'MMM d, y', 'MMM d,y', 'y-MMM-d', 'd-MMM-y', 'MMM d'];
            var monthFirst = ['M/d/y', 'M-d-y', 'M.d.y', 'MMM-d', 'M/d', 'M-d'];
            var dateFirst = ['d/M/y', 'd-M-y', 'd.M.y', 'd-MMM', 'd/M', 'd-M'];
            var checkList = [generalFormats, preferEuro ? dateFirst : monthFirst, preferEuro ? monthFirst : dateFirst];
            var d = null;
            for (var i = 0; i < checkList.length; i++) {
                var l = checkList[i];
                for (var j = 0; j < l.length; j++) {
                    d = DateFormat.parseFormat(val, l[j]);
                    if (d != 0) {
                        return new Date(d);
                    }
                }
            }
            return null;
        }
    }
});

DateFormat.prototype = {
    initialize: function (format) {
        this.format = format;
    },
    parse: function (value) {
        return DateFormat.parseFormat(value, this.format);
    },
    format: function (value) {
        return DateFormat.format(value, this.format);
    }
};

var ProgressiveCalendar = Class.create({
    initialize: function (element, options) {
        this.element = $(element);

        this.options = Object.extend({
            icon: "images/icons/calendar.gif",
            container: $(document.body)
        }, options || {});

        if (element.id) {
            var name_id = element.id;
        } else {
            var name_id = element.name;
        }
        if (!(this.elementView = $(this.element.form.elements[name_id + '_da']))) {
            this.elementView = new Element('input', {
                type: 'text',
                readonly: 'readonly',
                className: this.element.className || 'date'
            });
            this.element.insert({before: this.elementView});
        }

        this.date = this.getDate();
        $V(this.elementView, (parseInt(this.date.day) ? this.pad(this.date.day) + '/' : '') +
            (parseInt(this.date.month) ? this.pad(this.date.month) + '/' : '') +
            (parseInt(this.date.year) ? this.pad(this.date.year, 4) : ''));

        if (this.options.icon) {
            var cont = new Element('div', {
                className: "datePickerWrapper",
                style: 'position:relative;border:none;padding:0;margin:0;display:inline-block;'
            });
            this.elementView.wrap(cont);
            var icon = new DOM.i({class: "me-icon agenda me-primary inputExtension"});

            // No icon padding specified, default to 3px and calculate dynamically on image load
            var padding = 3;
            icon.observe('load', function () {
                var elementDim = this.elementView.getDimensions();
                var iconDim = icon.getDimensions();
                padding = parseInt(elementDim.height - iconDim.height) / 2;
            }.bindAsEventListener(this)).setStyle({position: 'absolute', right: padding + 'px', top: padding + 'px'});
            cont.insert(icon);

            icon.observe('click', this.createPicker.bindAsEventListener(this));
        } else {
            this.elementView.observe('click', this.createPicker.bindAsEventListener(this));
        }
    },
    getDate: function () {
        var parts = this.element.value.split('-');
        return {
            year: parts[0] || 0,
            month: parts[1] || 0,
            day: parts[2] || 0
        };
    },
    setDate: function (date) {
        $V(this.element, this.pad(date.year, 4) + '-' + this.pad(date.month) + '-' + this.pad(date.day));
        $V(this.elementView, (parseInt(date.day) ? this.pad(date.day) + '/' : '') +
            (parseInt(date.month) ? this.pad(date.month) + '/' : '') +
            (parseInt(date.year) ? this.pad(date.year, 4) : ''));
    },
    pad: function (str, length) {
        return String(str).pad('0', length || 2);
    },
    fillTable: function (table, cols, rows, min, max, type, date) {
        if (min === null) {
            min = max - rows * cols + 1;
        }

        var i, j, body = table.select('tbody').first(), origMin = min;

        for (i = 0; i < rows; i++) {
            var row = new Element('tr').addClassName('calendarRow');
            for (j = 0; j < cols; j++) {
                if (i == 0 && j == 0 && type == 'year') {
                    var before = new Element('td', {
                        rowSpan: rows,
                        style: 'width:0.1%;padding:1px;'
                    }).addClassName('day').update('<');
                    before.observe('click', function (e) {
                        e.stop();
                        body.update();
                        this.fillTable(table, cols, rows, origMin - cols * rows, max - cols * rows, type, date);
                    }.bindAsEventListener(this));
                    row.insert(before);
                }

                if (min <= max) {
                    var cell = new Element('td', {style: 'padding:1px;width:16.7%;'}).addClassName('day').update(min);
                    if (min++ == date[type]) {
                        cell.addClassName('current');
                    }
                    cell.observe('click', function (e) {
                        e.stop();
                        var element = e.element();
                        element.up(1).select('.current').invoke('removeClassName', 'current');
                        element.addClassName('current');
                        date[type] = element.innerHTML;
                        this.setDate(date);
                    }.bindAsEventListener(this))
                        .observe('dblclick', function (e) {
                            e.stop();
                            this.hidePicker();
                        }.bindAsEventListener(this));
                    row.insert(cell);
                }

                if (i == 0 && j == cols - 1 && type == 'year') {
                    var after = new Element('td', {
                        rowSpan: rows,
                        style: 'width:0.1%;padding:1px;',
                        className: 'day'
                    }).update('>');
                    after.observe('click', function (e) {
                        e.stop();
                        body.update();
                        this.fillTable(table, cols, rows, origMin + cols * rows, max + cols * rows, type, date);
                    }.bindAsEventListener(this));
                    row.insert(after);
                }
            }
            body.insert(row);
        }
    },
    createTable: function (container, title, cols, rows, min, max, type, date) {
        container.insert('<div />');

        var newContainer = container.childElements().last();
        newContainer.insert('<table style="width:100%;"><tbody /></table>');
        var table = newContainer.childElements().last();

        this.fillTable(table, cols, rows, min, max, type, date);

        if (title) {
            newContainer.insert('<a href="#1" style="text-align:center;display:block;font-weight:bold;">' + title + ' <i class="me-icon arrow-down"></i></a>');
        }

        if (title) {
            table.nextSiblings().first().observe('click', function (e) {
                e.stop();
                var element = e.findElement('a');
                if (!element.previousSiblings().first().select('.current').length) {
                    return;
                }

                var next = element.nextSiblings().first();
                if (next.visible()) {
                    switch (type) {
                        case 'year':
                            date.month = 0;
                        case 'month':
                            date.day = 0;
                    }
                    next.hide().select('table').invoke('hide');
                    element.select('i').first().removeClassName('cancel');
                    element.select('i').first().addClassName('arrow-down');
                } else {
                    var current = next.select('.current'),
                        v = current.length ? current.first().innerHTML : 0;
                    switch (type) {
                        case 'year':
                            date.month = v;
                            break;
                        case 'month':
                            date.day = v;
                            break;
                    }
                    next.show().select('table').first().show();
                    element.select('i').first().removeClassName('arrow-down');
                    element.select('i').first().addClassName('cancel');
                }

                this.setDate(date);
            }.bindAsEventListener(this));
        }

        return newContainer;
    },
    createPicker: function (e) {
        if (!this.picker) {
            this.picker = new Element(
                'div',
                {style: 'position:absolute;width:236px;', className: 'datepickerControl'}
            ).observe('click', Event.stop);
        }
        if (ProgressiveCalendar.activePicker) {
            ProgressiveCalendar.activePicker.hidePicker();
        }
        var container,
            now = new Date(),
            date = this.getDate();
        this.picker.update('<a href="#1" style="text-align:center;display:block;font-weight:bold;">Année <i class="me-icon me-primary cancel"></i></a>');
        this.picker.select('a').first().observe('click', function () {
            $V(this.element, '');
            $V(this.elementView, '');
            this.hidePicker();
        }.bindAsEventListener(this));

        container = this.createTable(this.picker, 'Mois', 4, 5, null, parseInt(now.getFullYear()), 'year', date);
        var monthContainer = this.createTable(container, 'Jour', 6, 2, 1, 12, 'month', date);
        var dayContainer = this.createTable(monthContainer, null, 6, 6, 1, 31, 'day', date);

        var pos = this.elementView.cumulativeOffset();

        // Test d'overflow du calendrier par rapport à la fenêtre
        var calendarFullHeight = 400;
        var documentHeight = Math.max(document.documentElement.clientHeight, document.body.scrollHeight);
        var deltaBottom = documentHeight - (pos.top + this.elementView.getDimensions().height);
        if (deltaBottom < calendarFullHeight) {
            pos.top -= calendarFullHeight - deltaBottom;
        }

        this.picker.setStyle({
            top: pos.top + this.elementView.getDimensions().height + 'px',
            left: pos.left + 'px'
        });

        container = $(this.options.container);
        if (container) {
            container.insert(this.picker);
        } else {
            this.insert({after: this.picker});
        }

        if (monthContainer.firstChild.select('.current').length == 0) {
            monthContainer.hide();
        }

        if (dayContainer.firstChild.select('.current').length == 0) {
            dayContainer.hide();
        }

        e.stop();
        this.picker.show();
        document.observe('click', this.hidePicker = this.hidePicker.bindAsEventListener(this));
        ProgressiveCalendar.activePicker = this;
    },
    hidePicker: function (e) {
        this.picker.hide();
        ProgressiveCalendar.activePicker = null;
        document.stopObserving('click', this.hidePicker);
    }
});

var Calendar = {
    ref_pays: null,
    ref_cp: null,
    // This function is bound to date specification
    dateProperties: function (date, dates) {
        if (!date) {
            return {};
        }
        var properties = {},
            sDate = date.toDATE();

        if (dates.limit.start && dates.limit.start > sDate ||
            dates.limit.stop && dates.limit.stop < sDate) {
            properties.disabled = true;
        }

        if ((dates.current.start || dates.current.stop) &&
            !(dates.current.start && dates.current.start > sDate || dates.current.stop && dates.current.stop < sDate)) {
            properties.className = "active";
        }

        if (Calendar.checkHolliday(date)) {
            properties.className = properties.className + " ferie";
        }

        if (dates.spots.include(sDate)) {
            properties.label = "Date";
        }

        return properties;
    },

    prepareDates: function (dates) {
        dates.current.start = Calendar.prepareDate(dates.current.start);
        dates.current.stop = Calendar.prepareDate(dates.current.stop);
        dates.limit.start = Calendar.prepareDate(dates.limit.start);
        dates.limit.stop = Calendar.prepareDate(dates.limit.stop);
        dates.spots = dates.spots.map(Calendar.prepareDate);
    },

    prepareDate: function (date) {
        if (!date) {
            return null;
        }
        return Date.isDATETIME(date) ? Date.fromDATETIME(date).toDATE() : date;
    },

    regField: function (element, dates, options) {
        if (!$(element) || $V(element.form._locked) == 1) {
            return;
        }

        if (element.hasClassName('datepicker')) {
            return;
        }

        if (dates) {
            dates.spots = $A(dates.spots);
        }

        dates = Object.extend({
            current: {
                start: null,
                stop: null
            },
            limit: {
                start: null,
                stop: null
            },
            spots: []
        }, dates);

        Calendar.prepareDates(dates);

        // Default options

        options = Object.extend({
            datePicker: true,
            timePicker: false,
            altElement: element,
            altFormat: 'yyyy-MM-dd',
            icon: 'agenda',
            locale: DateFormat.CODE_LOCALES[Preferences.LOCALE],
            timePickerAdjacent: true,
            use24hrs: true,
            weekNumber: true,
            container: $(document.body),
            dateProperties: function (date) {
                return Calendar.dateProperties(date, dates)
            },
            center: false,
            editable: false
        }, options);

        options.useIcon = true;
        options.captureKeys = !options.inline;
        options.emptyButton = (!options.noView && !element.hasClassName('notNull'));

        var elementView;

        if (element.id) {
            var name_id = element.id;
        } else {
            var name_id = element.name;
        }

        if (!(elementView = $(element.form.elements[name_id + '_da']))) {
            elementView = new Element('input', {type: 'text', readonly: 'readonly', name: name_id + '_da'});
            elementView.className = (element.className || 'date');
            element.insert({before: elementView});
        }

        if (element.hasClassName('dateTime')) {
            options.timePicker = true;
            options.altFormat = 'yyyy-MM-dd HH:mm:ss';
        } else if (element.hasClassName('time')) {
            options.timePicker = true;
            options.datePicker = false;
            options.altFormat = 'HH:mm:ss';
            options.icon = "images/icons/time.png";
            options.icon = 'clock';
        }

        var datepicker = new Control.DatePicker(elementView, options);
        if (options.editable) {
            var onChange = (function () {
                var date = DateFormat.parse(elementView.value, this.options.currentFormat);
                this.element.value = DateFormat.format(date, this.options.altFormat);
            }).bindAsEventListener(datepicker);

            elementView.mask(datepicker.options.currentFormat.replace(/[a-z]/gi, "9"));
            elementView.observe("ui:change", onChange).observe("focus", onChange);
            elementView.writeAttribute("readonly", false);
        }

        if (options.inline) {
            Event.stopObserving(document, 'click', datepicker.hidePickerListener);
        }

        // Control.DatePicker.show() override in order to hack the Control.DatePickerPanel.dateClicked and Control.DatePickerPanel.dateChanged functions
        datepicker.show = function () {
            if (!this.pickerActive) {
                if (Control.DatePicker.activePicker) {
                    Control.DatePicker.activePicker.hide();
                }
                if (!this.element.disabled && this.element.type != 'hidden') {
                    this.element.focus();
                }
                if (!this.datepicker) {
                    this.datepicker = new Control.DatePickerPanel(this.options);
                }

                // setSeconds(0) when date is modified (preserve when there is no date (aka 'now')
                this.datepicker.dateClicked = function (date, isHour) {
                    if (date !== null) {
                        date.setSeconds(0);
                    }

                    date = date || new Date();

                    if (this.options.fillText) {
                        var fill = this.options.fillText;
                        new Url('cerfa', 'getTextYear')
                            .addParam('date', date.getDate() + '-' + (date.getMonth() + 1) + '-' + date.getFullYear())
                            .requestJSON(function (data) {
                                $$("[name='" + fill + "']").each(function (element) {
                                    $V(element, data.date);
                                });
                            });
                    }

                    if (date) {
                        if (this.options.onSelect && !isHour) {
                            this.options.onSelect(date);
                        }
                        this.selectDate(date);
                    }
                };

                // setSeconds(0) when exact minutes are given
                this.datepicker.dateChanged = function (date) {
                    if (date) {
                        date.setSeconds(0);

                        if ((!this.options.timePicker || !this.options.datePicker) && this.options.onHover) {
                            this.options.onHover(date);
                        }
                        this.selectDate(date);
                    }
                };

                this.save();

                if (!this.options.inline) {
                    var pos = this.element.cumulativeOffset();
                    var dim = this.element.getDimensions();
                    this.datepicker.element.setStyle({
                        left: pos.left + 'px',
                        top: pos.top + dim.height + 'px'
                    });
                }

                if (this.altElement && this.altElement.value) {
                    this.datepicker.selectDate(DateFormat.parseFormat(this.altElement.value, this.options.altFormat));
                } else {
                    this.datepicker.selectDate(DateFormat.parseFormat(this.element.value, this.options.currentFormat));
                }

                if (this.options.captureKeys) {
                    this.datepicker.captureKeys();
                }

                var container;
                if (!(container = $(this.options.container))) {
                    container = this.options.inline ? this.element : this.element.up();
                    container.insert({after: this.datepicker.element});
                } else {
                    container.insert(this.datepicker.element);
                }
                Event.observe(document, 'click', this.hidePickerListener, true);
                this.pickerActive = true;
                Control.DatePicker.activePicker = this;
                this.pickerClicked();
            }
        }.bind(datepicker);

        var showPicker = function (e) {
            Event.stop(e);

            // Focus will be triggered a second time when the date is selected
            if (Prototype.Browser.IE) {
                if (this._dontShow && e.type !== "click") {
                    this._dontShow = false;
                    return;
                } else {
                    this._dontShow = true;
                }
            }

            this.show.bind(datepicker)(e);

            var dp = $(this.datepicker.element);

            if (!dp) {
                return;
            }

            dp.setStyle({zIndex: ""}). // FIXME do not set it in datepicker.js
                unoverflow();

        }.bindAsEventListener(datepicker);

        if (options.noView) {
            // @todo: Passer ça en classe CSS
            datepicker.element.setStyle({
                width: 0,
                border: 'none',
                background: 'none',
                position: 'absolute'
            }).addClassName("opacity-0");
            if (datepicker.icon) {
                datepicker.icon.setStyle({
                    position: 'relative',
                    right: 0,
                    top: ""
                });
            }
        } else {
            elementView.observe('click', showPicker)/*.observe('focus', showPicker)*/;

            // Evenement de mise à jour de la vue en fonction du champ caché
            element.observe("date:change", (function (e) {
                var element = Event.element(e);

                if (!$V(element)) {
                    $V(this, '');
                    return;
                }

                var view = "";
                switch (true) {
                    case element.hasClassName("date"):
                        view = Date.fromDATE($V(element)).toLocaleDate();
                        break;
                    case element.hasClassName("dateTime"):
                        view = Date.fromDATETIME($V(element)).toLocaleDateTime();
                        break;
                    case element.hasClassName("time"):
                        view = Date.fromTIME($V(element)).toLocaleTime();
                        break;
                }

                $V(this, view);
            }).bind(elementView));
        }

        // We update the view
        if (element.value && !elementView.value) {
            var date = DateFormat.parse(element.value, datepicker.options.altFormat);
            elementView.value = DateFormat.format(date, datepicker.options.currentFormat) || '';
        }

        if (datepicker.icon) {
            datepicker.icon.observe("click", function () {
                var element = this.datepicker ? this.datepicker.element : this.element;

                if (options.center) {
                    var posIcon = this.icon.cumulativeOffset();
                    $(element).centerHV(posIcon.top);
                    Calendar.mobileHide(datepicker);
                } else {
                    $(element).setStyle({zIndex: ""}). // FIXME do not set it in datepicker.js
                        unoverflow();
                }
            }.bindAsEventListener(datepicker));
        }

        datepicker.handlers.onSelect = function (date) {
            element.fire("ui:change");

            if (elementView != element) {
                elementView.fire("ui:change");
            }
        };

        element.addClassName('datepicker');
        return datepicker;
    },
    mobileHide: function (picker) {
        document.observeOnce('click', function (e) {
            $(picker).hide();
        });
    },

    regProgressiveField: function (element, options) {
        new ProgressiveCalendar(element, options);
    },

    /**
     * Set calendar to the current date/time
     *
     * @param {HTMLInputElement} element The hidden input element
     *
     * @return void
     */
    setNow: function (element) {
        var form = element.form;
        if (element.id) {
            var name_id = element.id;
        } else {
            var name_id = element.name;
        }
        var da = form.elements[name_id + "_da"];
        var now = new Date();

        if (element.hasClassName("date")) {
            $V(element, now.toDATE());
            $V(da, now.toLocaleDate());
        } else if (element.hasClassName("dateTime")) {
            $V(element, now.toDATETIME());
            $V(da, now.toLocaleDateTime());
        } else {
            $V(element, now.toTIME());
            $V(da, now.toLocaleTime());
        }
    },

    clear: function (element) {
        var form = element.form;
        if (element.id) {
            var name_id = element.id;
        } else {
            var name_id = element.name;
        }
        var da = form.elements[name_id + "_da"];

        $V(element, '');
        $V(da, '');
    },

    //check if a date is in holliday calendar
    checkHolliday: function (date) {
        var sDate = date.toDATE();

        //country
        var datesHolidays = Calendar.getDateHolidays(date);
        var length = datesHolidays.length;
        for (var i = 0; i < length; i++) {
            if (datesHolidays[i] == sDate) {
                return true;
            }
        }

        //states/canton/regions
        var datesHolidaysCP = Calendar.getDateHolidaysCP(date);
        length = datesHolidaysCP.length;
        for (var j = 0; j < length; j++) {
            if (datesHolidaysCP[j] == sDate) {
                return true;
            }
        }

        return false;
    },

    getDateHolidays: function (date, ref_pays) {
        var year = date.getFullYear();
        var next_year = parseInt(year) + 1;
        var datesH = [];

        ref_pays = ref_pays || this.ref_pays;
        switch (ref_pays) {
            // France
            case 1:
                // Static
                datesH.push(year + "-01-01");      // Jour de l'an
                datesH.push(year + "-05-01");      // Fête du travail
                datesH.push(year + "-05-08");      // Victoire de 1945
                datesH.push(year + "-07-14");      // Fête nationale
                datesH.push(year + "-08-15");      // Assomption
                datesH.push(year + "-11-01");      // Toussaint
                datesH.push(year + "-11-11");      // Armistice 1918
                datesH.push(year + "-12-25");      // Noël
                datesH.push(next_year + "-01-01"); // Jour de l'an

                // Dynamic
                var easter = Date.fromDATE(Calendar.getEasterDate(year));
                datesH.push(easter.addDays(1).toDATE());  // Lundi de Pâques
                datesH.push(easter.addDays(38).toDATE()); // Jeudi de l'Ascension
                datesH.push(easter.addDays(11).toDATE()); // Lundi de Pentecôte
                break;

            // Switzerland
            case 2:
                datesH.push(year + "-01-01");      // Jour de l'an
                datesH.push(year + "-08-01");      // Fête nationale suisse
                datesH.push(year + "-12-25");      // Noël
                datesH.push(next_year + "-01-01"); // Jour de l'an
                break;

            default:
        }

        return datesH;
    },

    /**
     * get the list of holidays following state CP
     *
     * @param date
     * @param ref_pays
     * @param ref_cp
     * @returns {Array}
     */
    getDateHolidaysCP: function (date, ref_pays, ref_cp) {
        var year = date.getFullYear();
        var datesH = [];

        ref_pays = ref_pays || this.ref_pays;
        ref_cp = ref_cp || this.ref_cp;

        if (!ref_cp) {
            return datesH;
        }

        switch (ref_pays) {
            // France
            case 1:
                break;
        }

        return datesH;
    },

    getEasterDate: function (year) {
        var C = Math.floor(year / 100);
        var N = year - 19 * Math.floor(year / 19);
        var K = Math.floor((C - 17) / 25);
        var I = C - Math.floor(C / 4) - Math.floor((C - K) / 3) + 19 * N + 15;
        I = I - 30 * Math.floor((I / 30));
        I = I - Math.floor(I / 28) * (1 - Math.floor(I / 28) * Math.floor(29 / (I + 1)) * Math.floor((21 - N) / 11));
        var J = year + Math.floor(year / 4) + I + 2 - C + Math.floor(C / 4);
        J = J - 7 * Math.floor(J / 7);
        var L = I - J;
        var M = 3 + Math.floor((L + 40) / 44);
        var D = L + 28 - 31 * Math.floor(M / 4);

        return year + "-" + Calendar.padout(M) + '-' + Calendar.padout(D);
    },

    padout: function (number) {
        return (number < 10) ? '0' + number : number;
    }
};


/**
 * Durations expressed in milliseconds
 */
Object.extend(Date, {
    // Exact durations
    millisecond: 1,
    second: 1000,
    minute: 60 * 1000,
    hour: 60 * 60 * 1000,
    day: 24 * 60 * 60 * 1000,
    week: 7 * 24 * 60 * 60 * 1000,

    // Approximative durations
    month: 30 * 24 * 60 * 60 * 1000,
    year: 365.2425 * 24 * 60 * 60 * 1000,

    isDATE: function (sDate) {
        return /^\d{4}-\d{2}-\d{2}$/.test(sDate);
    },
    isDATETIME: function (sDateTime) {
        return /^\d{4}-\d{2}-\d{2}[ \+T]\d{2}:\d{2}:\d{2}$/.test(sDateTime);
    },

    // sDate must be: YYYY-MM-DD
    fromDATE: function (sDate) {
        var match;

        if (!(match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(sDate))) {
            Assert.that(match, "'%s' is not a valid DATE", sDate);
        }

        return new Date(match[1], match[2] - 1, match[3]); // Js months are 0-11!!
    },

    // sDateTime must be: YYYY-MM-DD HH:MM:SS
    fromDATETIME: function (sDateTime) {
        var match, re = /^(\d{4})-(\d{2})-(\d{2})[ \+T](\d{2}):(\d{2}):(\d{2})$/;

        if (/^(\d{4})-(\d{2})-(\d{2})[ \+T](\d{2}):(\d{2})$/.exec(sDateTime)) {
            sDateTime += '-00';
        }

        if (!(match = re.exec(sDateTime))) {
            Assert.that(match, "'%s' is not a valid DATETIME", sDateTime);
        }

        return new Date(match[1], match[2] - 1, match[3], match[4], match[5], match[6]); // Js months are 0-11!!
    },

    // sTime must be: HH:MM:SS
    fromTIME: function (sTime) {
        var match;

        if (!(match = /^(\d{2}):(\d{2}):(\d{2})$/.exec(sTime))) {
            Assert.that(match, "'%s' is not a valid TIME", sTime);
        }

        return new Date(0, 0, 0, match[1], match[2], match[3]);
    },

    fromLocaleDate: function (sDate) {
        var match, re = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!(match = re.exec(sDate))) {
            Assert.that(match, "'%s' is not a valid display date", sDate);
        }

        return new Date(match[3], match[2] - 1, match[1]);
    },

    fromLocaleDateTime: function (sDate) {
        var match, re = /^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})$/;
        if (!(match = re.exec(sDate))) {
            Assert.that(match, "'%s' is not a valid display datetime", sDate);
        }

        return new Date(match[3], match[2] - 1, match[1], match[4], match[5], match[6]);
    }
});

Class.extend(Date, {
    format: function (format) {
        return DateFormat.format(this, format);
    },
    getWeekNumber: function () {
        var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
        d.setDate(d.getDate() - (d.getDay() + 6) % 7 + 3); // Nearest Thu
        var ms = d.valueOf(); // GMT
        d.setMonth(0);
        d.setDate(4); // Thu in Week 1
        return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
    },
    toDATE: function () {
        var y = this.getFullYear(),
            m = this.getMonth() + 1, // Js months are 0-11!!
            d = this.getDate();
        return printf("%04d-%02d-%02d", y, m, d);
    },
    toTIME: function () {
        var h = this.getHours(),
            m = this.getMinutes(),
            s = this.getSeconds();
        return printf("%02d:%02d:%02d", h, m, s);
    },
    toDATETIME: function (useSpace) {
        var h = this.getHours(),
            m = this.getMinutes(),
            s = this.getSeconds();

        if (useSpace) {
            return this.toDATE() + printf(" %02d:%02d:%02d", h, m, s);
        } else {
            return this.toDATE() + printf("+%02d:%02d:%02d", h, m, s);
        }
    },

    toLocaleDate: function () {
        var y = this.getFullYear();
        var m = this.getMonth() + 1; // Js months are 0-11!!
        var d = this.getDate();
        return printf("%02d/%02d/%04d", d, m, y);
    },

    toLocaleDateTime: function () {
        var h = this.getHours();
        var m = this.getMinutes();
        return this.toLocaleDate() + printf(" %02d:%02d", h, m);
    },

    toLocaleTime: function () {
        var h = this.getHours();
        var m = this.getMinutes();
        return printf(" %02d:%02d", h, m);
    },

    resetDate: function () {
        this.setFullYear(1970);
        this.setMonth(1);
        this.setDate(1);
    },

    resetTime: function () {
        this.setHours(0);
        this.setMinutes(0);
        this.setSeconds(0, 0); // s, ms
    },

    addDays: function (iDays) {
        this.setDate(this.getDate() + parseInt(iDays, 10));
        return this;
    },
    addHours: function (iHours) {
        this.setHours(this.getHours() + parseInt(iHours, 10));
        return this;
    },
    addMinutes: function (iMinutes) {
        this.setMinutes(this.getMinutes() + parseInt(iMinutes, 10));
        return this;
    },
    addMonths: function (iMonths) {
        this.setMonth(this.getMonth() + parseInt(iMonths, 10));
        return this;
    },
    addYears: function (iYears) {
        this.setFullYear(this.getFullYear() + parseInt(iYears, 10));
        return this;
    },
    cloneDate: function () {
        return new Date(this.getTime());
    }
});

/**
 * Date.now shim
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/now
 */
if (!Date.now) {
    Date.now = function now() {
        return new Date().getTime();
    };
}
