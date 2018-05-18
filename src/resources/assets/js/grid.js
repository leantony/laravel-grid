/*
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

'use strict';
const _grids = _grids || {};

(($ => {

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

  (($ => {
    /**
     * Handle an ajax request from a button, form, link, etc
     *
     * @param element
     * @param event
     * @param options
     */
    _grids.utils.handleAjaxRequest = (element, event, options) => {
      event = event || 'click';
      if (element.length < 1) return;

      element.each((i, obj) => {
        obj = $(obj);
        // confirmation
        const confirmation = obj.data('trigger-confirm');
        const confirmationMessage = obj.data('confirmation-msg') || 'Are you sure?';
        const pjaxContainer = obj.data('pjax-target');
        const refresh = obj.data('refresh-page');
        const isForm = obj.is('form');

        obj.on(event, e => {
          e.preventDefault();
          if (confirmation) {
            if (!confirm(confirmationMessage)) {
              return;
            }
          }
          $.ajax({
            method: isForm ? obj.attr('method') : (obj.data('method') || 'POST'),
            url: isForm ? obj.attr('action') : obj.attr('href'),
            data: isForm ? obj.serialize() : null,
            beforeSend() {
              if(options.beforeSend) {
                options.beforeSend.call(this)
              }
            },
            complete() {
              if(options.onComplete) {
                options.onComplete.call(this)
              }
            },
            success(data) {
              if (pjaxContainer) {
                $.pjax.reload({container: pjaxContainer});
              }
            },
            error(data) {
              if (typeof toastr !== 'undefined') {
                toastr.error('An error occurred', 'Whoops!');
              } else {
                alert('An error occurred');
              }
            },
          });
        });
      });
    };

    /**
     * Linkable rows on tables (rows that can be clicked to navigate to a location)
     */
    _grids.utils.tableLinks = options => {
      if (!options) {
        console.warn('No options defined.');
      } else {
        const elements = $(options.element);
        elements.each((i, obj) => {
          const el = $(obj);
          const link = el.data('url');
          el.css({'cursor': 'pointer'});
          el.click(e => {
            setTimeout(() => {
              window.location = link;
            }, options.navigationDelay || 100);
          });
        });
      }
    };
  }))(jQuery);

  /**
   * The global grid object
   *
   * @type object
   * @public
   */
  _grids.grid = {};

  (($ => {
    /**
     * Initialization
     *
     * @param opts
     */
    class grid {
      constructor(opts) {

        const defaults = {
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
            afterPjax(e) {
            },
          },
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
      setupPjax(container, target, afterPjax, options) {
        // global timeout
        $.pjax.defaults.timeout = options.timeout || 3000;
        $(document).pjax(target, container, options);
        $(document).on('ready pjax:end', event => {
          afterPjax($(event.target));
          // internal calls
          setupDateRangePicker(this);
        });
      }

      /**
       * Initialize pjax functionality
       */
      bindPjax() {
        this.setupPjax(
            this.opts.id,
            'a[data-trigger-pjax=1]',
            this.opts.pjax.afterPjax,
            this.opts.pjax.pjaxOptions,
        );

        setupDateRangePicker(this);
      }

      /**
       * Pjax per row filter
       */
      filter() {
        const form = $(this.opts.filterForm);

        if (form.length > 0) {
          $(document).on('submit', this.opts.filterForm, event => {
            $.pjax.submit(event, this.opts.id, this.opts.pjax.pjaxOptions);
          });
        }
      }

      /**
       * Pjax search
       */
      search() {
        const form = $(this.opts.searchForm);

        if (form.length > 0) {
          $(document).on('submit', this.opts.searchForm, event => {
            $.pjax.submit(event, this.opts.id, this.opts.pjax.pjaxOptions);
          });
        }
      }
    }

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
          const start = moment().subtract(29, 'days');
          const end = moment();
          const element = $(instance.opts.dateRangeSelector);
          element.daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
              'Last 7 Days': [
                  moment().subtract(6, 'days'), moment()
              ],
              'Last 30 Days': [
                  moment().subtract(29, 'days'), moment()
              ],
              'This Month': [
                moment().startOf('month'), moment().endOf('month')
              ],
              'Last Month': [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
              ],
            },
            autoUpdateInput: false,
            locale: {
              format: 'YYYY-MM-DD',
              cancelLabel: 'Clear',
            },
          });

          element.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
          });

          element.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
          });
        }
      }
    }

    _grids.grid.init = options => {
      const obj = new grid(options);
      obj.bindPjax();
      obj.search();
      obj.filter();
    };
  }))(jQuery);

  _grids.formUtils = {
    /**
     * Return html that can be used to render a bootstrap alert on the form
     *
     * @param type
     * @param response
     * @returns {string}
     */
    renderAlert(type, response) {
      const validTypes = ['success', 'error', 'notice'];
      let html = '';
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
        if(response.serverError) {
          html += response.serverError.message || 'A server error occurred.';
          html = `<strong>${html}</strong>`;
          return html;
        } else {
          html += response.message || 'Please fix the following errors';
          html = `<strong>${html}</strong>`;
          const errs = this.getValidationErrors(response.errors || {});
          return `${html + errs}</div>`;
        }
      } else {
        return `${html + response}</div>`;
      }
    },

    /**
     * process validation errors from json to html
     * @param response
     * @returns {string}
     */
    getValidationErrors(response) {
      let errorsHtml = '';
      $.each(response, (key, value) => {
        errorsHtml += `<li>${value}</li>`;
      });
      return errorsHtml;
    },

    /**
     * Form submission from a modal dialog
     *
     * @param formId
     * @param modal
     */
    handleFormSubmission(formId, modal) {
      const form = $(`#${formId}`);
      const submitButton = form.find(':submit');
      const data = form.serialize();
      const action = form.attr('action');
      const method = form.attr('method') || 'POST';
      const originalButtonHtml = $(submitButton).html();
      const pjaxTarget = form.data('pjax-target');
      const notification = form.data('notification-el') || 'modal-notification';
      const _this = this;

      $.ajax({
        type: method,
        url: action,
        data,
        dataType: 'json',
        success(response) {
          if (response.success) {
            let message = '<i class=\"fa fa-check\"></i> ';
            message += response.message;
            $(`#${notification}`).html(_this.renderAlert('success', message));
            // if a redirect is required...
            if (response.redirectTo) {
              setTimeout(() => {
                window.location = response.redirectTo;
              }, response.redirectTimeout || 500);
            } else {
              // hide the modal after 1000 ms
              setTimeout(() => {
                modal.modal('hide');
                if (pjaxTarget) {
                  // reload a pjax container
                  $.pjax.reload({container: pjaxTarget});
                }
              }, 500);
            }
          }
          else {
            // display message and hide modal
            const el = $(notification);
            el.html(_this.renderAlert('error', response.message));
            setTimeout(() => {
              modal.modal('hide');
            }, 500);
          }
        },
        beforeSend() {
          $(submitButton).attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;loading');
        },
        complete() {
          $(submitButton).html(originalButtonHtml).removeAttr('disabled');
        },
        error(data) {
          let msg;
          // error handling
          switch (data.status) {
            case 500:
              msg = _this.renderAlert('error', {serverError: {message: "An error occurred on the server."}});
              break;
            default:
              msg = _this.renderAlert('error', data.responseJSON);
              break;
          }
          // display errors
          const el = $(`#${notification}`);
          el.html(msg);
        },
      });
    },
  };

  /**
   * The global modal object
   *
   * @type object
   * @public
   */
  _grids.modal = {};

  (($ => {
    class modal {
      constructor(options) {
        const defaultOptions = {};
        this.options = $.extend({}, defaultOptions, options || {});
      }

      /**
       * Show a modal dialog dynamically
       */
      show() {
        $('.show_modal_form').on('click', function(e) {
          e.preventDefault();
          const btn = $(this);
          const btnHtml = btn.html();
          const modalDialog = $('#bootstrap_modal');
          const modalSize = btn.data('modal-size');
          // show spinner as soon as user click is triggered
          btn.attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;loading');

          // load the modal into the container put on the html
          $('.modal-content').
              load($(this).attr('href') || $(this).data('href'), () => {
                // show the modal
                $('#bootstrap_modal').modal({ show: true });
                // alter size
                if (modalSize) {
                  $('.modal-content').parent('div').addClass(modalSize);
                }
              });

          // revert button to original content, once the modal is shown
          modalDialog.on('shown.bs.modal', e => {
            $(btn).html(btnHtml).removeAttr('disabled');
          });

          // destroy the modal
          modalDialog.on('hidden.bs.modal', function(e) {
            $(this).modal('dispose');
          });
        });
      }
    }

    $('#bootstrap_modal').
        on('click', '#' + 'modal_form' + ' button[type="submit"]', e => {
          e.preventDefault();
          // process forms on the modal
          _grids.formUtils.handleFormSubmission('modal_form', $('#bootstrap_modal'));
        });

    _grids.modal.init = options => {
      const obj = new modal(options);
      obj.show();
    };
  })(jQuery));

  /**
   * Initialize stuff
   */
  _grids.init = () => {
    // date picker
    if (typeof daterangepicker !== 'function') {
      console.warn('date picker option requires https://github.com/dangrossman/bootstrap-daterangepicker.git');
    } else {
      const element = $('.grid-datepicker');
      element.daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minYear: 1901,
        locale: {
          format: 'YYYY-MM-DD',
        },
      });

      element.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
      });

      element.on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
      });
    }
    // initialize modal js
    _grids.modal.init({});
    // table links
    _grids.utils.tableLinks({element: '.linkable', navigationDelay: 100});
    // setup ajax listeners
    _grids.utils.handleAjaxRequest($('.data-remote'), 'click', {});
  };

  return _grids;
}))(jQuery);

_grids.init();
