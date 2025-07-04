$(function() {
    /*=============================================
    LOCAL STORAGE VARIABLE 
    =============================================*/
    if(localStorage.getItem("captureRange2") != null){
        $("#daterange-btn2 span").html(localStorage.getItem("captureRange2"));
    }else{
        $("#daterange-btn2 span").html('<i class="fa fa-calendar"></i> Date Range');
    }

    /*=============================================
    DATES RANGE
    =============================================*/
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
            opens: 'right',
            drops: 'down',
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-primary',
            cancelClass: 'btn-default',
            separator: ' to ',
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
        },
        function(start, end, label) {
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
        }
    );

    // Function to update date range and redirect
    function updateDateRange(start, end, label) {
        var displayText;
        if (label === 'Custom Range') {
            displayText = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
        } else {
            displayText = label;
        }
        
        $('#daterange-btn2 span').html(displayText);
        localStorage.setItem("captureRange2", displayText);
        
        window.location = "index.php?route=reports&initialDate=" + start.format('YYYY-MM-DD') + "&finalDate=" + end.format('YYYY-MM-DD');
    }

    // Handle cancel button
    $('.daterangepicker .cancelBtn').on('click', function() {
        localStorage.removeItem("captureRange2");
        localStorage.clear();
        window.location = "reports";
    });
});