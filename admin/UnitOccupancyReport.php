<?php
require '../session/db.php';

class UnitOccupancyReport {
    private $conn;

    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }

    public function generateReport($report_date, $report_year, $report_month) {
        $start_of_month = "$report_year-$report_month-01";
        $end_of_month = date('Y-m-t', strtotime($start_of_month));

        // Modified query to avoid the unnecessary binding of parameters
        $query = "
            SELECT 
                p.unit_id, p.unit_no, p.unit_type, p.status, p.monthly_rent,
                t.tenant_id, t.user_id, 
                u.name AS tenant_name,
                t.rent_from AS rent_start_date, 
                t.rent_until AS rent_end_date,
                t.outstanding_balance,
                t.payable_months,
                t.downpayment_amount,
                t.created_at
            FROM property p
            LEFT JOIN tenants t ON p.unit_id = t.unit_rented
            LEFT JOIN users u ON t.user_id = u.user_id
            ORDER BY p.unit_no
        ";

        // Execute the query without binding parameters
        $result = mysqli_query($this->conn, $query);

        if (!$result) {
            throw new Exception("Database query failed: " . mysqli_error($this->conn));
        }

        $report = [
            'overview' => [
                'report_date' => $report_date,
                'report_period' => "$report_year-$report_month"
            ],
            'units' => [],
            'summary' => []
        ];
        $total_units = 0;
        $occupied_units = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $total_units++;

            // Assign N/A for missing tenant details
            $tenant_name = empty($row['tenant_name']) ? 'N/A' : $row['tenant_name'];
            $rent_start_date = empty($row['rent_start_date']) ? 'N/A' : $row['rent_start_date'];
            $rent_end_date = empty($row['rent_end_date']) ? 'N/A' : $row['rent_end_date'];
            $outstanding_balance = empty($row['outstanding_balance']) ? 0 : $row['outstanding_balance'];
            $payable_months = empty($row['payable_months']) ? 0 : $row['payable_months'];
            $downpayment_amount = empty($row['downpayment_amount']) ? 0 : $row['downpayment_amount'];
            $registration_date = empty($row['created_at']) ? 'N/A' : $row['created_at'];

            // Add the unit to the report
            $report['units'][] = [
                'unit_number' => $row['unit_no'],
                'unit_type' => $row['unit_type'],
                'occupancy_status' => $row['status'],
                'tenant_name' => $tenant_name,
                'rent_start_date' => $rent_start_date,
                'rent_end_date' => $rent_end_date,
                'monthly_rent' => $row['monthly_rent'],
                'outstanding_balance' => $outstanding_balance,
                'payable_months' => $payable_months,
                'downpayment_amount' => $downpayment_amount,
                'registration_date' => $registration_date
            ];

            if ($row['status'] === 'Occupied') {
                $occupied_units++;
            }
        }

        // Summary
        $report['summary'] = [
            'total_units' => $total_units,
            'occupied_units' => $occupied_units,
            'available_units' => $total_units - $occupied_units,
            'occupancy_rate' => $total_units > 0 ? round(($occupied_units / $total_units) * 100, 2) : 0
        ];

        return $report;
    }

    public function saveReportToDatabase($report, $filePath) {
        // Convert the report array to JSON for storage
        $json_data = json_encode($report);
    
        // Prepare the SQL query to insert the report data along with the file path
        $query = "
            INSERT INTO generated_reports (report_type, report_date, report_period, report_data, file_path, created_at)
            VALUES ('Unit Occupancy Report', ?, ?, ?, ?, NOW())
        ";
    
        // Prepare the statement
        $stmt = mysqli_prepare($this->conn, $query);
    
        // Bind the parameters
        mysqli_stmt_bind_param($stmt, 'ssss', 
            $report['overview']['report_date'], 
            $report['overview']['report_period'], 
            $json_data, 
            $filePath
        );
    
        // Execute the statement
        return mysqli_stmt_execute($stmt);
    }
    

    public function exportReportToCSV($report) {
        $filename = 'unit_occupancy_report_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = '../reports/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');  
        fputcsv($file, ['Unit Number', 'Unit Type', 'Occupancy Status', 'Tenant Name', 'Rent Start Date', 'Rent End Date', 'Monthly Rent', 'Outstanding Balance', 'Payable Months', 'Downpayment Amount', 'Registration Date']);
        foreach ($report['units'] as $unit) {
            fputcsv($file, $unit);
        }
        fputcsv($file, []); // Blank row
        fputcsv($file, ['Summary Statistics']);
        fputcsv($file, ['Total Units', $report['summary']['total_units']]);
        fputcsv($file, ['Occupied Units', $report['summary']['occupied_units']]);
        fputcsv($file, ['Available Units', $report['summary']['available_units']]);
        fputcsv($file, ['Occupancy Rate', $report['summary']['occupancy_rate'] . '%']);
        fclose($file);

        return $filename;   
    }
}
?>
