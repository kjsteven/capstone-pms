<?php
require '../session/db.php';

class PropertyMaintenanceReport {
    private $conn;

    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }

    public function generateReport($report_date, $report_year, $report_month, $generated_by) {
        $start_of_month = "$report_year-$report_month-01";
        $end_of_month = date('Y-m-t', strtotime($start_of_month));

        $query = "
            SELECT 
                mr.id, 
                u.name AS tenant_name,
                u.user_id AS tenant_id, 
                mr.unit,
                mr.issue,
                mr.description, 
                mr.service_date,
                mr.created_at,
                mr.image,
                mr.report_pdf,
                s.name AS staff_name,
                s.staff_id,
                s.specialty AS staff_specialty,
                mr.status
            FROM maintenance_requests mr
            JOIN users u ON mr.user_id = u.user_id
            LEFT JOIN staff s ON mr.assigned_to = s.staff_id
            WHERE mr.service_date BETWEEN ? AND ?
            ORDER BY mr.service_date DESC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $start_of_month, $end_of_month);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            throw new Exception("Database query failed: " . mysqli_error($this->conn));
        }

        // Initialize the report structure
        $report = [
            'title' => 'Property Maintenance Report',
            'overview' => [
                'report_date' => $report_date,
                'report_period' => "$report_year-$report_month",
                'generated_by' => $generated_by
            ],
            'maintenance_requests' => [],
            'summary' => []
        ];

        // Counters for summary statistics
        $totalRequests = 0;
        $pendingRequests = 0;
        $inProgressRequests = 0;
        $completedRequests = 0;
        $issueCategories = [];

        // Process each maintenance request
        while ($row = mysqli_fetch_assoc($result)) {
            $totalRequests++;
            
            // Count status
            if ($row['status'] === 'Pending') {
                $pendingRequests++;
            } elseif ($row['status'] === 'In Progress') {
                $inProgressRequests++;
            } elseif ($row['status'] === 'Completed') {
                $completedRequests++;
            }
            
            // Track issue categories
            if (!isset($issueCategories[$row['issue']])) {
                $issueCategories[$row['issue']] = 1;
            } else {
                $issueCategories[$row['issue']]++;
            }
            
            // Format the maintenance request for the report
            $report['maintenance_requests'][] = [
                'id' => $row['id'],
                'tenant_name' => $row['tenant_name'],
                'unit' => $row['unit'],
                'issue' => $row['issue'],
                'description' => $row['description'],
                'service_date' => $row['service_date'],
                'submitted_on' => $row['created_at'],
                'assigned_to' => $row['staff_name'] ?: 'Not Assigned',
                'staff_specialty' => $row['staff_specialty'] ?: 'N/A',
                'status' => $row['status']
            ];
        }

        // Generate summary statistics
        $report['summary'] = [
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'in_progress_requests' => $inProgressRequests,
            'completed_requests' => $completedRequests,
            'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0,
            'issue_categories' => $issueCategories
        ];

        return $report;
    }

    public function saveReportToDatabase($report, $filePath) {
        $json_data = json_encode($report);

        $query = "
            INSERT INTO generated_reports (report_type, report_date, report_period, report_data, file_path, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssss', 
            $report['title'], 
            $report['overview']['report_date'], 
            $report['overview']['report_period'], 
            $json_data, 
            $filePath
        );

        return mysqli_stmt_execute($stmt);
    }

    public function exportReportToCSV($report) {
        date_default_timezone_set('Asia/Manila');
        $filename = 'property_maintenance_report_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = '../reports/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Add report metadata
        fputcsv($file, ['Report Title', $report['title']]);
        fputcsv($file, ['Generated By', $report['overview']['generated_by']]);
        fputcsv($file, ['Date Generated', $report['overview']['report_date']]);
        fputcsv($file, ['Report Period', $report['overview']['report_period']]);
        fputcsv($file, []); // Empty line for spacing

        // Add summary section
        fputcsv($file, ['SUMMARY STATISTICS']);
        fputcsv($file, ['Total Requests', $report['summary']['total_requests']]);
        fputcsv($file, ['Pending Requests', $report['summary']['pending_requests']]);
        fputcsv($file, ['In Progress Requests', $report['summary']['in_progress_requests']]);
        fputcsv($file, ['Completed Requests', $report['summary']['completed_requests']]);
        fputcsv($file, ['Completion Rate', $report['summary']['completion_rate'] . '%']);
        fputcsv($file, []);

        // Add issue categories breakdown
        fputcsv($file, ['ISSUE CATEGORIES BREAKDOWN']);
        foreach ($report['summary']['issue_categories'] as $issue => $count) {
            fputcsv($file, [$issue, $count]);
        }
        fputcsv($file, []);

        // Add maintenance requests details
        fputcsv($file, ['MAINTENANCE REQUESTS DETAILS']);
        fputcsv($file, ['ID', 'Tenant Name', 'Unit', 'Issue', 'Description', 'Service Date', 'Submitted On', 'Assigned To', 'Staff Specialty', 'Status']);
        
        foreach ($report['maintenance_requests'] as $request) {
            fputcsv($file, [
                $request['id'],
                $request['tenant_name'],
                $request['unit'],
                $request['issue'],
                $request['description'],
                $request['service_date'],
                $request['submitted_on'],
                $request['assigned_to'],
                $request['staff_specialty'],
                $request['status']
            ]);
        }

        fclose($file);
        return $filename;
    }
}
