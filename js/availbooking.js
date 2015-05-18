
jQuery(document).ready(function($) {
    $('.makeRequest').click(function() {
        var link = this;
        var instance = $(link).data('instance');
        var year = $('#table_info'+ instance).data('year');
        var monthmin = $('#table_info'+ instance).data('monthmin');
        var name = $('#table_info'+ instance).data('name');
        var data = {
                action: 'availbooking_action',
                security : availbooking.security,
                year: year,
                month: monthmin,
                instance: instance,
                name: name
            };
            $.get(availbooking.ajaxurl, data, function(response) {
                $('#data-update'+ instance).html(response);
                $('#availcalheader'+ instance).text($('#table_info'+ instance).data('monthname'))
        }
        );
        return false;
    });
    $('.makeRequest2').click(function() {
        var link = this;
        var instance = $(link).data('instance');
        var year = $('#table_info'+ instance).data('year');
        var monthplus = $('#table_info'+ instance).data('monthplus');
        var name = $('#table_info'+ instance).data('name');
        var data = {
                action: 'availbooking_action',
                security : availbooking.security,
                year: year,
                month: monthplus,
                instance: instance,
                name: name
            };
            $.get(availbooking.ajaxurl, data, function(response) {
                $('#data-update'+ instance).html(response);                
                $('#availcalheader'+ instance).text($('#table_info'+ instance).data('monthname'))
        }
        );
        return false;
    });
});



