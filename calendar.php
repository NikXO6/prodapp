<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Calendar</title>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Table CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.21.2/dist/bootstrap-table.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1>Production Calendar</h1>
        <div id="calendar"></div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        url: 'get_wo_for_calendar.php', // PHP that returns events
                        method: 'GET',
                        success: function(response) {
                            successCallback(response);
                        },
                        error: function() {
                            alert('Error fetching events');
                            failureCallback([]);
                        }
                    });
                },
                eventClick: function(info) {
                    // Open modal on event click and populate the form fields
                    $('#editModal').modal('show');
                    $('#work_order_id').val(info.event.id); // Set work order ID
                    $('#start_date').val(info.event.startStr); // Set start date
                    $('#end_date').val(info.event.endStr ? info.event.endStr : info.event.startStr); // Set end date, or default to start date
                },
                eventDidMount: function(info) {
                    // Tooltip logic
                    var tooltipContent = `
                <strong>WO Number:</strong> ${info.event.title}<br>
                <strong>Item Code:</strong> ${info.event.extendedProps.item_code}<br>
                <strong>Required Quantity:</strong> ${info.event.extendedProps.required_qty}<br>
                <strong>Line:</strong> ${info.event.extendedProps.line}<br>
                <strong>Status:</strong> ${info.event.extendedProps.status}
            `;
                    var tooltip = new bootstrap.Tooltip(info.el, {
                        title: tooltipContent,
                        html: true,
                        placement: 'top',
                        trigger: 'hover'
                    });
                }
            });

            calendar.render();
        });
    </script>
    <!-- Modal for editing work order dates -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editDatesForm" method="POST" action="update_wo_dates.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Work Order Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="work_order_id" id="work_order_id">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
<!-- Bootstrap JS (necessary for modal and tooltips) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<!-- jQuery (for AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</html>