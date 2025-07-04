$(function() {
    /*=============================================
    INITIALIZE DATE RANGE PICKER
    =============================================*/
    
    // Set initial value from localStorage
    if(localStorage.getItem("captureRange2") != null){
        $("#daterange-btn2").val(localStorage.getItem("captureRange2"));
    }

    // Initialize daterangepicker
    $('#daterange-btn2').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        locale: {
            format: 'MMMM D, YYYY',
            separator: ' - ',
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom Range',
            weekLabel: 'W',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            firstDay: 1
        }
    });

    // Handle date range selection
    $('#daterange-btn2').on('apply.daterangepicker', function(ev, picker) {
        var start = picker.startDate;
        var end = picker.endDate;
        var label = picker.chosenLabel;

        if (label === 'Yesterday') {
            // Check if there's data for yesterday
            $.ajax({
                url: "ajax/datatable-sales.ajax.php",
                method: "POST",
                data: {
                    initialDate: start.format('YYYY-MM-DD'),
                    finalDate: end.format('YYYY-MM-DD')
                },
                success: function(response) {
                    if (response && response.trim() !== '[]') {
                        updateDateRange(start, end, label);
                    } else {
                        swal({
                            type: "info",
                            title: "No Data",
                            text: "There are no sales records for yesterday",
                            showConfirmButton: true,
                            confirmButtonText: "Close"
                        });
                    }
                }
            });
        } else {
            updateDateRange(start, end, label);
        }
    });

    // Handle cancel
    $('#daterange-btn2').on('cancel.daterangepicker', function() {
        localStorage.removeItem("captureRange2");
        localStorage.clear();
        window.location = "reports";
    });

    // Function to update date range and redirect
    function updateDateRange(start, end, label) {
        var displayText = label;
        if (label === 'Custom Range') {
            displayText = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
        }
        
        localStorage.setItem("captureRange2", displayText);
        window.location = "index.php?route=reports&initialDate=" + start.format('YYYY-MM-DD') + "&finalDate=" + end.format('YYYY-MM-DD');
    }
});