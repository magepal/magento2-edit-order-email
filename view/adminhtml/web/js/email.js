/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Ui/js/modal/modal',
    'jquery'
], function (alert, $) {
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
        var form = $('form.change-order-email');

        form.submit(function (e) {
            if (form.validation('isValid')) {
                var url = form.attr('action');
                var data = $(this).serialize();
                data['form_key'] =  FORM_KEY;


                try {
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'POST',
                        data: data,
                        success: function (data) {
                            if (data.error === false) {
                                emailModal.modal('closeModal');
                                return true;
                            } else {
                                $(".errormsg").html(data.message);
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
                } catch (e) {
                    loadingMessage.html(e.message);
                }
            } else {
                $("div.menu-wrapper._fixed").removeAttr("style");
            }
            return false;
        });

    };

    return function (config) {
        var html = '<button id="mpEditOrderEmailPopup">' + config.buttonLabel + '</button>';
        $('table.order-account-information-table tr a[href^="mailto:"]').parent().append(html);

        $('#mpEditOrderEmailPopup').click(function () {
            mpEditOrderEmailPopup();
        });

        mpSaveNewEmailFormPost(config.postUrl);
    }

});