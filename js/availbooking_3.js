jQuery(document).ready(function($) {
    $('.makeRequest').click(function() {        
        var link = this;
        var instance = $(link).data('instance');
        var year = $('#table_info0'+ instance).data('year');
        var monthmin = $('#table_info0'+ instance).data('monthmin');
        var name = $('#table_info0'+ instance).data('name');
        var data = {
                action: 'availbooking_action',
                security : availbooking.security,
                year: year,
                month: monthmin,
                instance: instance,
                name: name
            };
            $.get(availbooking.ajaxurl, data, function(response) {
                var result = response.split('|');
                $('#data-update0'+ instance).html(result[0]);
                $('#availcalheader0'+ instance).text($('#table_info0'+ instance).data('monthname'))
                $('#data-update1'+ instance).html(result[1]);
                $('#availcalheader1'+ instance).text($('#table_info1'+ instance).data('monthname'))
                $('#data-update2'+ instance).html(result[2]);
                $('#availcalheader2'+ instance).text($('#table_info2'+ instance).data('monthname'))
        }
        );
        return false;
    });
    $('.makeRequest2').click(function() {
        var link = this;
        var instance = $(link).data('instance');
        var year = $('#table_info0'+ instance).data('year');
        var monthplus = $('#table_info0'+ instance).data('monthplus');
        var name = $('#table_info0'+ instance).data('name');
        var data = {
                action: 'availbooking_action',
                security : availbooking.security,
                year: year,
                month: monthplus,
                instance: instance,
                name: name
            };
           $.get(availbooking.ajaxurl, data, function(response) {
                var result = response.split('|');
                $('#data-update0'+ instance).html(result[0]);
                $('#availcalheader0'+ instance).text($('#table_info0'+ instance).data('monthname'))
                $('#data-update1'+ instance).html(result[1]);
                $('#availcalheader1'+ instance).text($('#table_info1'+ instance).data('monthname'))
                $('#data-update2'+ instance).html(result[2]);
                $('#availcalheader2'+ instance).text($('#table_info2'+ instance).data('monthname'))
        }
        );
        return false;
    });
});



