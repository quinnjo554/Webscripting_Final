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
    <title>Schedule Appointment - Scheduling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .slot-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .slot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .slot-card.selected {
            border: 2px solid #6a11cb;
        }
    </style>
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
                        <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Schedule Appointment</a>
                    </li>
                </ul>
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
        <h2 class="text-center mb-4">Schedule Appointment</h2>
        
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Available Time Slots
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-4">
                                <label for="filterDate" class="form-label">Filter by Date:</label>
                                <input type="date" id="filterDate" class="form-control" name="filterDate">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" id="filterBtn">Filter Slots</button>
                            </div>
                        </div>
                        
                        <div id="availableSlotsContainer" class="row row-cols-1 row-cols-md-2 g-4 mt-2">
                            <!-- Available slots will be loaded here -->
                            <div class="col text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p>Loading available slots...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking form (hidden initially) -->
        <div class="row mt-4" id="bookingFormContainer" style="display: none;">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Book Selected Time Slot
                    </div>
                    <div class="card-body">
                        <form id="bookingForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="selectedSlot" class="form-label">Selected Time Slot:</label>
                                        <input type="text" class="form-control" id="selectedSlot" readonly>
                                        <input type="hidden" id="slotId" name="slotId">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="selectedInstructor" class="form-label">Instructor:</label>
                                        <input type="text" class="form-control" id="selectedInstructor" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="projectName" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="projectName" name="projectName" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="groupMembers" class="form-label">Group Members <small class="text-muted">(comma-separated names)</small></label>
                                <textarea class="form-control" id="groupMembers" name="groupMembers" rows="3" placeholder="Enter the names of your team members, separated by commas"></textarea>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-success">Confirm Booking</button>
                                <button type="button" class="btn btn-outline-secondary ms-2" id="cancelBookingBtn">Cancel</button>
                            </div>
                        </form>
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
            // Set default date to today
            document.getElementById('filterDate').valueAsDate = new Date();
            
            // Elements
            const filterBtn = document.getElementById('filterBtn');
            const availableSlotsContainer = document.getElementById('availableSlotsContainer');
            const bookingFormContainer = document.getElementById('bookingFormContainer');
            const bookingForm = document.getElementById('bookingForm');
            const cancelBookingBtn = document.getElementById('cancelBookingBtn');
            
            // First load of available slots (today)
            loadAvailableSlots(document.getElementById('filterDate').value);
            
            // Filter button click
            filterBtn.addEventListener('click', function() {
                const selectedDate = document.getElementById('filterDate').value;
                if (!selectedDate) {
                    alert('Please select a date.');
                    return;
                }
                loadAvailableSlots(selectedDate);
            });
            
            // Function to load available slots
            function loadAvailableSlots(date) {
                availableSlotsContainer.innerHTML = `
                    <div class="col text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading available slots...</p>
                    </div>
                `;
                
                fetch(`backend/student_api.php?action=get_available_slots&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.slots && data.slots.length > 0) {
                                availableSlotsContainer.innerHTML = '';
                                
                                // Group slots by date
                                const slotsByDate = {};
                                data.slots.forEach(slot => {
                                    if (!slotsByDate[slot.date]) {
                                        slotsByDate[slot.date] = [];
                                    }
                                    slotsByDate[slot.date].push(slot);
                                });
                                
                                // Display slots grouped by date
                                Object.keys(slotsByDate).forEach(date => {
                                    const dateHeader = document.createElement('div');
                                    dateHeader.className = 'col-12 mb-2';
                                    dateHeader.innerHTML = `<h4>${slotsByDate[date][0].date_formatted}</h4>`;
                                    availableSlotsContainer.appendChild(dateHeader);
                                    
                                    // Display slots for this date
                                    slotsByDate[date].forEach(slot => {
                                        const slotCol = document.createElement('div');
                                        slotCol.className = 'col';
                                        slotCol.innerHTML = `
                                            <div class="card slot-card h-100" data-slot-id="${slot.slot_id}" data-slot-date="${slot.date_formatted}" data-slot-time="${slot.start_time}" data-instructor="${slot.instructor_name}">
                                                <div class="card-body">
                                                    <h5 class="card-title">${slot.start_time} - ${slot.end_time}</h5>
                                                    <p class="card-text">Instructor: ${slot.instructor_name}</p>
                                                </div>
                                                <div class="card-footer text-center">
                                                    <button class="btn btn-sm btn-outline-primary select-slot-btn">Select</button>
                                                </div>
                                            </div>
                                        `;
                                        availableSlotsContainer.appendChild(slotCol);
                                    });
                                });
                                
                                // Add event listeners to select slot buttons
                                document.querySelectorAll('.select-slot-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        // Remove selection from all cards
                                        document.querySelectorAll('.slot-card').forEach(card => {
                                            card.classList.remove('selected');
                                        });
                                        
                                        // Select this card
                                        const card = this.closest('.slot-card');
                                        card.classList.add('selected');
                                        
                                        // Set selected slot details in form
                                        const slotId = card.dataset.slotId;
                                        const slotDate = card.dataset.slotDate;
                                        const slotTime = card.dataset.slotTime;
                                        const instructor = card.dataset.instructor;
                                        
                                        document.getElementById('slotId').value = slotId;
                                        document.getElementById('selectedSlot').value = `${slotDate} at ${slotTime}`;
                                        document.getElementById('selectedInstructor').value = instructor;
                                        
                                        // Show booking form
                                        bookingFormContainer.style.display = 'block';
                                        
                                        // Scroll to booking form
                                        bookingFormContainer.scrollIntoView({ behavior: 'smooth' });
                                    });
                                });
                            } else {
                                availableSlotsContainer.innerHTML = `
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No available time slots found for the selected date. Please try another date.
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            availableSlotsContainer.innerHTML = `
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        Error loading available slots: ${data.error || 'Unknown error'}
                                    </div>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading slots:', error);
                        availableSlotsContainer.innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    Failed to load available slots. Please try again.
                                </div>
                            </div>
                        `;
                    });
            }
            
            // Cancel booking button
            cancelBookingBtn.addEventListener('click', function() {
                // Hide booking form
                bookingFormContainer.style.display = 'none';
                
                // Clear selection
                document.querySelectorAll('.slot-card').forEach(card => {
                    card.classList.remove('selected');
                });
            });
            
            // Booking form submission
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const slotId = document.getElementById('slotId').value;
                const projectName = document.getElementById('projectName').value;
                const groupMembers = document.getElementById('groupMembers').value;
                
                if (!slotId) {
                    alert('Please select a time slot.');
                    return;
                }
                
                if (!projectName) {
                    alert('Please enter a project name.');
                    return;
                }
                
                // Book appointment
                fetch('backend/student_api.php?action=book_appointment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        slot_id: slotId,
                        project_name: projectName,
                        group_members: groupMembers
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Appointment booked successfully!');
                        // Redirect to dashboard
                        window.location.href = 'student_dashboard.php';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to book appointment.'));
                    }
                })
                .catch(error => {
                    console.error('Error booking appointment:', error);
                    alert('Failed to book appointment. Please try again.');
                });
            });
        });
    </script>
</body>
</html>

