jQuery(document).ready(function($) {
    function calculateProfit() {
        var cost = parseFloat($('#_woo_product_cost').val()) || 0;
        var price = parseFloat($('#_regular_price').val()) || 0;
        var profit = price - cost;
        var profitPercentage = (cost > 0) ? (profit / cost) * 100 : 0;

        $('#product_profit_display').text(
            'Profit: ' + profit.toFixed(2) + get_woocommerce_currency_symbol() + ' (' + profitPercentage.toFixed(2) + '%)'
        );
    }

    // Calculate profit on page load
    calculateProfit();

    // Recalculate profit when the cost or price field changes
    $('#_woo_product_cost, #_regular_price').on('input', function() {
        calculateProfit();
    });

    function get_woocommerce_currency_symbol() {
        return $('#_regular_price').closest('.woocommerce').find('.currency_symbol').text();
    }
});
