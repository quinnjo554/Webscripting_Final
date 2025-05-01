<?php
require_once 'backend/session_check.php';
check_instructor();

$instructor_name = $_SESSION['user_name'] ?? 'Instructor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Availability - Scheduling System</title>
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
                        <a class="nav-link" href="instructor_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Manage Availability</a>
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
        <h2 class="text-center mb-4">Setup Available Time Slots</h2>
        
        <div class="row">
            <!-- Add New Slots Form -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        Add New Time Slots
                    </div>
                    <div class="card-body">
                        <form id="addSlotForm">
                            <div class="mb-3">
                                <label for="slotDate" class="form-label">Date:</label>
                                <input type="date" class="form-control" id="slotDate" name="slotDate" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Time Slots (20 minutes each):</label>
                                <div class="slot-time-container">
                                    <!-- JavaScript will create time slots here -->
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="startTime" class="form-label">Start Time:</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="numberOfSlots" class="form-label">Number of Slots:</label>
                                <select class="form-control" id="numberOfSlots" name="numberOfSlots" required>
                                    <option value="">Select</option>
                                    <option value="1">1 (20 minutes)</option>
                                    <option value="2">2 (40 minutes)</option>
                                    <option value="3">3 (1 hour)</option>
                                    <option value="4">4 (1 hour 20 minutes)</option>
                                    <option value="5">5 (1 hour 40 minutes)</option>
                                    <option value="6">6 (2 hours)</option>
                                </select>
                            </div>
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-primary" id="previewSlotsBtn">Preview Slots</button>
                                <button type="submit" class="btn btn-success" id="addSlotsBtn" disabled>Add Slots</button>
                            </div>
                        </form>
                        
                        <!-- Preview container -->
                        <div class="mt-4" id="previewContainer" style="display: none;">
                            <h5>Preview:</h5>
                            <div class="alert alert-info">
                                <p><strong>Date:</strong> <span id="previewDate"></span></p>
                                <div id="previewSlots"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- View Existing Slots -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        View & Manage Existing Slots
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-4">
                                <label for="viewDate" class="form-label">Select Date:</label>
                                <input type="date" class="form-control" id="viewDate" name="viewDate">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" id="viewSlotsBtn">View Slots</button>
                            </div>
                        </div>
                        
                        <!-- Existing slots container -->
                        <div id="existingSlotsContainer" class="mt-3">
                            <p class="text-muted">Select a date and click "View Slots" to see existing time slots.</p>
                        </div>
                    </div>
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
            // Set default dates to today
            document.getElementById('slotDate').valueAsDate = new Date();
            document.getElementById('viewDate').valueAsDate = new Date();
            
            // Elements
            const previewSlotsBtn = document.getElementById('previewSlotsBtn');
            const addSlotsBtn = document.getElementById('addSlotsBtn');
            const previewContainer = document.getElementById('previewContainer');
            const previewDate = document.getElementById('previewDate');
            const previewSlots = document.getElementById('previewSlots');
            const viewSlotsBtn = document.getElementById('viewSlotsBtn');
            const existingSlotsContainer = document.getElementById('existingSlotsContainer');
            
            // Preview slots button click
            previewSlotsBtn.addEventListener('click', function() {
                const date = document.getElementById('slotDate').value;
                const startTime = document.getElementById('startTime').value;
                const numberOfSlots = document.getElementById('numberOfSlots').value;
                
                if (!date || !startTime || !numberOfSlots) {
                    alert('Please fill all fields to preview slots.');
                    return;
                }
                
                // Generate preview
                previewDate.textContent = new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                previewSlots.innerHTML = '';
                
                const slots = generateTimeSlots(startTime, numberOfSlots);
                
                slots.forEach((slot, index) => {
                    previewSlots.innerHTML += `
                        <p><strong>Slot ${index + 1}:</strong> ${slot.start} - ${slot.end}</p>
                    `;
                });
                
                // Show preview and enable add button
                previewContainer.style.display = 'block';
                addSlotsBtn.disabled = false;
            });
            
            // Generate time slots
            function generateTimeSlots(startTime, numberOfSlots) {
                const slots = [];
                const startDate = new Date(`2000-01-01T${startTime}`);
                
                for (let i = 0; i < numberOfSlots; i++) {
                    const slotStart = new Date(startDate.getTime() + (i * 20 * 60000));
                    const slotEnd = new Date(slotStart.getTime() + (20 * 60000));
                    
                    slots.push({
                        start: slotStart.toTimeString().substring(0, 5),
                        end: slotEnd.toTimeString().substring(0, 5)
                    });
                }
                
                return slots;
            }
            
            // Format time for display (12-hour format)
            function formatTime(timeString) {
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours);
                const period = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes} ${period}`;
            }
            
            // Add slots form submission
            document.getElementById('addSlotForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const date = document.getElementById('slotDate').value;
                const startTime = document.getElementById('startTime').value;
                const numberOfSlots = parseInt(document.getElementById('numberOfSlots').value);
                
                if (!date || !startTime || isNaN(numberOfSlots)) {
                    alert('Please fill all fields correctly.');
                    return;
                }
                
                const slots = generateTimeSlots(startTime, numberOfSlots);
                const addPromises = [];
                
                // Add each slot separately
                slots.forEach(slot => {
                    const promise = fetch('backend/instructor_api.php?action=add_slot', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            date: date,
                            start_time: slot.start,
                            end_time: slot.end
                        })
                    }).then(response => response.json());
                    
                    addPromises.push(promise);
                });
                
                // Wait for all slots to be added
                Promise.all(addPromises)
                    .then(results => {
                        // Check if all slots were added successfully
                        const allSuccess = results.every(result => result.success);
                        
                        if (allSuccess) {
                            alert(`${numberOfSlots} time slot(s) added successfully!`);
                            
                            // Clear form
                            document.getElementById('startTime').value = '';
                            document.getElementById('numberOfSlots').value = '';
                            
                            // Hide preview and disable add button
                            previewContainer.style.display = 'none';
                            addSlotsBtn.disabled = true;
                            
                            // If viewing same date, refresh the view
                            if (document.getElementById('viewDate').value === date) {
                                viewSlotsForDate(date);
                            }
                        } else {
                            // Some slots failed to add
                            const errors = results.filter(result => !result.success).map(result => result.error);
                            alert(`Error adding some slots: ${errors.join(', ')}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error adding slots:', error);
                        alert('Failed to add slots. Please try again.');
                    });
            });
            
            // View slots button click
            viewSlotsBtn.addEventListener('click', function() {
                const date = document.getElementById('viewDate').value;
                if (!date) {
                    alert('Please select a date to view slots.');
                    return;
                }
                
                viewSlotsForDate(date);
            });
            
            // Function to view slots for a date
            function viewSlotsForDate(date) {
                existingSlotsContainer.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading existing slots...</p>
                    </div>
                `;
                
                fetch(`backend/instructor_api.php?action=get_slots&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.slots && data.slots.length > 0) {
                                // Display slots in a table
                                let html = `
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                `;
                                
                                data.slots.forEach(slot => {
                                    const status = slot.is_booked === '1' ? 
                                        '<span class="badge bg-danger">Booked</span>' : 
                                        '<span class="badge bg-success">Available</span>';
                                    const deleteBtn = slot.is_booked === '1' ?
                                        '<button disabled class="btn btn-sm btn-secondary me-2" title="Cannot delete booked slots">Delete</button>' :
                                        `<button class="btn btn-sm btn-danger me-2 delete-slot-btn" data-slot-id="${slot.slot_id}">Delete</button>`;
                                    
                                    html += `
                                        <tr>
                                            <td>${slot.start_time} - ${slot.end_time}</td>
                                            <td>${status}</td>
                                            <td>${deleteBtn}</td>
                                        </tr>
                                    `;
                                });
                                
                                html += `
                                        </tbody>
                                    </table>
                                `;
                                
                                existingSlotsContainer.innerHTML = html;
                                
                                // Add event listeners to delete buttons
                                document.querySelectorAll('.delete-slot-btn').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const slotId = this.dataset.slotId;
                                        if (confirm('Are you sure you want to delete this time slot?')) {
                                            deleteSlot(slotId, date);
                                        }
                                    });
                                });
                            } else {
                                existingSlotsContainer.innerHTML = `
                                    <div class="alert alert-info">
                                        No time slots found for the selected date.
                                    </div>
                                `;
                            }
                        } else {
                            existingSlotsContainer.innerHTML = `
                                <div class="alert alert-danger">
                                    Error loading slots: ${data.error || 'Unknown error'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading slots:', error);
                        existingSlotsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Failed to load time slots. Please try again.
                            </div>
                        `;
                    });
            }
            
            // Function to delete a slot
            function deleteSlot(slotId, date) {
                fetch(`backend/instructor_api.php?action=delete_slot&slot_id=${slotId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Time slot deleted successfully!');
                        // Refresh the view
                        viewSlotsForDate(date);
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete time slot.'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting slot:', error);
                    alert('Failed to delete time slot. Please try again.');
                });
            }
        });
    </script>
</body>
</html>
