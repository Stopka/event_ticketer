/**
 * Doplnění chybějícího mimeType validátoru
 * @param elem
 * @param arg
 * @param val
 * @returns {boolean}
 */
Nette.validators.mimeType = function (elem, arg, val) {
    if (window.FileList && val instanceof FileList) {
        for (var i = 0; i < val.length; i++) {
            var type = val[i].type;
            if (type) {
                if(!Nette.isArray(arg)){
                    arg=arg.split(',');
                }
                for(var j=0; j<arg.length; j++) {
                    if (type == arg[j]) {
                            return true;
                    }
                }
                return false;
            }
        }
    }
    return true;
};

/**
 * Javascriptové obohacení url o parametry
 * @param string
 * @param params
 * @returns {*}
 */
Nette.parametrizeLink= function (string, params) {
    for (var i = 0; i < params.length; i++) {
        string = string.replace("Par_" + i, params[i]);
    }
    return string;
};

/**
 * Obohacuje výchozí handler Live from o odebrání kontorlních tříd signal server validace
 * @type {any}
 */
var LiveForm_eventHandler_old=LiveForm.eventHandler;
LiveForm.eventHandler = function(event){
    if(event){
        $(event.target).removeClass("validated")
            .removeClass("validated-success")
            .removeClass("validated-failed");
    }
    LiveForm_eventHandler_old(event);
};

/**
 * Helper pro tvorbu validátoru s kontorlou na serveru
 * @param signal_link
 * @returns {Function}
 */
Nette.createServerSignalValidator=function (signal_link){
    return function (elem, arg, value) {
        if($(elem).hasClass("validated")){
            return $(elem).hasClass('validated-success');
        }

        $.nette.ajax({
            url: Nette.parametrizeLink(signal_link, [value]),
            spinner: true,
            success: function(data){
                elem=$(this);
                elem.addClass("validated")
                    .addClass(data.result?"validated-success":"validated-failed");
                Nette.validateControl(elem);
            },
            context: elem
        });
        return true;
    }
}

/**
 * Kanfigurace live-form-validace
 */
LiveForm.setOptions({
    controlErrorClass: 'form-control-error',            // CSS class for an invalid control
    messageErrorClass: 'form-error-message',            // CSS class for an error message
    controlValidClass: 'form-control-valid',            // CSS class for a valid message
    disableLiveValidationClass: 'no-live-validation',             // CSS class for a valid message
    disableShowValidClass: 'no-show-valid',
    showValid: false,                                   // show message when valid
    dontShowWhenValidClass: 'dont-show-when-valid',     // control with this CSS class will not show valid message
    messageTag: 'span',                                 // tag that will hold the error/valid message
    messageIdPostfix: '_message',                       // message element id = control id + this postfix
    messageErrorPrefix: '<i class="fa fa-exclamation-triangle"></i> ',
    wait: 300
});

/**
 * Nahrazuje výchozí skrývání elementu skrýváním s animací
 */
Nette.toggle = function (id, visible) {
    var el = $('#' + id);
    if (visible) {
        el.slideDown();
    } else {
        el.slideUp();
    }
};