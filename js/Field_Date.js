jQuery(document).ready(function() {
    jQuery(function() {        
        jQuery("#start_date").datepicker({            
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            minDate: '0',
            onClose: function(selectedDate) {
                jQuery("#end_date").datepicker("option", "minDate", selectedDate);
            }
        });
        jQuery("#end_date").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            onClose: function(selectedDate) {
                jQuery("#start_date").datepicker("option", "maxDate", selectedDate);
            }
        });
        jQuery("#date").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,            
        });
    });
});


