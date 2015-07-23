jQuery(document).ready(function() {
    jQuery(function() {        
        jQuery("#start_date").datepicker({            
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
        });
        jQuery("#end_date").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true, 
            minDate: '0',            
        });        
    });
});


