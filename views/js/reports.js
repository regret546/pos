/*=============================================
LOCAL STORAGE VARIABLE 
=============================================*/

if(localStorage.getItem("captureRange2") != null){
    $("#daterange-btn2 span").html(localStorage.getItem("captureRange2"));
}else{
    $("#daterange-btn2 span").html('<i class="fa fa-calendar"></i> Date Range')
}

/*=============================================
DATES RANGE
=============================================*/

// Initialize the date range picker
var daterangepicker = $('#daterange-btn2').daterangepicker(
    {
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 days': [moment().subtract(6, 'days'), moment()],
            'Last 30 days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        opens: 'left',
        autoUpdateInput: false,
        alwaysShowCalendars: true
    }
);

// Handle range selection
$('#daterange-btn2').on('apply.daterangepicker', function(ev, picker) {
    var start = picker.startDate;
    var end = picker.endDate;
    var selectedRange = picker.chosenLabel;

    if (selectedRange === 'Yesterday') {
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
                    updateDateRange(start, end, 'Yesterday');
                } else {
                    swal({
                        type: "info",
                        title: "No Data",
                        text: "There are no sales records for yesterday",
                        showConfirmButton: true,
                        confirmButtonText: "Close"
                    });
                    // Reset the date picker text
                    if (localStorage.getItem("captureRange2") != null) {
                        $("#daterange-btn2 span").html(localStorage.getItem("captureRange2"));
                    } else {
                        $("#daterange-btn2 span").html('<i class="fa fa-calendar"></i> Date Range');
                    }
                }
            }
        });
    } else {
        // For all other selections (including custom range)
        updateDateRange(start, end, selectedRange);
    }
});

// Handle cancel button
$('#daterange-btn2').on('cancel.daterangepicker', function() {
    localStorage.removeItem("captureRange2");
    localStorage.clear();
    window.location = "reports";
});

// Function to update date range and redirect
function updateDateRange(start, end, rangeLabel) {
    var initialDate = start.format('YYYY-MM-DD');
    var finalDate = end.format('YYYY-MM-DD');
    var displayText = rangeLabel;
    
    if (rangeLabel === 'Custom Range') {
        displayText = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
    }
    
    localStorage.setItem("captureRange2", displayText);
    window.location = "index.php?route=reports&initialDate=" + initialDate + "&finalDate=" + finalDate;
}