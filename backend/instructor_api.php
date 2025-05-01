<?php
session_start();
require_once 'db_connection.php';
require_once 'session_check.php';

check_instructor();

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
switch ($action) {
    case 'get_slots':
        // Get available slots for a specific date
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        getSlots($conn, $date);
        break;
        
    case 'add_slot':
        // Add a new available slot
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            addSlot($conn, $data);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method not allowed. Use POST.']);
        }
        break;
        
    case 'delete_slot':
        // Delete an available slot
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;
            deleteSlot($conn, $slot_id);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method not allowed. Use DELETE.']);
        }
        break;
        
    case 'get_bookings':
        // Get bookings for a specific date
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        getBookings($conn, $date);
        break;
        
    default:
        // Unknown action
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Unknown action specified.']);
        break;
}

// Function to get available slots for a specific date
function getSlots($conn, $date) {
    try {
        $instructor_id = $_SESSION['user_id'];
        
        $query = "
            SELECT slot_id, date, start_time, end_time, is_booked 
            FROM available_slots 
            WHERE instructor_id = ? AND date = ? 
            ORDER BY start_time
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $instructor_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            // Format times for display
            $row['start_time'] = date('h:i A', strtotime($row['start_time']));
            $row['end_time'] = date('h:i A', strtotime($row['end_time']));
            
            $slots[] = $row;
        }
        
        echo json_encode(['success' => true, 'slots' => $slots]);
        
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to add a new available slot
function addSlot($conn, $data) {
    try {
        // Validate required fields
        if (empty($data['date']) || empty($data['start_time']) || empty($data['end_time'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Missing required fields.']);
            return;
        }
        
        $instructor_id = $_SESSION['user_id'];
        $date = $data['date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        
        // Insert new slot
        $query = "
            INSERT INTO available_slots (instructor_id, date, start_time, end_time) 
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $instructor_id, $date, $start_time, $end_time);
        $stmt->execute();
        
        $new_slot_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Slot added successfully.',
            'slot_id' => $new_slot_id
        ]);
        
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to delete an available slot
function deleteSlot($conn, $slot_id) {
    try {
        // First, check if the slot belongs to the instructor
        $instructor_id = $_SESSION['user_id'];
        
        $query = "
            SELECT * FROM available_slots 
            WHERE slot_id = ? AND instructor_id = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $slot_id, $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to delete this slot.']);
            return;
        }
        
        // Check if the slot is already booked
        $row = $result->fetch_assoc();
        if ($row['is_booked']) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Cannot delete a slot that is already booked.']);
            return;
        }
        
        // Delete the slot
        $query = "DELETE FROM available_slots WHERE slot_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Slot deleted successfully.']);
        
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to get bookings for a specific date
function getBookings($conn, $date) {
    try {
        $instructor_id = $_SESSION['user_id'];
        
        $query = "
            SELECT a.appointment_id, a.slot_id, a.project_name, a.group_members, 
                   s.date, s.start_time, s.end_time, 
                   u.full_name as student_name, u.email as student_email
            FROM appointments a
            JOIN available_slots s ON a.slot_id = s.slot_id
            JOIN users u ON a.student_id = u.user_id
            WHERE s.instructor_id = ? AND s.date = ?
            ORDER BY s.start_time
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $instructor_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Format times for display
            $row['start_time'] = date('h:i A', strtotime($row['start_time']));
            $row['end_time'] = date('h:i A', strtotime($row['end_time']));
            
            $bookings[] = $row;
        }
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
        
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
