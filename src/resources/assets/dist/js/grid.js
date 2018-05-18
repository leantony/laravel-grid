/*
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var _grids = _grids || {};

(function ($) {

  if (typeof $ === 'undefined') {
    throw new Error('Requires jQuery');
  }

  /**
   * Shared utilities
   *
   * @type object
   * @public
   */
  _grids.utils = {};

  (function ($) {
    /**
     * Handle an ajax request from a button, form, link, etc
     *
     * @param element
     * @param event
     * @param options
     */
    _grids.utils.handleAjaxRequest = function (element, event, options) {
      event = event || 'click';
      if (element.length < 1) return;

      element.each(function (i, obj) {
        obj = $(obj);
        // confirmation
        var confirmation = obj.data('trigger-confirm');
        var confirmationMessage = obj.data('confirmation-msg') || 'Are you sure?';
        var pjaxContainer = obj.data('pjax-target');
        var refresh = obj.data('refresh-page');
        var isForm = obj.is('form');

        obj.on(event, function (e) {
          e.preventDefault();
          if (confirmation) {
            if (!confirm(confirmationMessage)) {
              return;
            }
          }
          $.ajax({
            method: isForm ? obj.attr('method') : obj.data('method') || 'POST',
            url: isForm ? obj.attr('action') : obj.attr('href'),
            data: isForm ? obj.serialize() : null,
            beforeSend: function beforeSend() {
              if (options.beforeSend) {
                options.beforeSend.call(this);
              }
            },
            complete: function complete() {
              if (options.onComplete) {
                options.onComplete.call(this);
              }
            },
            success: function success(data) {
              if (pjaxContainer) {
                $.pjax.reload({ container: pjaxContainer });
              }
            },
            error: function error(data) {
              if (typeof toastr !== 'undefined') {
                toastr.error('An error occurred', 'Whoops!');
              } else {
                alert('An error occurred');
              }
            }
          });
        });
      });
    };

    /**
     * Linkable rows on tables (rows that can be clicked to navigate to a location)
     */
    _grids.utils.tableLinks = function (options) {
      if (!options) {
        console.warn('No options defined.');
      } else {
        var elements = $(options.element);
        elements.each(function (i, obj) {
          var el = $(obj);
          var link = el.data('url');
          el.css({ 'cursor': 'pointer' });
          el.click(function (e) {
            setTimeout(function () {
              window.location = link;
            }, options.navigationDelay || 100);
          });
        });
      }
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
    /**
     * Initialization
     *
     * @param opts
     */
    var grid = function () {
      function grid(opts) {
        _classCallCheck(this, grid);

        var defaults = {
          /**
           * The ID of the html element containing the grid
           */
          id: '#some-grid',
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
            afterPjax: function afterPjax(e) {}
          }
        };
        this.opts = $.extend({}, defaults, opts || {});
      }

      /**
       * Enable pjax
       *
       * @param container the root element for which html contents shall be replaced
       * @param target the element in the root element that will trigger the pjax request
       * @param afterPjax a function that will be executed after the pjax request is done
       * @param options
       */


      _createClass(grid, [{
        key: 'setupPjax',
        value: function setupPjax(container, target, afterPjax, options) {
          var _this2 = this;

          // global timeout
          $.pjax.defaults.timeout = options.timeout || 3000;
          $(document).pjax(target, container, options);
          $(document).on('ready pjax:end', function (event) {
            afterPjax($(event.target));
            // internal calls
            setupDateRangePicker(_this2);
          });
        }

        /**
         * Initialize pjax functionality
         */

      }, {
        key: 'bindPjax',
        value: function bindPjax() {
          this.setupPjax(this.opts.id, 'a[data-trigger-pjax=1]', this.opts.pjax.afterPjax, this.opts.pjax.pjaxOptions);

          setupDateRangePicker(this);
        }

        /**
         * Pjax per row filter
         */

      }, {
        key: 'filter',
        value: function filter() {
          var _this3 = this;

          var form = $(this.opts.filterForm);

          if (form.length > 0) {
            $(document).on('submit', this.opts.filterForm, function (event) {
              $.pjax.submit(event, _this3.opts.id, _this3.opts.pjax.pjaxOptions);
            });
          }
        }

        /**
         * Pjax search
         */

      }, {
        key: 'search',
        value: function search() {
          var _this4 = this;

          var form = $(this.opts.searchForm);

          if (form.length > 0) {
            $(document).on('submit', this.opts.searchForm, function (event) {
              $.pjax.submit(event, _this4.opts.id, _this4.opts.pjax.pjaxOptions);
            });
          }
        }
      }]);

      return grid;
    }();

    /**
     * Setup date range picker
     *
     * @param instance
     */


    function setupDateRangePicker(instance) {
      if (instance.opts.dateRangeSelector) {
        if (typeof daterangepicker !== 'function') {
          console.warn('date range picker option requires https://github.com/dangrossman/bootstrap-daterangepicker.git');
        } else {
          var start = moment().subtract(29, 'days');
          var end = moment();
          var element = $(instance.opts.dateRangeSelector);
          element.daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
              'Last 7 Days': [moment().subtract(6, 'days'), moment()],
              'Last 30 Days': [moment().subtract(29, 'days'), moment()],
              'This Month': [moment().startOf('month'), moment().endOf('month')],
              'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            autoUpdateInput: false,
            locale: {
              format: 'YYYY-MM-DD',
              cancelLabel: 'Clear'
            }
          });

          element.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
          });

          element.on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
          });
        }
      }
    }

    _grids.grid.init = function (options) {
      var obj = new grid(options);
      obj.bindPjax();
      obj.search();
      obj.filter();
    };
  })(jQuery);

  _grids.formUtils = {
    /**
     * Return html that can be used to render a bootstrap alert on the form
     *
     * @param type
     * @param response
     * @returns {string}
     */
    renderAlert: function renderAlert(type, response) {
      var validTypes = ['success', 'error', 'notice'];
      var html = '';
      if (typeof type === 'undefined' || $.inArray(type, validTypes) < 0) {
        type = validTypes[0];
      }
      if (type === 'success') {
        html += '<div class="alert alert-success">';
      } else if (type === 'error') {
        html += '<div class="alert alert-danger">';
      } else {
        html += '<div class="alert alert-warning">';
      }
      html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
      // add a heading
      if (type === 'error') {
        if (response.serverError) {
          html += response.serverError.message || 'A server error occurred.';
          html = '<strong>' + html + '</strong>';
          return html;
        } else {
          html += response.message || 'Please fix the following errors';
          html = '<strong>' + html + '</strong>';
          var errs = this.getValidationErrors(response.errors || {});
          return html + errs + '</div>';
        }
      } else {
        return html + response + '</div>';
      }
    },


    /**
     * process validation errors from json to html
     * @param response
     * @returns {string}
     */
    getValidationErrors: function getValidationErrors(response) {
      var errorsHtml = '';
      $.each(response, function (key, value) {
        errorsHtml += '<li>' + value + '</li>';
      });
      return errorsHtml;
    },


    /**
     * Form submission from a modal dialog
     *
     * @param formId
     * @param modal
     */
    handleFormSubmission: function handleFormSubmission(formId, modal) {
      var form = $('#' + formId);
      var submitButton = form.find(':submit');
      var data = form.serialize();
      var action = form.attr('action');
      var method = form.attr('method') || 'POST';
      var originalButtonHtml = $(submitButton).html();
      var pjaxTarget = form.data('pjax-target');
      var notification = form.data('notification-el') || 'modal-notification';
      var _this = this;

      $.ajax({
        type: method,
        url: action,
        data: data,
        dataType: 'json',
        success: function success(response) {
          if (response.success) {
            var message = '<i class=\"fa fa-check\"></i> ';
            message += response.message;
            $('#' + notification).html(_this.renderAlert('success', message));
            // if a redirect is required...
            if (response.redirectTo) {
              setTimeout(function () {
                window.location = response.redirectTo;
              }, response.redirectTimeout || 500);
            } else {
              // hide the modal after 1000 ms
              setTimeout(function () {
                modal.modal('hide');
                if (pjaxTarget) {
                  // reload a pjax container
                  $.pjax.reload({ container: pjaxTarget });
                }
              }, 500);
            }
          } else {
            // display message and hide modal
            var el = $(notification);
            el.html(_this.renderAlert('error', response.message));
            setTimeout(function () {
              modal.modal('hide');
            }, 500);
          }
        },
        beforeSend: function beforeSend() {
          $(submitButton).attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;loading');
        },
        complete: function complete() {
          $(submitButton).html(originalButtonHtml).removeAttr('disabled');
        },
        error: function error(data) {
          var msg = void 0;
          // error handling
          switch (data.status) {
            case 500:
              msg = _this.renderAlert('error', { serverError: { message: "An error occurred on the server." } });
              break;
            default:
              msg = _this.renderAlert('error', data.responseJSON);
              break;
          }
          // display errors
          var el = $('#' + notification);
          el.html(msg);
        }
      });
    }
  };

  /**
   * The global modal object
   *
   * @type object
   * @public
   */
  _grids.modal = {};

  (function ($) {
    var modal = function () {
      function modal(options) {
        _classCallCheck(this, modal);

        var defaultOptions = {};
        this.options = $.extend({}, defaultOptions, options || {});
      }

      /**
       * Show a modal dialog dynamically
       */


      _createClass(modal, [{
        key: 'show',
        value: function show() {
          $('.show_modal_form').on('click', function (e) {
            e.preventDefault();
            var btn = $(this);
            var btnHtml = btn.html();
            var modalDialog = $('#bootstrap_modal');
            var modalSize = btn.data('modal-size');
            // show spinner as soon as user click is triggered
            btn.attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;loading');

            // load the modal into the container put on the html
            $('.modal-content').load($(this).attr('href') || $(this).data('href'), function () {
              // show the modal
              $('#bootstrap_modal').modal({ show: true });
              // alter size
              if (modalSize) {
                $('.modal-content').parent('div').addClass(modalSize);
              }
            });

            // revert button to original content, once the modal is shown
            modalDialog.on('shown.bs.modal', function (e) {
              $(btn).html(btnHtml).removeAttr('disabled');
            });

            // destroy the modal
            modalDialog.on('hidden.bs.modal', function (e) {
              $(this).modal('dispose');
            });
          });
        }
      }]);

      return modal;
    }();

    $('#bootstrap_modal').on('click', '#' + 'modal_form' + ' button[type="submit"]', function (e) {
      e.preventDefault();
      // process forms on the modal
      _grids.formUtils.handleFormSubmission('modal_form', $('#bootstrap_modal'));
    });

    _grids.modal.init = function (options) {
      var obj = new modal(options);
      obj.show();
    };
  })(jQuery);

  /**
   * Initialize stuff
   */
  _grids.init = function () {
    // date picker
    if (typeof daterangepicker !== 'function') {
      console.warn('date picker option requires https://github.com/dangrossman/bootstrap-daterangepicker.git');
    } else {
      var element = $('.grid-datepicker');
      element.daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minYear: 1901,
        locale: {
          format: 'YYYY-MM-DD'
        }
      });

      element.on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
      });

      element.on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
      });
    }
    // initialize modal js
    _grids.modal.init({});
    // table links
    _grids.utils.tableLinks({ element: '.linkable', navigationDelay: 100 });
    // setup ajax listeners
    _grids.utils.handleAjaxRequest($('.data-remote'), 'click', {});
  };

  return _grids;
})(jQuery);

_grids.init();

//# sourceMappingURL=grid.js.map