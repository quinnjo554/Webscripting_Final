<?php
// Start session and include required files
session_start();
require_once 'db_connection.php';
require_once 'session_check.php';

// Ensure user is a student
check_student();

// Get the requested action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
switch ($action) {
    case 'get_appointment':
        // Get the current appointment for the student
        getCurrentAppointment($conn);
        break;
        
    case 'delete_appointment':
        // Delete the student's appointment
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deleteAppointment($conn);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method not allowed. Use DELETE.']);
        }
        break;
        
    case 'get_available_slots':
        // Get all available slots
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        getAvailableSlots($conn, $date);
        break;
        
    case 'book_appointment':
        // Book a new appointment
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            bookAppointment($conn, $data);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method not allowed. Use POST.']);
        }
        break;
        
    default:
        // Unknown action
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Unknown action specified.']);
        break;
}

// Function to get the current appointment for the student
function getCurrentAppointment($conn) {
    try {
        $student_id = $_SESSION['user_id'];
        
        $query = "
            SELECT a.appointment_id, a.project_name, a.group_members,
                   s.date, s.start_time, s.end_time,
                   u.full_name as instructor_name
            FROM appointments a
            JOIN available_slots s ON a.slot_id = s.slot_id
            JOIN users u ON s.instructor_id = u.user_id
            WHERE a.student_id = ?
            ORDER BY s.date, s.start_time
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $appointment = $result->fetch_assoc();
            
            // Format dates and times for display
            $appointment['date'] = date('Y-m-d', strtotime($appointment['date']));
            $appointment['start_time'] = date('h:i A', strtotime($appointment['start_time']));
            $appointment['end_time'] = date('h:i A', strtotime($appointment['end_time']));
            
            echo json_encode(['success' => true, 'has_appointment' => true, 'appointment' => $appointment]);
        } else {
            echo json_encode(['success' => true, 'has_appointment' => false]);
        }
        
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to delete the student's appointment
function deleteAppointment($conn) {
    try {
        $student_id = $_SESSION['user_id'];
        
        // First, get the appointment details to update the slot
        $query = "SELECT a.slot_id FROM appointments a WHERE a.student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'No appointment found to delete.']);
            return;
        }
        
        $appointment = $result->fetch_assoc();
        $slot_id = $appointment['slot_id'];
        
        // Start a transaction
        $conn->begin_transaction();
        
        // Delete the appointment
        $query = "DELETE FROM appointments WHERE student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        // Update the slot to be available again
        $query = "UPDATE available_slots SET is_booked = FALSE WHERE slot_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully.']);
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        if (isset($conn) && !$conn->connect_error) {
            $conn->rollback();
        }
        
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to get all available slots
function getAvailableSlots($conn, $date) {
    try {
        $query = "
            SELECT s.slot_id, s.date, s.start_time, s.end_time, u.full_name as instructor_name
            FROM available_slots s
            JOIN users u ON s.instructor_id = u.user_id
            WHERE s.is_booked = FALSE AND s.date >= ?
            ORDER BY s.date, s.start_time
            LIMIT 50
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates and times for display
            $row['date_formatted'] = date('F j, Y', strtotime($row['date']));
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

// Function to book a new appointment
function bookAppointment($conn, $data) {
    try {
        // Validate required fields
        if (empty($data['slot_id']) || empty($data['project_name'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Missing required fields.']);
            return;
        }
        
        $student_id = $_SESSION['user_id'];
        $slot_id = $data['slot_id'];
        $project_name = $data['project_name'];
        $group_members = isset($data['group_members']) ? $data['group_members'] : '';
        
        // Check if student already has an appointment
        $query = "SELECT COUNT(*) as count FROM appointments WHERE student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'You already have an appointment. Please delete it first.']);
            return;
        }
        
        // Check if the slot is available
        $query = "SELECT is_booked FROM available_slots WHERE slot_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Time slot not found.']);
            return;
        }
        
        $slot = $result->fetch_assoc();
        if ($slot['is_booked']) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'This time slot is already booked.']);
            return;
        }
        
        // Start a transaction
        $conn->begin_transaction();
        
        // Book the appointment
        $query = "
            INSERT INTO appointments (slot_id, student_id, project_name, group_members) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $slot_id, $student_id, $project_name, $group_members);
        $stmt->execute();
        
        // Update the slot to be booked
        $query = "UPDATE available_slots SET is_booked = TRUE WHERE slot_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully.']);
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        if (isset($conn) && !$conn->connect_error) {
            $conn->rollback();
        }
        
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
