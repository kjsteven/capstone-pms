<?php
require '../session/db.php';

class UnitOccupancyReport {
    private $conn;

    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }

    public function generateReport($report_date, $report_period) {
        $query = "
            SELECT 
                p.unit_id, p.unit_no, p.unit_type, p.status, p.monthly_rent,
                t.tenant_id, t.user_id, 
                u.name AS tenant_name, -- Use 'name' instead of CONCAT
                t.rent_from AS rent_start_date, 
                t.rent_until AS rent_end_date
            FROM property p
            LEFT JOIN tenants t ON p.unit_id = t.unit_rented
            LEFT JOIN users u ON t.user_id = u.user_id -- Ensure correct join and column usage
            ORDER BY p.unit_no
        ";
        $result = mysqli_query($this->conn, $query);
    
        if (!$result) {
            throw new Exception("Database query failed: " . mysqli_error($this->conn));
        }
    
        $report = ['overview' => compact('report_date', 'report_period'), 'units' => [], 'summary' => []];
        $total_units = $occupied_units = 0;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $total_units++;
            $report['units'][] = [
                'unit_number' => $row['unit_no'],
                'unit_type' => $row['unit_type'],
                'occupancy_status' => $row['status'],
                'tenant_name' => $row['tenant_name'] ?? 'N/A',
                'rent_start_date' => $row['rent_start_date'] ?? 'N/A',
                'rent_end_date' => $row['rent_end_date'] ?? 'N/A',
                'monthly_rent' => $row['monthly_rent']
            ];
            if ($row['status'] === 'Occupied') {
                $occupied_units++;
            }
        }
    
        $report['summary'] = [
            'total_units' => $total_units,
            'occupied_units' => $occupied_units,
            'available_units' => $total_units - $occupied_units,
            'occupancy_rate' => $total_units > 0 ? round(($occupied_units / $total_units) * 100, 2) : 0
        ];
    
        return $report;
    }
    

    public function saveReportToDatabase($report) {
        $json_data = json_encode($report);
        $query = "
            INSERT INTO generated_reports (report_type, report_date, report_period, report_data, created_at)
            VALUES ('Unit Occupancy Report', ?, ?, ?, NOW())
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $report['overview']['report_date'], $report['overview']['report_period'], $json_data);
        return mysqli_stmt_execute($stmt);
    }

    public function exportReportToCSV($report) {
        $filename = 'unit_occupancy_report_' . date('YmdHis') . '_' . uniqid() . '.csv';
        $filepath = '../reports/' . $filename;
         

        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        fputcsv($file, ['Unit Number', 'Unit Type', 'Occupancy Status', 'Tenant Name', 'Rent Start Date', 'Rent End Date', 'Monthly Rent']);
        foreach ($report['units'] as $unit) {
            fputcsv($file, $unit);
        }
        fputcsv($file, []);
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