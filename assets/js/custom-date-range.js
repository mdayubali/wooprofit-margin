jQuery(document).ready(function($) {
    $('#start_date, #end_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('#custom-date-range-form').on('submit', function(e) {
        e.preventDefault();

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'get_orders_by_date_range',
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                //$('#orders-list').html(response);
                // alert('hello')
                const data = JSON.parse(response);
                $('#orders-list').html(data.total_orders);
                $('#total-sales').html(data.total_sales);
                $('#net-sales').html(data.net_sales);
                $('#average-order-value').html(data.average_order_value);
            }
        });
    });


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
            case 'custom':
                startDate = '';
                endDate = '';
                break;
            default:
                startDate = '';
                endDate = '';
        }

        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
    });
});
