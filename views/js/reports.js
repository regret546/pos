$(function () {
  /*=============================================
    LOCAL STORAGE VARIABLE 
    =============================================*/

  if (localStorage.getItem("captureRange2") != null) {
    $("#daterange-btn2 span").html(localStorage.getItem("captureRange2"));
  } else {
    $("#daterange-btn2 span").html('<i class="fa fa-calendar"></i> Date range');
  }

  /*=============================================
    DATES RANGE
    =============================================*/

  $("#daterange-btn2").daterangepicker({
    ranges: {
      Today: [moment(), moment()],
      Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
      "Last 7 Days": [moment().subtract(6, "days"), moment()],
      "Last 30 Days": [moment().subtract(29, "days"), moment()],
      "This Month": [moment().startOf("month"), moment().endOf("month")],
      "Last Month": [
        moment().subtract(1, "month").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
      "Last 3 Months": [
        moment().subtract(3, "months").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
      "This Year": [moment().startOf("year"), moment().endOf("year")],
      "Last Year": [
        moment().subtract(1, "year").startOf("year"),
        moment().subtract(1, "year").endOf("year"),
      ],
    },
    startDate: moment(),
    endDate: moment(),
    opens: "left",
    drops: "down",
    showDropdowns: true,
    showWeekNumbers: false,
    alwaysShowCalendars: true,
    autoUpdateInput: false,
    autoApply: false,
    linkedCalendars: false,
    timePicker: false,
    showCustomRangeLabel: true,
    buttonClasses: "btn btn-sm",
    applyClass: "btn-success",
    cancelClass: "btn-default",
    locale: {
      format: "MMMM D, YYYY",
      separator: " - ",
      applyLabel: "Apply",
      cancelLabel: "Cancel",
      fromLabel: "From",
      toLabel: "To",
      customRangeLabel: "Custom Range",
      weekLabel: "W",
      daysOfWeek: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
      monthNames: [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ],
      firstDay: 1,
    },
  });

  // Handle apply button click
  $("#daterange-btn2").on("apply.daterangepicker", function (ev, picker) {
    $("#daterange-btn2 span").html(
      picker.startDate.format("MMMM D, YYYY") +
        " - " +
        picker.endDate.format("MMMM D, YYYY")
    );

    var initialDate = picker.startDate.format("YYYY-MM-DD");
    var finalDate = picker.endDate.format("YYYY-MM-DD");

    var captureRange = $("#daterange-btn2 span").html();

    localStorage.setItem("captureRange2", captureRange);

    window.location =
      "index.php?route=reports&inicialDate=" +
      initialDate +
      "&finalDate=" +
      finalDate;
  });

  // Handle cancel button click
  $("#daterange-btn2").on("cancel.daterangepicker", function (ev, picker) {
    // Do nothing, just close the picker
  });

  /*=============================================
    CANCEL DATES RANGE
    =============================================*/

  $(".daterangepicker .range_inputs .cancelBtn").on("click", function () {
    localStorage.removeItem("captureRange2");
    localStorage.clear();
    window.location = "index.php?route=reports";
  });
});
