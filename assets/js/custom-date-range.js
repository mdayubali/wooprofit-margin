;(function ($) {

    'use strict'

    jQuery(document).ready(function($) {
        $('select').niceSelect();

        $('#date-range-select').on('change', function() {
            const selectedRange = $(this).val();
            const today = new Date();
            let startDate, endDate;

            switch (selectedRange) {
                case 'today':
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);
                    startDate = yesterday.toISOString().split('T')[0];
                    endDate = startDate;
                    break;
                case 'last-7-days':
                    const last7Days = new Date(today);
                    last7Days.setDate(today.getDate() - 7);
                    startDate = last7Days.toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    break;
                case 'last-14-days':
                    const last14Days = new Date(today);
                    last14Days.setDate(today.getDate() - 14);
                    startDate = last14Days.toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    break;
                case 'this-month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    break;
                    case 'last-month':
                    const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                    startDate = firstDayLastMonth.toISOString().split('T')[0];
                    endDate = lastDayLastMonth.toISOString().split('T')[0];
                    break;
                default:
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
            }

            console.log(`Start Date: ${startDate}, End Date: ${endDate}`); // Debugging

            $('#start_date').val(startDate);
            $('#end_date').val(endDate);
        });

        $('#start_date, #end_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $("#custom-date-range-form").submit(function(event) {
            event.preventDefault();
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            if (!startDate || !endDate) {
                alert('Please select a valid date range.');
                return;
            }

            $.ajax({
                url: ajax_params.ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_orders_by_date_range',
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {

                    const data = JSON.parse(response);

                    $('#orders-list').html(data.total_orders);
                    $('#total-sales').html(data.total_sales);
                    $('#net-sales').html(data.net_sales);
                    $('#total-cost').html(data.total_cost);
                    $('#average-order-value').html(data.average_order_value);
                    $('#profit').html(data.profit).attr('class', data.profit_class);
                    $('#average-profit').html(data.average_profit);
                    $('#average-order-profit').html(data.average_order_profit);
                    // $('#profit-percentage').html(data.profit_percentage);
                    $('#profit-percentage').html(`(<span id="profit-percentage">${data.profit_percentage}</span>)`);
                },
                error: function(error) {
                    console.log('Error:', error);
                }
            });
        });

        $('#date-range-select').val('today').change();
    });


})(jQuery);