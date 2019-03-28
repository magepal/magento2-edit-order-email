/**
 * Google Tag Manager
 *
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Ui/js/modal/modal',
    'jquery',
    'mage/validation'
], function (alert, $, validator) {
    'use strict';

    var emailModal;

    var mpEditOrderEmailPopup = function () {
        if (!emailModal) {
            emailModal = $('#mp_edit_order_email').modal({
                title: 'Edit Email',
                content: 'Warning content',
                buttons: []
            });
        }

        emailModal.modal('openModal');
    };

    var mpSaveNewEmailFormPost = function ( postUrl ) {

        var postData = {form_key: FORM_KEY};

        //global var configForm
        $('#mp_edit_order_email').find('input').serializeArray().map(function (field) {
            postData[field.name] = field.value;
        });

        $.ajax({
            url: postUrl,
            type: 'post',
            dataType: 'json',
            data: postData,
            showLoader: true,
            success: function (res) {
                if (res.error === false) {
                    $('#mp_edit_order_email').modal('hide');
                    return true;
                } else {
                    $(".errormsg").html(res.message);
                    return false;
                }
            }
            }).done(function (response) {
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
                return true;

            });
    };

    return function (config) {
        var html = '<button id="mpEditOrderEmailPopup">edit</button>';
        $('table.order-account-information-table tr a[href^="mailto:"]').parent().append(html);

        $('#mpEditOrderEmailPopup').click(function () {
            mpEditOrderEmailPopup();
        });

        $('#mpSaveNewEmailFormPost').click(function () {
            if ($.validator.validateElement($("#mp_edit_order_email input[name='email']"))) {
                mpSaveNewEmailFormPost(config.postUrl);
            }
        });
    }

});