/**
 * Google Tag Manager
 *
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Ui/js/modal/alert',
    'jquery',
], function(alert, $){
    'use strict';

    var mpEditOrderEmailPopup = function () {
        $('#mp_edit_order_email').alert({
            title: 'Warning',
            content: 'Warning content',
            actions: {
                always: function(){}
            }
        });
    };

    var mpEditOrderEmailPost = function ( postUrl ) {

        var postData = {form_key: FORM_KEY};

        //global var configForm
        $('#mp_edit_order_email:input').serializeArray().map(function(field){
            postData[name[2]] = field.value;
        });


        $.ajax({
            url: postUrl,
            type: 'post',
            dataType: 'html',
            data: postData,
            showLoader: true
        }).done(function(response) {
            if (typeof response === 'object') {
                if (response.error) {
                    alert({ title: 'Error', content: response.message });
                } else if (response.ajaxExpired) {
                    window.location.href = response.ajaxRedirect;
                }
            } else {
                alert({
                    title:'',
                    content:response,
                    buttons: []
                });
            }

        });
    };



    return function (config) {
        var html = '<button id="mpEditOrderEmailPopup">edit</button>';
        $('table.order-account-information-table tr a[href^="mailto:"]').parent().append(html);

        $('#mpEditOrderEmailPopup').click(function () {
            mpEditOrderEmailPopup();
        });

        $('#mpSaveNewEmail').click(function () {
            mpEditOrderEmailPost(config.url);
        });
    }

});