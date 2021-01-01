/**
 * Rozšíření nette.ajax
 */

(function($, undefined) {

    $.nette.ext('datePicker', {
        load: function() {
            this.enableDatePicker()
        },
        complete: function(jqXHR, status, settings) {
            this.enableDatePicker()
        },
    }, {
        enableDatePicker: function() {
            if (Modernizr.inputtypes.date) {
                return;
            }
            $('input[type=date]').each(function() {
                this.type = 'text'
                this.placeholder = 'rrrr-mm-dd'
                $(this).dateRangePicker({
                    language: 'cz',
                    autoClose: true,
                    singleDate: true,
                    showShortcuts: false,
                    singleMonth: true,
                    startOfWeek: 'monday',
                })
            })
            $.dateRangePickerLanguages.custom = {
                'selected': 'Vybráno:',
                'day': 'Den',
                'days': 'Dny',
                'apply': 'Zavřít',
                'week-1': 'Po',
                'week-2': 'Út',
                'week-3': 'St',
                'week-4': 'Čt',
                'week-5': 'Pa',
                'week-6': 'So',
                'week-7': 'Ne',
                'week-number': 'T',
                'month-name': ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
                'shortcuts': 'Zkratky',
                'custom-values': 'Vlastní hodnoty',
                'past': 'Minulé',
                'following': 'Následující',
                'previous': 'Předchozí',
                'prev-week': 'Týden',
                'prev-month': 'Měsíc',
                'prev-year': 'Rok',
                'next': 'Další',
                'next-week': 'Týden',
                'next-month': 'Měsíc',
                'next-year': 'Rok',
                'less-than': 'Interval by neměl být delší než %d dní',
                'more-than': 'Interval by neměl být kratší než %d dní',
                'default-more': 'Vyberte prosím interval delší než %d dní',
                'default-single': 'Zvolte prosím datum',
                'default-less': 'Vyberte prosím interval kratší než %d dní',
                'default-range': 'Vyberte prosím interval mezi %d a %d dny',
                'default-default': 'Vyberte prosím interval',
                'time': 'Čas',
                'hour': 'Hodina',
                'minute': 'Minuta',
            }
        },
    })

})(jQuery)
