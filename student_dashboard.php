<?php
// Include session check
require_once 'backend/session_check.php';
check_student();

// Get student's name from session
$student_name = $_SESSION['user_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Scheduling System</title>
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3">
                        <!-- Display student name -->
                        <span class="navbar-text user-display">
                            Welcome, <?php echo htmlspecialchars($student_name); ?>!
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
        <h2 class="text-center mb-4">Student Dashboard</h2>

        <div class="row g-4">

            <!-- Appointment section -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        My Current Appointment
                    </div>
                    <div class="card-body" id="currentAppointmentDetails">
                        <!-- Appointment details will be loaded via JavaScript -->
                        <p><strong>Date:</strong> <span id="appt-date"></span></p>
                        <p><strong>Time:</strong> <span id="appt-time"></span></p>
                        <p><strong>Instructor:</strong> <span id="appt-instructor"></span></p>
                        <p><strong>Project:</strong> <span id="appt-project"></span></p>
                        <p><strong>Group Members:</strong> <span id="appt-group"></span></p>

                        <!-- Delete button -->
                        <button class="btn btn-danger mt-3" id="deleteAppointmentBtn">Delete Appointment</button>

                        <!-- Message if no appointment -->
                        <p id="noAppointmentMsg" class="text-muted">You do not have an appointment scheduled.</p>
                    </div>
                </div>
            </div>

            <!-- Scheduling actions section -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        Schedule Appointment
                    </div>
                    <div class="card-body text-center">
                        <p>View available slots and schedule your presentation time.</p>
                        <!-- Link to schedule page -->
                        <a href="schedule_appointment.php" class="btn btn-success btn-lg" id="scheduleNewBtn">
                            View Available Slots & Schedule New
                        </a>
                        <p class="mt-3 text-muted">(If you have an existing appointment, you must delete it before scheduling a new one.)</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2023 Scheduling System. <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load current appointment
            fetch('backend/student_api.php?action=get_appointment')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const detailsDiv = document.getElementById('currentAppointmentDetails');
                        const noMsg = document.getElementById('noAppointmentMsg');
                        const deleteBtn = document.getElementById('deleteAppointmentBtn');
                        const scheduleBtn = document.getElementById('scheduleNewBtn');
                        
                        if (data.has_appointment) {
                            // Display appointment details
                            document.getElementById('appt-date').textContent = data.appointment.date;
                            document.getElementById('appt-time').textContent = data.appointment.start_time;
                            document.getElementById('appt-instructor').textContent = data.appointment.instructor_name;
                            document.getElementById('appt-project').textContent = data.appointment.project_name;
                            document.getElementById('appt-group').textContent = data.appointment.group_members;
                            
                            // Show appointment details and delete button
                            detailsDiv.querySelectorAll('p:not(#noAppointmentMsg)').forEach(p => p.style.display = 'block');
                            deleteBtn.style.display = 'block';
                            noMsg.style.display = 'none';
                            
                            // Disable schedule button
                            scheduleBtn.classList.add('disabled');
                            scheduleBtn.setAttribute('aria-disabled', 'true');
                        } else {
                            // Hide appointment details and delete button
                            detailsDiv.querySelectorAll('p:not(#noAppointmentMsg)').forEach(p => p.style.display = 'none');
                            deleteBtn.style.display = 'none';
                            noMsg.style.display = 'block';
                            
                            // Enable schedule button
                            scheduleBtn.classList.remove('disabled');
                            scheduleBtn.removeAttribute('aria-disabled');
                        }
                    }
                })
                .catch(error => console.error('Error loading appointment:', error));
            
            // Delete appointment
            document.getElementById('deleteAppointmentBtn').addEventListener('click', function() {
                if (confirm('Are you sure you want to delete your current appointment?')) {
                    fetch('backend/student_api.php?action=delete_appointment', {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment deleted successfully.');
                            // Reload the page to reflect changes
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error deleting appointment:', error));
                }
            });
        });
    </script>
</body>
</html>
