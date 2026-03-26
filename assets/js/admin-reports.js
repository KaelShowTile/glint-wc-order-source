jQuery(document).ready(function($) {
    var reportsChart = null;

    $('#report_type').on('change', function() {
        if ($(this).val() === 'location') {
            $('#location_by_container').show();
        } else {
            $('#location_by_container').hide();
        }
    });

    $('#glint-reports-form').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $('#glint-generate-btn');
        var $spinner = $('#glint-reports-spinner');
        
        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        var data = {
            action: 'glint_generate_reports',
            nonce: glint_reports_obj.nonce,
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            statuses: $('#statuses').val(),
            report_type: $('#report_type').val(),
            location_by: $('#location_by').val()
        };

        $.post(glint_reports_obj.ajax_url, data, function(response) {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');

            if (response.success) {
                renderReport(response.data);
            } else {
                alert('Error generating report: ' + response.data);
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');
            alert('An error occurred. Please try again.');
        });
    });

    function renderReport(data) {
        var labels = data.labels;
        var values = data.values;
        var tbody = $('#glint-reports-tbody');
        tbody.empty();

        if (labels.length === 0) {
            tbody.append('<tr><td colspan="2">No data found for the selected criteria.</td></tr>');
            if (reportsChart) {
                reportsChart.destroy();
            }
            return;
        }

        var total = 0;
        for (var i = 0; i < labels.length; i++) {
            total += parseInt(values[i], 10);
            tbody.append('<tr><td>' + labels[i] + '</td><td>' + values[i] + '</td></tr>');
        }
        tbody.append('<tr><td><strong>Total</strong></td><td><strong>' + total + '</strong></td></tr>');

        var colors = generateColors(labels.length);
        
        if (reportsChart) {
            reportsChart.destroy();
        }

        var ctx = document.getElementById('glintReportsChart').getContext('2d');
        reportsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }

    function generateColors(count) {
        var colors = [];
        for (var i = 0; i < count; i++) {
            var hue = (i * 137.508) % 360; 
            colors.push('hsl(' + hue + ', 70%, 50%)');
        }
        return colors;
    }
});
