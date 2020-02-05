define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function(Component, customerData){
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Findify_Findify/update_cart'
        },
        initialize: function () {
            this._super();
            this.updateCartData = customerData.get('findify-update-cart');
        }
    });

});
