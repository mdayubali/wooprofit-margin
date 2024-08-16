;(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {


        // Helper function to format dates as DD-MM-YYYY
        function formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const year = date.getFullYear();
            return `${year}-${month}-${day}`;
        }

        $('select').niceSelect();

        // Event listener for date range selection
        $('#date-range-select').on('change', function () {
            const selectedRange = $(this).val();
            const today = new Date();
            let startDate, endDate;

            switch (selectedRange) {
                case 'today':
                    startDate = new Date(today);
                    endDate = startDate;
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);
                    startDate = yesterday;
                    endDate = startDate;
                    break;
                case 'last-7-days':
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 7);
                    endDate = new Date(today);
                    break;
                case 'last-14-days':
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 14);
                    endDate = new Date(today);
                    break;
                case 'this-month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today);
                    break;
                case 'last-month':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                default:
                    startDate = new Date(today);
                    endDate = startDate;
            }

            // Set the start and end date in the input fields
            $('#start_date').val(formatDate(startDate));
            $('#end_date').val(formatDate(endDate));


            // Call the loadData function to update the data
            loadData();
        });

        // Initialize the date pickers

        function loadData() {
            const start_date = $('#start_date').val();
            const end_date = $('#end_date').val();
            const date_range = $('#date-range-select').val();

            $.ajax({
                url: ajax_params.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wooprofit_get_orders_by_date_range',
                    start_date: start_date,
                    end_date: end_date,
                    date_range: date_range
                },
                success: function (response) {
                    const data = JSON.parse(response);

                    $('#orders-list').html(data.total_orders || 0);
                    $('#total-sales').html(data.total_sales || 0);
                    $('#net-sales').html(data.net_sales || 0);
                    $('#total-cost').html(data.total_cost || 0);
                    $('#profit').html(data.profit).attr('class', data.profit_class || '');
                    $('#average-profit').html(data.average_profit || 0);
                    $('#average-order-profit').html(data.average_order_profit || 0);
                    $('#profit-percentage').html(`(<span id="profit-percentage">${data.profit_percentage || 0}</span>)`);

                },
                error: function (error) {
                    console.log('Error:', error);
                }
            });
        }

/*
        $('#start_date, #end_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });*/


        // Function to calculate the end date of the previous range based on the selected start and end dates
        function calculatePreviousRange(startDate, endDate, type) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            let prevStartDate, prevEndDate;
            const duration = (end - start) / (1000 * 60 * 60 * 24) + 1; // Calculate duration in days

            switch (type) {
                case 'month':
                    const prevMonthStart = new Date(start.getFullYear(), start.getMonth() - 1, start.getDate());
                    const prevMonthEnd = new Date(prevMonthStart.getFullYear(), prevMonthStart.getMonth() + 1, prevMonthStart.getDate() - 1);
                    prevStartDate = new Date(prevMonthStart);
                    prevEndDate = new Date(prevStartDate);
                    prevEndDate.setDate(prevStartDate.getDate() + duration - 1);
                    if (prevEndDate > prevMonthEnd) prevEndDate = prevMonthEnd; // Ensure end date does not exceed the last day of the month
                    break;
                case 'period':
                    const prevPeriodStart = new Date(start.getFullYear(), start.getMonth() - 2, start.getDate());
                    const prevPeriodEnd = new Date(prevPeriodStart.getFullYear(), prevPeriodStart.getMonth() + 1, prevPeriodStart.getDate() - 1);
                    prevStartDate = new Date(prevPeriodStart);
                    prevEndDate = new Date(prevStartDate);
                    prevEndDate.setDate(prevStartDate.getDate() + duration - 1);
                    if (prevEndDate > prevPeriodEnd) prevEndDate = prevPeriodEnd; // Ensure end date does not exceed the last day of the period
                    break;
                case 'quarter':
                    const currentQuarter = Math.floor((start.getMonth() + 3) / 3);
                    const prevQuarterStart = new Date(start.getFullYear(), (currentQuarter - 2) * 3, start.getDate());
                    const prevQuarterEnd = new Date(prevQuarterStart.getFullYear(), (currentQuarter - 1) * 3, prevQuarterStart.getDate() - 1);
                    prevStartDate = new Date(prevQuarterStart);
                    prevEndDate = new Date(prevStartDate);
                    prevEndDate.setDate(prevStartDate.getDate() + duration - 1);
                    if (prevEndDate > prevQuarterEnd) prevEndDate = prevQuarterEnd; // Ensure end date does not exceed the last day of the quarter
                    break;
                default:
                    prevStartDate = '';
                    prevEndDate = '';
            }

            return {
                startDate: formatDate(prevStartDate),
                endDate: formatDate(prevEndDate)
            };
        }



// Function to update the date range fields based on the selected comparison option
        function updateDateRangeFields() {
            var $select = $('#previous-date-range-select');
            var selectedOption = $select.find('option:selected');
            var optionValue = selectedOption.val();
            var optionText = selectedOption.text().trim();
            var smallText = '';

            // Extract the start and end dates from the input fields
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            // Default start and end date for comparison
            var prevStartDate = '';
            var prevEndDate = '';

            // Calculate the previous date ranges based on the selected option
            if (optionValue === 'previous-month') {
                var dates = calculatePreviousRange(startDate, endDate, 'month');
                prevStartDate = dates.startDate;
                prevEndDate = dates.endDate;

                smallText = `From ${prevStartDate} to ${prevEndDate}`;
            } else if (optionValue === 'previous-period') {
                var dates = calculatePreviousRange(startDate, endDate, 'period');
                prevStartDate = dates.startDate;
                prevEndDate = dates.endDate;
               smallText = `From ${prevStartDate} to ${prevEndDate}`;
            } else if (optionValue === 'previous-quarter') {
                var dates = calculatePreviousRange(startDate, endDate, 'quarter');
                prevStartDate = dates.startDate;
                prevEndDate = dates.endDate;
                smallText = `From ${prevStartDate} to ${prevEndDate}`;
            }

            // Update the input fields with the extracted dates
            $('#prev_start_date').val(prevStartDate);
            $('#prev_end_date').val(prevEndDate);

            // Update the Nice Select current span with the formatted date range
            $('.nice-select').each(function () {
                var $current = $(this).find('.current');
                if ($current.closest('#previous-date-range-form').length) {
                    if (optionValue === 'no-comparison') {
                        $current.text(optionText); // Remove the smallText part for "No comparison"
                    } else {
                        $current.text(`${optionText} (${smallText})`);
                    }
                }
            });

            // Update the <small> tags within the dropdown options
            $select.find('.option').each(function () {
                var value = $(this).data('value');
                var $smallTag = $(this).find('small');

                if (value === 'previous-month' || value === 'previous-period' || value === 'previous-quarter') {
                    $smallTag.text(smallText);
                } else {
                    $smallTag.text('');
                }
            });
        }


        $('#previous-date-range-select').on('change', updateDateRangeFields);
        updateDateRangeFields();

        $("#custom-date-range-form").submit(function (event) {
            event.preventDefault();
            loadData();
        });

        $('#start_date').change(loadData);
        $('#end_date').change(loadData);

        $('#date-range-select').val('today').change();

        $('select.nice-select').niceSelect();

        $('#custom-date-range').hide();

        // onload badge class will be hide
        $('.badge').hide();
        $('[id^=pre-]').hide();

        $('#previous-date-range-select').on('change', function () {
            updateDateRangeFields();
            const selectedValue = $(this).val();
            const selectedOption = $(this).find('option:selected');

            if (selectedValue === 'no-comparison') {
                $('.badge').hide();
                $('[id^=pre-]').hide();
                $('#custom-date-range').hide();
                $('#prev_start_date').val('');
                $('#prev_end_date').val('');
            } else if (selectedValue === 'previous-month' || selectedValue === 'previous-period' || selectedValue === 'previous-quarter') {
                $('#custom-date-range').hide();
                const smallElement = selectedOption.find('small');

                if (smallElement.length > 0) {
                    const dateText = smallElement.text().split(' to ');
                    $('#prev_start_date').val(dateText[0].trim());
                    $('#prev_end_date').val(dateText[1].trim());
                }

                $('.badge').show();
                $('[id^=pre-]').show();
                compareData();
            } else {
                $('#custom-date-range').show();
            }
        });

        $('#prev_start_date, #prev_end_date').datepicker({
            // dateFormat: 'yy-mm-dd',
            onSelect: function () {
                const startDate = $('#prev_start_date').val();
                const endDate = $('#prev_end_date').val();
                if (startDate && endDate) {
                    $('.badge').show();
                    $('[id^=pre-]').show();
                    compareData();
                }
            }
        });

        function updateData(previousMonth, currencySymbol) {
            if (typeof previousMonth !== 'object' || previousMonth === null) {
                return;
            }

            const validateNumber = (num) => typeof num === 'number' && !isNaN(num);

            jQuery("#pre-orders-list").html(validateNumber(previousMonth.total_orders) ? previousMonth.total_orders : 0);
            jQuery("#pre-total-sales").html(validateNumber(previousMonth.total_sales) ? `${currencySymbol}${previousMonth.total_sales.toFixed(2)}` : `${currencySymbol}0.00`);
            jQuery("#pre-total-cost").html(validateNumber(previousMonth.total_cost) ? `${currencySymbol}${previousMonth.total_cost.toFixed(2)}` : `${currencySymbol}0.00`);
            jQuery("#pre-average-order-profit").html(validateNumber(previousMonth.average_order_profit) ? `${currencySymbol}${previousMonth.average_order_profit.toFixed(2)}` : `${currencySymbol}0.00`);
            jQuery("#pre-profit").html(validateNumber(previousMonth.total_profit) ? `${currencySymbol}${previousMonth.total_profit.toFixed(2)}` : `${currencySymbol}0.00`);
            jQuery("#pre-average-daily-profit").html(validateNumber(previousMonth.average_daily_profit) ? `${currencySymbol}${previousMonth.average_daily_profit.toFixed(2)}` : `${currencySymbol}0.00`);
        }

        function updatePercentageChanges(response) {
            const setPercentageChange = (selector, value) => {
                const element = jQuery(selector);

                if (typeof value !== 'number' || isNaN(value)) {
                    element.html('N/A');
                    return;
                }

                let formattedValue = value > 0 ? `+${value.toFixed(1)}%` : `${value.toFixed(1)}%`;

                if (value < 0) {
                    element.css('background-color', '#E68E00');
                } else if (value === 0) {
                    element.css('background-color', '#50809f');
                    formattedValue = '0.0%';
                } else {
                    element.css('background-color', '');
                }

                element.html(formattedValue);
            };

            setPercentageChange("#order-percentage-change", response.order_percentage_change);
            setPercentageChange("#total-sales-percentage-change", response.total_sales_percentage_change);
            setPercentageChange("#total-cost-percentage-change", response.total_cost_percentage_change);
            setPercentageChange("#average-daily-profit-percentage-change", response.average_daily_profit_percentage_change);
            setPercentageChange("#average-order-profit-percentage-change", response.average_order_profit_percentage_change);
            setPercentageChange("#total-profit-percentage-change", response.total_profit_percentage_change);
        }

        function compareData() {
            const currentStartDate = $('#start_date').val();
            const currentEndDate = $('#end_date').val();
            const prevStartDate = $('#prev_start_date').val();
            const prevEndDate = $('#prev_end_date').val();

            if (!currentStartDate || !currentEndDate || !prevStartDate || !prevEndDate) {
                console.error('Please select valid date ranges.');
                return;
            }

            $.ajax({
                url: ajax_params.ajaxurl,
                method: "POST",
                data: {
                    action: "wooprofit_compare_monthly_orders",
                    nonce: ajax_params.nonce,
                    current_start_date: currentStartDate,
                    current_end_date: currentEndDate,
                    prev_start_date: prevStartDate,
                    prev_end_date: prevEndDate
                },
                success: function (response) {
                    try {
                        if (response && response.success) {
                            const previousMonth = response.data.previous_month;
                            const currencySymbol = response.data.currency_symbol || '$';
                            updateData(previousMonth, currencySymbol);
                            updatePercentageChanges(response.data);
                        } else {
                            console.error('Failed to fetch comparison data:', response);
                            handleEmptyResponse('$');
                        }
                    } catch (error) {
                        console.error('Error parsing response:', error);
                        handleEmptyResponse('$');
                    }
                },
                error: function (error) {
                    console.error('Error:', error);
                    handleEmptyResponse('$');
                }
            });
        }

        function handleEmptyResponse(currencySymbol) {
            jQuery("#pre-orders-list").html(0);
            jQuery("#pre-total-sales").html(`${currencySymbol}0.00`);
            jQuery("#pre-total-cost").html(`${currencySymbol}0.00`);
            jQuery("#pre-average-order-profit").html(`${currencySymbol}0.00`);
            jQuery("#pre-profit").html(`${currencySymbol}0.00`);
            jQuery("#pre-average-daily-profit").html(`${currencySymbol}0.00`);
            updatePercentageChanges({
                order_percentage_change: 0,
                total_sales_percentage_change: 0,
                total_cost_percentage_change: 0,
                average_daily_profit_percentage_change: 0,
                average_order_profit_percentage_change: 0,
                total_profit_percentage_change: 0
            });
        }

        compareData();
        $('#date-range-select, #previous-date-range-select').on('change', compareData);
    });
})(jQuery);
