/**
 * Rozšíření nette.ajax
 */

//Opatření pro refresh session při dlouhém uploadu souborů
$(document).on('submit', 'form', function(event){
    if(typeof refresh_session ==  undefined){
        return;
    }
    if($(this).find("input[type=file]") && $('iframe.session_refresher').length==0){
        $('<iframe src="'+refresh_session.link+'" style="border: none; width: 1px; height: 1px;" class="session_refresher"></iframe>').appendTo("body");
    }
});

$(document).ready(function() {
    $("[data-autocomplete-array]").each(function(){
        el=$(this);
        el.autocomplete({
            source: el.data('autocomplete-array'),
            minLength: 0
        }).focus(function () {
            //this shows the full list after focusing the text input
            $(this).autocomplete('search', $(this).val())
        });
    });

    $(function () {
        $.nette.init();
    });
});

(function($, undefined) {

    $.nette.ext('localformscript', {
        start: function (jqXHR, settings) {
            if(typeof beforeAjaxFormSubmit==="function" && settings.nette!==undefined && settings.nette.form!==undefined){
                beforeAjaxFormSubmit();
            }
        },
        complete: function (jqXHR, status, settings) {
            if(typeof afterAjaxFormSubmit==="function"&& settings.nette!==undefined && settings.nette.form!==undefined){
                afterAjaxFormSubmit();
            }
        }
    }, {
        instances: []
    });

})(jQuery);
