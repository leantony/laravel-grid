var _modal = {};

(function ($) {
    'use strict';
    var modal = function (options) {
        var defaultOptions = {
            // id of modal form template on page
            modal_id: 'bootstrap_modal',
            // id of notification element where messages will be displayed on the modal. E.g validation errors
            notification_id: 'modal-notification',
            // the id of the form that contains the data that will be sent to the server
            form_id: 'modal_form',
            // the class of the element that will trigger the modal. typically a link
            modalTriggerSelector: '.show_modal_form',
            // when the modal is shown
            onShown: function (e, modal) {
                if (modal) {
                    if (modal.options.onShown) {
                        modal.options.onShown(e);
                    }
                }
            },
            onHidden: function (e, modal) {
                $(this).removeData('bs.modal');
            },
            onShow: function (e, modal) {
                // display a loader, when the modal is being displayed
                var spinner_content = '<div class="row"><div class="col-md-12"><div class="text-center"><i class="fa fa-spinner fa-6x fa-spin color-primary mt30"></i></div></div></div>';
                $('#' + modal.options.modal_id).find('.modal-content').html(spinner_content);
            },
            onLoaded: function (e, modal) {
                if (modal && modal.options) {
                    modal.options.onLoaded(e);
                }
            }
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };

    /**
     * show the modal
     */
    modal.prototype.show = function () {
        var $this = this;
        var modal_id = $this.options.modal_id;
        var clickHandler = function (e) {
            var modal_size = $(e).data('modal-size');
            var modal = $('#' + modal_id);
            if (!modal_size) {
                modal.find('.modal-dialog').addClass(modal_size);
            }
            var url = $(e).attr('href') || $(e).data('url');
            modal
                .on('shown.bs.modal', function () {
                    $this.options.onShown.call(this, e, $this);
                })
                .on('hidden.bs.modal', function () {
                    $this.options.onHidden.call(this, e, $this);
                })
                .on('show.bs.modal', function () {
                    $this.options.onShow.call(this, e, $this);
                })
                .on('loaded.bs.modal', function () {
                    $this.options.onLoaded.call(this, e, $this);
                })
                .modal({
                    remote: url,
                    backdrop: 'static',
                    refresh: true
                });
        };

        $(document.body).off('click.leantony.modal').on('click.leantony.modal', $this.options.modalTriggerSelector, function (e) {
            e.preventDefault();
            clickHandler(this);
        });
    };

    /**
     * Render a bootstrap alert to the user. Requires the html to be inserted to the target element
     * @param type
     * @param message
     * @returns {string}
     */
    modal.prototype.renderAlert = function (type, message) {
        var validTypes = ['success', 'error', 'notice'], html = '';
        if (typeof type === 'undefined' || ($.inArray(type, validTypes) < 0)) {
            type = validTypes[0];
        }
        if (type === 'success') {
            html += '<div class="alert alert-success">';
        }
        else if (type === 'error') {
            html += '<div class="alert alert-danger">';
        } else {
            html += '<div class="alert alert-warning">';
        }
        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        // add a heading
        if (type === 'error') {
            html += "<strong>Please fix the following errors:</strong>";
        }
        message = this.processMessageObject(message);
        return html + message + '</div>';
    };

    /**
     * Laravel returns validation error messages as a json object
     * We process that to respective html here
     * @param message
     * @returns {string}
     */
    modal.prototype.processMessageObject = function (message) {
        var errors = '';
        // check if the msg was an object
        if ($.type(message) === "object") {
            $.each(message, function (key, value) {
                errors += '<li>' + value[0] + '</li>';
            });
        } else {
            errors += '<p>' + message + '</p>';
        }
        return errors;
    };

    /**
     * submit the modal form
     */
    modal.prototype.submitForm = function () {
        var $this = this;

        var submit_form = function (e) {
            var form = $('#' + $this.options.form_id);
            var data = form.serialize();
            var action = form.attr('action');
            var method = form.attr('method') || 'POST';
            var originalButtonHtml = $(e).html();
            var pjaxTarget = form.data('pjax-target');
            $.ajax({
                type: method,
                url: action,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var message = '<i class=\"fa fa-check\"></i> ';
                        message += response.message;
                        $('#' + $this.options.notification_id).html($this.renderAlert('success', message));
                        // if a redirect is required...
                        if (response.redirectTo) {
                            setTimeout(function () {
                                window.location = response.redirectTo;
                            }, response.redirectTimeout || 1000);
                        } else {
                            // hide the modal after 1000 ms
                            setTimeout(function () {
                                $('#' + $this.options.modal_id).modal('hide');
                                if (pjaxTarget) {
                                    // reload a pjax container
                                    $.pjax.reload({container: pjaxTarget})
                                }
                            }, 1000);
                        }
                    }
                    else {
                        // display message and hide modal
                        var el = $('#' + $this.options.notification_id);
                        el.html($this.renderAlert('error', response.message));
                        setTimeout(function () {
                            $('#' + $this.options.modal_id).modal('hide');
                        }, 1000);
                    }
                },
                beforeSend: function () {
                    $(e).attr('disabled', 'disabled').html('Please wait....');
                },
                complete: function () {
                    $(e).html(originalButtonHtml).removeAttr('disabled');
                },
                error: function (data) {
                    var msg;
                    console.log(data);
                    // error handling
                    switch (data.status) {
                        case 500:
                            msg = 'A server error occurred...';
                            break;
                        default:
                            msg = $this.renderAlert('error', data.responseJSON);
                            break;
                    }
                    // display errors
                    var el = $('#' + $this.options.notification_id);
                    el.html(msg);

                }
            });
        };

        $('#' + $this.options.modal_id).off("click.leantony.modal").on("click.leantony.modal", '#' + $this.options.form_id + ' button[type="submit"]', function (e) {
            e.preventDefault();
            submit_form(this);
        });
    };

    _modal = function (options) {
        var obj = new modal(options);
        obj.show();
        obj.submitForm();
    };
}(jQuery));