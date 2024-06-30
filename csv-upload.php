<?php
include 'config.php';

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if file is a CSV
if ($fileType != "csv") {
    echo "Sorry, only CSV files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";

        // Process the CSV file
        $file = fopen($target_file, 'r');

        // Skip the header row
        fgetcsv($file);

        $data = [];

        while (($row = fgetcsv($file)) !== FALSE) {
            $department = $row[0];
            $name = $row[1];
            $number = $row[2];
            $datetime = $row[3];
            $location_id = $row[4];
            $id_number = $row[5];
            $verify_code = $row[6];
            $card_no = isset($row[7]) ? $row[7] : '';

            // Parse date only
            $datetime_parts = explode(' ', $datetime);
            if (count($datetime_parts) >= 2) {
                $date = date('Y-m-d', strtotime($datetime_parts[0]));

                // Check if this employee has already been added for this date
                $employeeExists = false;
                foreach ($data as $entry) {
                    if ($entry['number'] == $number && $entry['date'] == $date) {
                        $employeeExists = true;
                        break;
                    }
                }

                // If not already added, add to the data array
                if (!$employeeExists) {
                    $data[] = [
                        'department' => $department,
                        'name' => $name,
                        'number' => $number,
                        'date' => $date,
                        'location_id' => $location_id,
                        'verify_code' => $verify_code,
                        'card_no' => $card_no
                    ];
                }
            } else {
                echo "Error parsing date and time for row: " . implode(',', $row) . "<br>";
            }
        }

        fclose($file);

        // Debug: Output the contents of the data array
        echo "<pre>";
        print_r($data);
        echo "</pre>";

        // Insert data into attendance1 table
        foreach ($data as $entry) {
            $employeeID = $entry['number'];
            $unit = $entry['department'];
            $date = $entry['date'];
            $day = date('d', strtotime($date));
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $time_in = '09:00:00'; // Sample time
            $time_out = '17:00:00'; // Sample time
            $created_at = date('Y-m-d H:i:s');
            $updated_at = $created_at;
            $updated_by = 'system'; // Assuming 'system' updates, you can replace it with the actual user if needed.

            $sql = "INSERT INTO attendance1 (employeeID, unit, date, day, month, year, time_in, time_out, created_at, updated_at, updated_by)
                    VALUES ('$employeeID', '$unit', '$date', '$day', '$month', '$year', '$time_in', '$time_out', '$created_at', '$updated_at', '$updated_by')";

            if ($conn->query($sql) === TRUE) {
                echo "New record created successfully for ID $employeeID on $date<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        header("Location: add-attendance.php?action=success");
        $conn->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
