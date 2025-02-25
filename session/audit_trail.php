<?php
require_once 'db.php';

function logActivity($id, $action, $details, $ip_address = null) {
    global $conn;
    
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Check if ID exists in staff table
    $staff_query = "SELECT 'staff' as type FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($staff_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_staff = $result->num_rows > 0;

    if ($is_staff) {
        // It's a staff member
        $query = "INSERT INTO activity_logs (staff_id, user_id, user_role, action, details, ip_address) 
                 VALUES (?, NULL, 'Staff', ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        $stmt->bind_param("isss", $id, $action, $details, $ip_address);
    } else {
        // It's a user - get their role from the users table
        $role_query = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $user_role = $role_result->fetch_assoc()['role'] ?? 'User';

        $query = "INSERT INTO activity_logs (user_id, staff_id, user_role, action, details, ip_address) 
                 VALUES (?, NULL, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        $stmt->bind_param("issss", $id, $user_role, $action, $details, $ip_address);
    }
    
    $result = $stmt->execute();
    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    } 
    
    $stmt->close();
    return true;
}

function getRecentActivities($limit = 10) {
    global $conn;
    
    $query = "SELECT al.*, 
              COALESCE(u.name, s.name) as name,
              CASE 
                  WHEN al.staff_id IS NOT NULL THEN 'Staff'
                  ELSE u.role 
              END as role
              FROM activity_logs al
              LEFT JOIN users u ON al.user_id = u.user_id
              LEFT JOIN staff s ON al.staff_id = s.staff_id
              ORDER BY al.timestamp DESC 
              LIMIT ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
