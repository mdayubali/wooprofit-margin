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
});
