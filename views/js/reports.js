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

$(".daterangepicker").on("show.daterangepicker", function (ev, picker) {
    $('.ranges li').off('click').on('click', function(e) {
        var selectedRange = $(this).attr("data-range-key");
        
        if(selectedRange == "Yesterday"){
            var yesterday = moment().subtract(1, 'days');
            var initialDate = yesterday.format('YYYY-MM-DD');
            var finalDate = yesterday.format('YYYY-MM-DD');

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
                        swal({
                            type: "info",
                            title: "No Data",
                            text: "There are no sales records for yesterday",
                            showConfirmButton: true,
                            confirmButtonText: "Close"
                        });
                        // Prevent the picker from closing
                        e.stopPropagation();
                        e.preventDefault();
                    }
                }
            });
        }
        // For all other options, let the default daterangepicker handle it
    });
});

// Handle the apply button click separately
$(".daterangepicker .applyBtn").on("click", function(){
    var picker = $('#daterange-btn2').data('daterangepicker');
    var initialDate = picker.startDate.format('YYYY-MM-DD');
    var finalDate = picker.endDate.format('YYYY-MM-DD');
    var captureRange = picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY');
    
    localStorage.setItem("captureRange2", captureRange);
    window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
});