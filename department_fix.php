<?php

include 'config.php';

// Step 1: Get distinct departments from employees table
$sql = "SELECT DISTINCT department FROM employees";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $department = $row['department'];

        // Step 2: Count the number of employees in the department
        $countSql = "SELECT COUNT(*) AS employee_count FROM employees WHERE department = ?";
        $stmt = $conn->prepare($countSql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $currentStrength = $countRow['employee_count'];

        // Step 3: Insert or update the department information into the department table
        $insertSql = "INSERT INTO department (name, current_Strength, allowed_Strength, created_at, updated_at) 
                      VALUES (?, ?, ?, NOW(), NOW()) 
                      ON DUPLICATE KEY UPDATE 
                      current_Strength = VALUES(current_Strength), 
                      allowed_Strength = VALUES(allowed_Strength), 
                      updated_at = VALUES(updated_at)";
        
        $allowedStrength = $currentStrength + 5;
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sii", $department, $currentStrength, $allowedStrength);
        $insertStmt->execute();
    }
} else {
    echo "No departments found.";
}

// Close the connection
$conn->close();
?>