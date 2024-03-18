(() => {
    "use strict";
    const e = window.wc.wcBlocksRegistry,
        t = window.wp.i18n,
        n = window.wc.wcSettings,
        o = window.wp.htmlEntities,
        a = window.React,
        j = window.JSON,
        s = (0, n.getSetting)("cardgategiropay_data", {}),
        c = (0, t.__)("Giropay", "wc_payment_method_cardgategiropay"),
        l = (0, o.decodeEntities)(s.title) || c,
        i = (e) => {
            const [n, r] = (0, a.useState)(""),
                { eventRegistration: l, emitResponse: i } = e,
                { onPaymentSetup: k } = l;
            a.useEffect(() => {
                const tl = (newTotal, currency)=> {
                    var feeText = currency + newTotal;
                    var totalSpan = "<span class='wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value'>" + feeText + "</span>";
                    var total = jQuery('.wc-block-components-totals-footer-item .wc-block-formatted-money-amount:first');
                    total.replaceWith(totalSpan);
                };
               const ttl = (newTotal, currency) => {
                    var feeText = currency + newTotal;
                    var totalSpan = "<span class='wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value'>" + feeText + "</span>";
                    var total = jQuery('div.wp-block-woocommerce-checkout-order-summary-taxes-block.wc-block-components-totals-wrapper > div > span.wc-block-formatted-money-amount.wc-block-components-formatted-money-amount.wc-block-components-totals-item__value:first');
                    total.replaceWith(totalSpan);
               };
                jQuery.ajax({
                    url: s.feeUrl,
                    method: 'POST',
                    data: {
                        action: 'wp_ajax_cardgate_checkout_fees',
                        method: e.activePaymentMethod
                    },
                    complete: function complete(jqXHR, textStatus) {},
                    success: function success(response, textStatus, jqXHR) {
                        let fee = jQuery('.wc-block-components-totals-fees');
                        if (!response.data.amount) {
                            fee === null || fee === void 0 || fee.hide();
                            tl(response.data.newTotal.toFixed(2).replace('.', ','), response.data.currency);
                            ttl(response.data.totalTax.toFixed(2).replace('.', ','), response.data.currency);
                        } else {
                            let newFee = "<div class='wc-block-components-totals-item wc-block-components-totals-fees'>" + "<span class='wc-block-components-totals-item__label'>" + response.data.name + "</span>" + "<span class='wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value'>" + response.data.currency +  response.data.amount.toFixed(2).replace('.', ',') +"</span>" + "<div class='wc-block-components-totals-item__description'>" + "</div>" + "</div>";
                            if (fee.length) {
                                fee.replaceWith(newFee);
                                tl(response.data.newTotal.toFixed(2).replace('.', ','), response.data.currency);
                                ttl(response.data.totalTax.toFixed(2).replace('.', ','), response.data.currency);
                            } else {
                                let subtotal = jQuery('.wc-block-components-totals-item:first');
                                subtotal.after(newFee);
                                tl(response.data.newTotal.toFixed(2).replace('.', ','), response.data.currency);
                                ttl(response.data.totalTax.toFixed(2).replace('.', ','), response.data.currency);
                            }
                        }
                    },
                    error: function error(jqXHR, textStatus, errorThrown) {
                        console.warn(textStatus, errorThrown);
                    }
                });

            });
            return a.createElement("div", null, (0, o.decodeEntities)(s.description || ""));
        },

        r = {
            name: "cardgategiropay",
            label: a.createElement((e) => {
                var p = a.createElement("img", { src: s.icon, width: 28, height: 24, style: { display: "inline" } });
                if(!s.show_icon) p = null;
                return a.createElement("span", { className: "wc-block-components-payment-method-label wc-block-components-payment-method-label--with-icon" }, p, (0, o.decodeEntities)(s.title) || c);
            }, null),
            content: a.createElement(i, null),
            edit:a.createElement(i, null),
            icons: null,
            canMakePayment: (e) => {
                return !0;
            },
            ariaLabel: l,
            supports: { features: a.supports },
        };
    (0, e.registerPaymentMethod)(r);
})();