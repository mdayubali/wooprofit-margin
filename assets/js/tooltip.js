document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('.tooltip');

    tooltips.forEach(tooltip => {
        const tooltipText = document.createElement('span');
        tooltipText.classList.add('tooltiptext');
        tooltipText.textContent = tooltip.getAttribute('data-tooltip');
        tooltip.appendChild(tooltipText);

        tooltip.addEventListener('click', function() {
            tooltip.classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            if (!tooltip.contains(event.target)) {
                tooltip.classList.remove('active');
            }
        });
    });
});
