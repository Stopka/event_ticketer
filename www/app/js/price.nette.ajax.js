/**
 * Rozšíření nette.ajax
 */

(function ($, undefined) {

    $.nette.ext('price', {
        load: function () {
            $(document).on('change, click, blur', this.selectors.input, $.proxy(this.onChange, this));
            $(document).on('keydown', this.selectors.input, $.proxy(this.onKeyDown, this));
            $(document).on('click', this.selectors.recalculate, $.proxy(this.onRecalculateClicked,this))
            this.onLoaded();
            this.restorePrechecked();
            this.restorePredisabled();
        },
        complete: function (jqXHR, status, settings) {
            this.onComplete(jqXHR);
            this.restorePrechecked();
            this.restorePredisabled();
        }
    }, {
        selectors: {
            recalculate: '.price_recalculate',
            space: '*[data-price-currency]',
            subspace: '.price_subspace',
            input: '*[data-price-value]',
            total: '.price_total',
            subtotal: '.price_subtotal',
            amount: '.price_amount',
            currency: '.price_currency'
        },
        restorePrechecked: function(){
            $('[data-price-value][data-price-precheck]').each(function (index,elem) {
                var el = $(elem);
                if(el.val()!=el.data('price-precheck')) {
                    return;
                }
                elem.checked = true;
            })
        },
        restorePredisabled: function(){
            input = $('[data-price-predisable]').parent().find('input');
            input.attr('disabled',true);
        },
        onComplete: function (event) {
            this.planRecalcucation();
        },
        onLoaded: function (event) {
            this.planRecalcucation();
        },
        onRecalculateClicked: function (event) {
            event.preventDefault();
            this.planRecalcucation();
        },
        onChange: function (event) {
            this.planRecalcucation();
        },
        onKeyDown: function (event) {
            if (!LiveForm.isSpecialKey(event.which)) {
                return;
            }
            this.planRecalcucation();
        },
        planRecalcucation: function(){
            setTimeout($.proxy(this.recalculate,this),300);
        },
        recalculate: function () {
            console.log('recalculating price');
            $(this.selectors.space).each(
                $.proxy(this.recalculateSpace, this)
            );
        },
        recalculateSpace: function (index, space_el) {
            var el = $(space_el);
            var currency = el.data('price-currency');
            if (!currency) {
                return;
            }
            this.recalculateSpaceTotal(el, currency);
            this.recalculateSpaceSubtotals(el, currency);
        },
        recalculateSpaceTotal: function (el, currency) {
            sum = this.sumSubSpaceInputs(el, currency);
            this.setTotalHtmlPrice(el, sum, currency);
        },
        recalculateSpaceSubtotals: function (el, currency) {
            subspaces = el.find(this.selectors.subspace);
            subspaces.each($.proxy(function (index,element) {
                this.data.recalculateSpaceSubtotal($(element),this.currency);
            }, {data: this, currency: currency}));
        },
        recalculateSpaceSubtotal: function (el, currency) {
            sum = this.sumSubSpaceInputs(el, currency);
            this.setSubtotalHtmlPrice(el, sum, currency);
            this.recalculateSpaceSubtotals(el, currency);
        },
        sumSubSpaceInputs: function (el, currency) {
            var sum = 0;
            el.find(this.selectors.input).each(function (index, input_el) {
                var el = $(input_el);
                var value = Nette.getValue(input_el);
                if (el.val() !== value) {//optření proti duplicitnímu započítávání
                    return;
                }
                var prices = el.data('price-value');
                if (prices[value] && prices[value].currency != currency) {//pokud nesouhlasí měna
                    return;
                }
                sum += Number(prices[value].amount);
            });
            return sum;
        },
        setSubtotalHtmlPrice: function (subspace_el, amount, currency) {
            this.setHtmlPrice(subspace_el, this.selectors.subtotal, amount, currency)
        },
        setTotalHtmlPrice: function (space_el, amount, currency) {
            this.setHtmlPrice(space_el, this.selectors.total, amount, currency)
        },
        setHtmlPrice: function (space_el, selector, amount, currency) {
            total_els = space_el.find(selector);
            total_els.find(this.selectors.amount).text(amount);
            total_els.find(this.selectors.currency).text(currency);
        }
    });

})(jQuery);
