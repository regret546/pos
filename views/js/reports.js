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
    startDate: moment(),
    endDate  : moment(),
    opens: 'left',
    autoUpdateInput: true,
    alwaysShowCalendars: true,
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

        localStorage.setItem("captureRange2", "Yesterday");
        window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
    }
    
    if(selectedRange == "Today"){
        var today = moment();
        var initialDate = today.format('YYYY-MM-DD');
        var finalDate = today.format('YYYY-MM-DD');

        localStorage.setItem("captureRange2", "Today");
        window.location = "index.php?route=reports&initialDate="+initialDate+"&finalDate="+finalDate;
    }
})