/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

define([
    'Magento_Ui/js/modal/modal',
    'jquery',
    'mage/translate'
], function (alert, $, $t) {
    'use strict';

    var emailModal;
    var $form = $('form.change-order-email');
    var $emailHref = $('table.order-account-information-table tr a[href^="mailto:"]');

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


        if ($form.valid()) {
            var url = $form.attr('action');
            var postData = $form.serializeArray();
            postData.push({form_key : FORM_KEY});

            try {
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'POST',
                    showLoader: true,
                    data: $.param(postData),
                    complete: function (data) {
                        if (typeof data === 'object') {
                            data = data.responseJSON;
                            if (data.error === false) {
                                setTimeout(function () {
                                    emailModal.modal('closeModal');
                                },10000);

                                if (data.email) {
                                    $emailHref.attr("href", "mailto:" + data.email).text(data.email)
                                }
                            }

                            if (data.ajaxExpired) {
                                window.location.href = data.ajaxRedirect;
                            }

                            $(".mage-error").html(data.message);
                        } else {
                            $(".mage-error").html($t('Unknown Error'));
                        }

                        return false;
                    }
                });
            } catch (e) {
                $(".mage-error").html(e.message);
            }
        } else {
            $("div.menu-wrapper._fixed").removeAttr("style");
        }

        return false;

    };

    return function (config) {
        var html = '<button id="mpEditOrderEmailPopup">' + config.buttonLabel + '</button>';
        $emailHref.parent().append(html);

        $('#mpEditOrderEmailPopup').click(function () {
            mpEditOrderEmailPopup();
        });

        $('form.change-order-email button').on('click', function () {
            mpSaveNewEmailFormPost(config.postUrl);
        });

        $form.on("keypress", function (event) {
            if (event.keyCode === 13) {
                mpSaveNewEmailFormPost(config.postUrl);
            }

            return event.keyCode != 13;
        });

        $form.submit(function (event) {
            return false;
        });

    }

});