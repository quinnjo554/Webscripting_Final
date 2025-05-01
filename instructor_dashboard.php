<?php
// Include session check
require_once 'backend/session_check.php';
check_instructor();

// Get instructor's name from session
$instructor_name = $_SESSION['user_name'] ?? 'Instructor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - Scheduling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="Logo">
                Scheduling System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="setup_availability.php">Manage Availability</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3">
                        <!-- Display instructor name -->
                        <span class="navbar-text user-display">
                            Welcome, <?php echo htmlspecialchars($instructor_name); ?>!
                        </span>
                    </li>
                    <li class="nav-item">
                        <!-- Logout link -->
                        <a class="btn btn-outline-danger btn-sm" href="backend/logout.php">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <h2 class="text-center mb-4">Instructor Dashboard</h2>

        <!-- Quick links -->
        <div class="text-center mb-4">
            <a href="setup_availability.php" class="btn btn-primary btn-lg">
                Setup/Modify Available Time Slots
            </a>
        </div>

        <!-- View bookings card -->
        <div class="card">
            <div class="card-header">
                View Booked Appointments
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-auto">
                        <label for="viewDate" class="col-form-label">Select Date:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" id="viewDate" class="form-control" name="viewDate">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-info" id="showBookingsBtn">Show Bookings</button>
                    </div>
                </div>

                <!-- Bookings list -->
                <div id="bookedAppointmentsList">
                    <p class="text-muted">Select a date and click "Show Bookings" to view appointments.</p>
                    
                    <!-- Bookings table (hidden by default) -->
                    <table class="table table-striped table-hover d-none" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Student</th>
                                <th>Project Name</th>
                                <th>Group Members</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                    
                    <!-- No bookings message (hidden by default) -->
                    <p id="noBookingsMsg" class="alert alert-info d-none">No appointments booked for the selected date.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2023 Scheduling System. <a href="#">Help</a> | <a href="#">Contact</a></span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('viewDate').valueAsDate = new Date();
            
            // Show bookings button click handler
            document.getElementById('showBookingsBtn').addEventListener('click', function() {
                const selectedDate = document.getElementById('viewDate').value;
                if (!selectedDate) {
                    alert('Please select a date.');
                    return;
                }
                
                // Fetch bookings for the selected date
                fetch(`backend/instructor_api.php?action=get_bookings&date=${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const bookingsTable = document.getElementById('bookingsTable');
                            const bookingsTableBody = bookingsTable.querySelector('tbody');
                            const noBookingsMsg = document.getElementById('noBookingsMsg');
                            const defaultMsg = document.querySelector('#bookedAppointmentsList p.text-muted');
                            
                            // Clear previous content
                            bookingsTableBody.innerHTML = '';
                            
                            // Hide all messages and tables initially
                            bookingsTable.classList.add('d-none');
                            noBookingsMsg.classList.add('d-none');
                            defaultMsg.classList.add('d-none');
                            
                            if (data.bookings && data.bookings.length > 0) {
                                // Populate the table with bookings
                                data.bookings.forEach(booking => {
                                    const row = bookingsTableBody.insertRow();
                                    row.innerHTML = `
                                        <td>${booking.start_time} - ${booking.end_time}</td>
                                        <td>${booking.student_name}</td>
                                        <td>${booking.project_name}</td>
                                        <td>${booking.group_members || 'N/A'}</td>
                                    `;
                                });
                                
                                // Show the table
                                bookingsTable.classList.remove('d-none');
                            } else {
                                // Show no bookings message
                                noBookingsMsg.classList.remove('d-none');
                            }
                        } else {
                            alert('Error: ' + (data.error || 'Failed to load bookings.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching bookings:', error);
                        alert('Failed to load bookings. Please try again.');
                    });
            });
        });
    </script>
</body>
</html>
