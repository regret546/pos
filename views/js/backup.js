/*=============================================
BACKUP MANAGEMENT JAVASCRIPT
=============================================*/

$(document).ready(function () {
  // Load initial data
  loadBackupStats();
  loadBackupList();
  loadBackupLog();

  // Auto-refresh every 30 seconds
  setInterval(function () {
    loadBackupStats();
    loadBackupList();
  }, 30000);
});

/*=============================================
CREATE BACKUP
=============================================*/
function createBackup() {
  // Show loading modal
  $("#loadingModal").modal("show");

  // Disable button
  $("#createBackupBtn")
    .prop("disabled", true)
    .html('<i class="fa fa-spinner fa-spin"></i> Creating...');

  $.ajax({
    url: "ajax/backup.ajax.php",
    method: "POST",
    data: { action: "create" },
    dataType: "json",
    timeout: 300000, // 5 minutes timeout
    success: function (response) {
      $("#loadingModal").modal("hide");
      $("#createBackupBtn")
        .prop("disabled", false)
        .html('<i class="fa fa-database"></i> Create Backup Now');

      if (response.success) {
        swal({
          type: "success",
          title: "Backup Created Successfully!",
          text: response.message,
          showConfirmButton: true,
          confirmButtonText: "Close",
        });

        // Refresh data
        loadBackupStats();
        loadBackupList();
        loadBackupLog();
      } else {
        swal({
          type: "error",
          title: "Backup Failed!",
          text: response.message,
          showConfirmButton: true,
          confirmButtonText: "Close",
        });
      }
    },
    error: function (xhr, status, error) {
      $("#loadingModal").modal("hide");
      $("#createBackupBtn")
        .prop("disabled", false)
        .html('<i class="fa fa-database"></i> Create Backup Now');

      var errorMessage = "Could not create backup. ";
      if (status === "timeout") {
        errorMessage +=
          "The request timed out. The backup might still be processing - please check the backup list after a moment.";
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage += xhr.responseJSON.message;
      } else {
        errorMessage += "Please check the log for details and try again.";
      }

      swal({
        type: "error",
        title: "Error!",
        text: errorMessage,
        showConfirmButton: true,
        confirmButtonText: "Close",
      });
    },
  });
}

/*=============================================
LOAD BACKUP STATISTICS
=============================================*/
function loadBackupStats() {
  $.ajax({
    url: "ajax/backup.ajax.php",
    method: "POST",
    data: { action: "stats" },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        $("#totalBackups").text(response.data.total_backups);
        $("#totalSize").text(response.data.total_size);
        $("#lastBackup").text(
          response.data.newest_backup !== "None"
            ? formatDate(response.data.newest_backup)
            : "None"
        );
        $("#oldestBackup").text(
          response.data.oldest_backup !== "None"
            ? formatDate(response.data.oldest_backup)
            : "None"
        );
      }
    },
  });
}

/*=============================================
LOAD BACKUP FILES LIST
=============================================*/
function loadBackupList() {
  $.ajax({
    url: "ajax/backup.ajax.php",
    method: "POST",
    data: { action: "list" },
    dataType: "json",
    success: function (response) {
      var tbody = $("#backupTableBody");
      tbody.empty();

      if (response.success && response.data.length > 0) {
        $.each(response.data, function (index, backup) {
          var row =
            "<tr>" +
            "<td>" +
            (index + 1) +
            "</td>" +
            "<td><code>" +
            backup.filename +
            "</code></td>" +
            "<td>" +
            formatDate(backup.date) +
            "</td>" +
            '<td><span class="label label-info">' +
            backup.size +
            "</span></td>" +
            "<td>" +
            '<div class="btn-group">' +
            '<button class="btn btn-primary btn-sm" onclick="downloadBackup(\'' +
            backup.filename +
            '\')" title="Download">' +
            '<i class="fa fa-download"></i>' +
            "</button>" +
            '<button class="btn btn-danger btn-sm" onclick="deleteBackup(\'' +
            backup.filename +
            '\')" title="Delete">' +
            '<i class="fa fa-trash"></i>' +
            "</button>" +
            "</div>" +
            "</td>" +
            "</tr>";
          tbody.append(row);
        });
      } else {
        tbody.html(
          '<tr><td colspan="5" class="text-center">No backup files found</td></tr>'
        );
      }
    },
  });
}

/*=============================================
LOAD BACKUP LOG
=============================================*/
function loadBackupLog() {
  $.ajax({
    url: "ajax/backup.ajax.php",
    method: "POST",
    data: { action: "log" },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        $("#backupLog").text(response.data);
      } else {
        $("#backupLog").text("No log data available");
      }
    },
  });
}

/*=============================================
DOWNLOAD BACKUP
=============================================*/
function downloadBackup(filename) {
  window.location.href =
    "ajax/backup.ajax.php?action=download&file=" + encodeURIComponent(filename);
}

/*=============================================
DELETE BACKUP
=============================================*/
function deleteBackup(filename) {
  swal({
    title: "Are you sure?",
    text: "This will permanently delete the backup file: " + filename,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(function (result) {
    if (result.value) {
      $.ajax({
        url: "ajax/backup.ajax.php",
        method: "POST",
        data: {
          action: "delete",
          filename: filename,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            swal({
              type: "success",
              title: "Deleted!",
              text: "Backup file has been deleted.",
              showConfirmButton: true,
              confirmButtonText: "Close",
            });

            // Refresh data
            loadBackupStats();
            loadBackupList();
          } else {
            swal({
              type: "error",
              title: "Error!",
              text: response.message,
              showConfirmButton: true,
              confirmButtonText: "Close",
            });
          }
        },
      });
    }
  });
}

/*=============================================
REFRESH BACKUP LIST
=============================================*/
function refreshBackupList() {
  loadBackupStats();
  loadBackupList();
  loadBackupLog();

  // Show brief success message
  var button = event.target;
  var originalHtml = button.innerHTML;
  button.innerHTML = '<i class="fa fa-check"></i> Refreshed';
  setTimeout(function () {
    button.innerHTML = originalHtml;
  }, 2000);
}

/*=============================================
FORMAT DATE
=============================================*/
function formatDate(dateString) {
  if (dateString === "None") return "None";

  var date = new Date(dateString);
  var options = {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };

  return date.toLocaleDateString("en-US", options);
}
