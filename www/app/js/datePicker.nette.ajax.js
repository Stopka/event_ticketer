/**
 * Rozšíření nette.ajax
 */

(function ($, undefined) {

    $.nette.ext('datePicker', {
        load: function () {
            this.enableDatePicker();
        },
        complete: function (jqXHR, status, settings) {
            this.enableDatePicker();
        }
    }, {
        enableDatePicker: function(){
            if(!Modernizr.inputtypes.date){
                $("input[type=date]").each(function(){
                    this.type='text';
                    $(this).dateRangePicker({
                        language: 'custom',
                        autoClose: true,
                        singleDate : true,
                        showShortcuts: false,
                        singleMonth: true
                    });
                });
            }
        }
    });

})(jQuery);