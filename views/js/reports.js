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

$('#daterange-btn2').daterangepicker(
  {
    ranges   : {
      'Today'       : [moment(), moment()],
      'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 days' : [moment().subtract(6, 'days'), moment()],
      'Last 30 days': [moment().subtract(29, 'days'), moment()],
      'This Month'  : [moment().startOf('month'), moment().endOf('month')],
      'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment().subtract(29, 'days'),
    endDate: moment(),
    opens: 'left',
    autoUpdateInput: true,
    alwaysShowCalendars: true,
    showCustomRangeLabel: true,
    locale: {
        format: 'YYYY-MM-DD',
        customRangeLabel: 'Custom Range'
    }
  },
  function (start, end) {
    $('#daterange-btn2 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var initialDate = start.format('YYYY-MM-DD');
    var finalDate = end.format('YYYY-MM-DD');
    var captureRange = $("#daterange-btn2 span").html();
   
    localStorage.setItem("captureRange2", captureRange);

    window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
  }
)

/*=============================================
CANCEL DATES RANGE
=============================================*/

$(".daterangepicker .range_inputs .cancelBtn").on("click", function(){
    localStorage.removeItem("captureRange2");
    localStorage.clear();
    window.location = "reports";
})

/*=============================================
CAPTURE PREDEFINED DATE RANGES
=============================================*/

$(".daterangepicker .ranges li").on("click", function(){
    var selectedRange = $(this).attr("data-range-key");
    
    if(selectedRange == "Yesterday"){
        var yesterday = moment().subtract(1, 'days');
        var initialDate = yesterday.format('YYYY-MM-DD');
        var finalDate = yesterday.format('YYYY-MM-DD');

        // Check if there are sales for yesterday before redirecting
        $.ajax({
            url: "ajax/datatable-sales.ajax.php",
            method: "POST",
            data: {
                initialDate: initialDate,
                finalDate: finalDate
            },
            success: function(response) {
                if(response && response.trim() !== '[]') {
                    localStorage.setItem("captureRange2", "Yesterday");
                    window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
                } else {
                    // If no data, show message but keep the date picker active
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
    } else if(selectedRange == "Today"){
        var today = moment();
        var initialDate = today.format('YYYY-MM-DD');
        var finalDate = today.format('YYYY-MM-DD');

        localStorage.setItem("captureRange2", "Today");
        window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
    } else if(selectedRange == "Custom Range") {
        // Do nothing, let the date picker handle it
        return;
    } else {
        // For other predefined ranges
        var start = $(this).data('daterangepicker').startDate;
        var end = $(this).data('daterangepicker').endDate;
        var initialDate = start.format('YYYY-MM-DD');
        var finalDate = end.format('YYYY-MM-DD');

        localStorage.setItem("captureRange2", selectedRange);
        window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
    }
})