var _grid = {};

(function ($) {

    "use strict";

    var grid = function (opts) {
        var defaults = {
            id: '#grid-leantony',
            filterForm: undefined,
            searchForm: undefined,
            sortLinks: 'data-sort',
            dateRangeSelector: '.date-range',
            linkables: {
                element: '.linkable',
                url: 'url',
                timeout: 100
            },
            pjax: {
                pjaxOptions: {}
            }
        };
        this.opts = $.extend({}, defaults, opts || {});
    };

    /**
     * Block UI. call this at the start of an ajax request
     * @param message
     */
    grid.prototype.startBlockUI = function (message) {
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
    grid.prototype.stopBlockUI = function () {
        $.unblockUI();
    };

    /**
     * send an ajax request quickly via a link, etc
     *
     * @param element
     * @param event
     */
    grid.prototype.executeAjaxRequest = function (element, event) {
        // click or submit
        event = event || 'click';
        var $this = this;

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

            // console.log([event, isForm, obj.attr('action')]);
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
                            $this.startBlockUI(waitingMsg || 'Please wait ...')
                        }
                    },
                    complete: function () {
                        if (blockUi) {
                            $this.stopBlockUI();
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
     * Linkable rows
     */
    grid.prototype.tableLinks = function () {
        var options = this.opts.linkables;
        var elements = $(options.element);
        elements.each(function (i, obj) {
            var link = $(obj).data(options.url);
            $(obj).click(function (e) {
                setTimeout(function () {
                    window.location = link;
                }, options.delay || 100);
            });
        });
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
        $(document).pjax(target, container, options).on('pjax:end', afterPjax());
    };

    /**
     * Initialize pjax functionality
     */
    grid.prototype.bindPjax = function () {
        var $this = this;
        console.log($this.opts.pjax);
        this.setupPjax(
            $this.opts.id,
            'a[data-trigger-pjax=1]',
            function() {
                $.pjax.reload({container: $this.opts.id})
            },
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
            $(document).on('submit', $this.opts.filterForm, function(event) {
                $.pjax.submit(event, $this.opts.id)
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
            $(document).on('submit', $this.opts.searchForm, function(event) {
                $.pjax.submit(event, $this.opts.id)
            });
        }
    };

    _grid = function (options) {
        var obj = new grid(options);
        obj.bindPjax();
        obj.tableLinks();
        obj.search();
        obj.filter();
    };
})(jQuery);