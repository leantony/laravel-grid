var _grids = _grids || {};

(function ($) {

    if (typeof $ === 'undefined') {
        throw new Error("Requires jQuery")
    }

    /**
     * Shared utilities
     *
     * @type object
     * @public
     */
    _grids.utils = {};

    (function ($) {

        "use strict";

        /**
         * Execute an ajax request from a button, form, link, etc
         *
         * @param element
         * @param event
         */
        _grids.utils.executeAjaxRequest = function (element, event) {
            // click or submit
            event = event || 'click';

            // do not do anything if we have nothing to work with
            if (element.length < 1) return;

            // since our refs are data-remote or class with data-remote, we need to loop
            element.each(function (i, obj) {
                obj = $(obj);
                var confirmation = obj.data('confirm');
                // check if we need to refresh any pjax container
                var pjaxContainer = obj.data('pjax-target');
                // check if we need to force a page refresh. will override shouldPjax
                var refresh = obj.data('refresh-page');
                // a form
                var isForm = obj.is('form');
                // prevent or enable blocking of UI
                var blockUi = obj.data('block-ui') || true;
                // custom block UI msg
                var waitingMsg = obj.data('waiting-message');

                obj.on(event, function (e) {
                    e.preventDefault();
                    // check for a confirmation message
                    if (confirmation) {
                        if (!confirm(confirmation)) {
                            return;
                        }
                    }
                    $.ajax({
                        method: isForm ? obj.attr('method') : (obj.data('method') || 'POST'),
                        url: isForm ? obj.attr('action') : obj.attr('href'),
                        data: isForm ? obj.serialize() : null,
                        beforeSend: function () {
                            if (blockUi) {
                                _grids.utils.blockUI(waitingMsg || 'Please wait ...')
                            }
                        },
                        complete: function () {
                            if (blockUi) {
                                _grids.utils.unBlockUI();
                            }
                        },
                        success: function (data) {
                            // reload a pjax container
                            if (pjaxContainer) {
                                $.pjax.reload({container: pjaxContainer});
                            }
                        },
                        error: function (data) {
                            // handle errors gracefully
                            if (typeof toastr !== 'undefined') {
                                toastr.error("An error occurred", "Whoops!");
                            }
                            console.error("An error occurred");
                        }
                    });
                });
            });
        };

        /**
         * Block UI. call this at the start of an ajax request
         * @param message
         */
        _grids.utils.blockUI = function (message) {
            if (typeof message === 'undefined') {
                message = 'Please wait ...';
            }
            var content = '<span id="bui">' + message + '</span>';
            $.blockUI({
                message: content,
                css: {
                    border: 'none', padding: '15px',
                    backgroundColor: '#333C44',
                    '-webkit-border-radius': '3px',
                    '-moz-border-radius': '3px',
                    opacity: 1, color: '#fff'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.4,
                    cursor: 'wait',
                    'z-index': 1030
                }
            });
        };

        /**
         * Unblock UI
         */
        _grids.utils.unBlockUI = function () {
            $.unblockUI();
        };

        /**
         * Linkable rows on tables
         */
        _grids.utils.tableLinks = function (options) {
            if (!options) {
                console.warn('No options defined.');
                return;
            }
            var elements = $(options.element);
            elements.each(function (i, obj) {
                var el = $(obj);
                var link = el.data('url');
                el.css({ 'cursor': "pointer" });
                el.click(function (e) {
                    setTimeout(function () {
                        window.location = link;
                    }, options.navigationDelay || 100);
                });
            });
        };

    })(jQuery);

    /**
     * The global grid object
     *
     * @type object
     * @public
     */
    _grids.grid = {};

    (function ($) {

        "use strict";

        /**
         * Initialization
         *
         * @param opts
         */
        var grid = function (opts) {

            var defaults = {
                /**
                 * The ID of the html element containing the grid
                 */
                id: '#grid-_grids',
                /**
                 * The ID of the html element containing the filter form
                 */
                filterForm: undefined,
                /**
                 * The ID of the html element containing the search form
                 */
                searchForm: undefined,
                /**
                 * The CSS class of the columns that are sortable
                 */
                sortLinks: 'data-sort',
                /**
                 * The selector of a date range filter
                 */
                dateRangeSelector: '.date-range',
                /**
                 * PJAX
                 */
                pjax: {
                    /**
                     * Any extra pjax plugin options
                     */
                    pjaxOptions: {},

                    /**
                     * Something to do once the PJAX request has been finished
                     */
                    afterPjax: function (e) {}
                }
            };
            this.opts = $.extend({}, defaults, opts || {});
        };

        /**
         * Enable pjax
         *
         * @param container the root element for which html contents shall be replaced
         * @param target the element in the root element that will trigger the pjax request
         * @param afterPjax a function that will be executed after the pjax request is done
         * @param options
         */
        grid.prototype.setupPjax = function (container, target, afterPjax, options) {
            // global timeout
            $.pjax.defaults.timeout = options.timeout || 3000;
            $(document).pjax(target, container, options);
            $(document).on('ready pjax:end', function (event) {
                afterPjax($(event.target));
            })
        };

        /**
         * Initialize pjax functionality
         */
        grid.prototype.bindPjax = function () {
            var $this = this;
            this.setupPjax(
                $this.opts.id,
                'a[data-trigger-pjax=1]',
                $this.opts.pjax.afterPjax,
                $this.opts.pjax.pjaxOptions
            );

            if ($this.opts.dateRangeSelector && typeof moment === 'function') {
                var start = moment().subtract(29, 'days');
                var end = moment();

                $($this.opts.dateRangeSelector).daterangepicker({startDate: start, endDate: end});
            }
        };

        /**
         * Pjax per row filter
         */
        grid.prototype.filter = function () {
            var $this = this;
            var form = $($this.opts.filterForm);

            if (form.length > 0) {
                $(document).on('submit', $this.opts.filterForm, function (event) {
                    $.pjax.submit(event, $this.opts.id, $this.opts.pjax.pjaxOptions)
                });
            }
        };

        /**
         * Pjax search
         */
        grid.prototype.search = function () {
            var $this = this;
            var form = $($this.opts.searchForm);

            if (form.length > 0) {
                $(document).on('submit', $this.opts.searchForm, function (event) {
                    $.pjax.submit(event, $this.opts.id, $this.opts.pjax.pjaxOptions)
                });
            }
        };

        _grids.grid.init = function (options) {
            var obj = new grid(options);
            obj.bindPjax();
            obj.search();
            obj.filter();
        };
    })(jQuery);

    /**
     * The global modal object
     *
     * @type object
     * @public
     */
    _grids.modal = {};

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

            $(document.body).off('click.bs.modal').on('click.bs.modal', $this.options.modalTriggerSelector, function (e) {
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

            $('#' + $this.options.modal_id).off("click.bs.modal").on("click.bs.modal", '#' + $this.options.form_id + ' button[type="submit"]', function (e) {
                e.preventDefault();
                submit_form(this);
            });
        };

        _grids.modal.init = function (options) {
            var obj = new modal(options);
            obj.show();
            obj.submitForm();
        };
    }(jQuery));

    /**
     * Initialize stuff
     */
    _grids.init = function () {
        // tooltip
        $('[data-toggle="tooltip"]').tooltip();
        // initialize modal js
        _grids.modal.init({});
        // table links
        _grids.utils.tableLinks({element: '.linkable', navigationDelay: 100});
        // setup ajax listeners
        _grids.utils.executeAjaxRequest($('.data-remote'), 'click');
        _grids.utils.executeAjaxRequest($('form[data-remote]'), 'submit');
    };

    return _grids;
})(jQuery);
