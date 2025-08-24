<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

class CreateProperExcelTemplates extends Command
{
    protected $signature = 'templates:create-proper-excel';
    protected $description = 'Create proper Excel template files using PhpSpreadsheet directly';

    public function handle()
    {
        $templates = [
            'projects-template.xlsx' => [
                'headings' => ['project_key', 'title', 'developer_name', 'developer_email', 'developer_phone', 'location', 'type', 'unit_no', 'project', 'area', 'garden_area', 'bedrooms', 'bathrooms', 'floor', 'status', 'stage', 'target_1', 'target_2', 'entry_date', 'exit_date', 'investment_type'],
                'data' => [
                    ['PROP-001', 'Sample Villa Project', 'ABC Developers', 'contact@abcdev.com', '971501234567', 'Dubai Marina', 'Villa', 'V-101', 'Marina Heights', 2500.50, 150.25, 4, 3, 'Ground Floor', 'active', 'construction', 'Q4 2024', 'Q2 2025', '2023-01-15', '2025-06-30', 'residential'],
                    ['PROP-002', 'Downtown Apartment', 'XYZ Properties', 'info@xyzprop.com', '971507654321', 'Downtown Dubai', 'Apartment', 'A-205', 'City Center Tower', 1200.75, '', 2, 2, '2nd Floor', 'active', 'planning', 'Q1 2024', 'Q3 2024', '2023-03-01', '', 'commercial']
                ]
            ],
            'users-template.xlsx' => [
                'headings' => ['full_name', 'email', 'password', 'auth_provider', 'provider_user_id', 'email_verified', 'phone_number', 'country', 'profile_picture_url', 'is_active', 'last_login_at', 'theme_color', 'custom_theme_color', 'custom_id'],
                'data' => [
                    ['John Smith', 'john.smith@example.com', 'password123', 'local', '', true, '971501234567', 'UAE', '', true, '2024-01-15 10:30:00', 'blue', '#3B82F6', 'inv-100'],
                    ['Sarah Johnson', 'sarah.johnson@example.com', 'securepass456', 'local', '', true, '971507654321', 'UAE', '', true, '2024-02-20 14:45:00', 'green', '#10B981', 'inv-101']
                ]
            ],
            'project-evaluations-template.xlsx' => [
                'headings' => ['project_key', 'project_title', 'evaluation_date', 'evaluation_amount', 'notes'],
                'data' => [
                    ['PROP-001', '', '2024-01-01', 250000.50, 'Initial evaluation for Q1 2024'],
                    ['', 'Sample Villa Project', '2024-02-01', 275000.75, 'Monthly revaluation - market improvement'],
                    ['PROP-002', '', '2024-01-01', 150000.00, 'Downtown apartment evaluation']
                ]
            ],
            'developers-template.xlsx' => [
                'headings' => ['name', 'email', 'phone', 'address', 'website', 'description', 'status'],
                'data' => [
                    ['ABC Developers', 'contact@abcdev.com', '971501234567', '123 Business Bay, Dubai, UAE', 'https://www.abcdev.com', 'Leading real estate developer in Dubai Marina area', 'active'],
                    ['XYZ Properties', 'info@xyzprop.com', '971507654321', '456 Downtown Dubai, UAE', 'https://www.xyzprop.com', 'Specializing in luxury apartments and commercial spaces', 'active'],
                    ['Emirates Construction', '', '971509876543', '789 Jumeirah, Dubai, UAE', '', 'Traditional construction company with 20+ years experience', 'inactive']
                ]
            ],
            'project-transactions-template.xlsx' => [
                'headings' => ['project_key', 'financial_type', 'serving', 'amount', 'due_date', 'actual_date', 'transaction_date', 'method', 'reference_no', 'status', 'note', 'transaction_category'],
                'data' => [
                    ['PROP-001', 'revenue', 'operation', 5000.00, '2024-01-15', '2024-01-15', '2024-01-15', 'bank_transfer', 'REF-001', 'done', 'Monthly rental income from tenant', 'rental'],
                    ['PROP-001', 'expense', 'asset', 2000.00, '2024-01-10', '2024-01-12', '2024-01-12', 'cash', 'MAINT-001', 'done', 'Property maintenance and repairs', 'maintenance'],
                    ['PROP-002', 'revenue', 'asset', 15000.00, '2024-01-20', '', '2024-01-20', 'cheque', 'COMM-001', 'pending', 'Property sale commission payment', 'commission'],
                    ['PROP-002', 'expense', 'operation', 500.00, '2024-01-18', '2024-01-18', '2024-01-18', 'credit_card', 'MKT-001', 'done', 'Digital marketing campaign expenses', 'marketing']
                ]
            ],
            'user-transactions-template.xlsx' => [
                'headings' => ['user_id', 'type', 'amount', 'transaction_date', 'description', 'status'],
                'data' => [
                    [1, 'deposit', 10000.00, '2024-01-15', 'Initial investment deposit', 'done'],
                    [1, 'withdrawal', 2000.00, '2024-01-20', 'Partial withdrawal', 'done'],
                    [2, 'deposit', 5000.00, '2024-01-18', 'Monthly contribution', 'done'],
                    [2, 'deposit', 3000.00, '2024-01-25', 'Additional investment', 'pending']
                ]
            ]
        ];

        foreach ($templates as $filename => $template) {
            $this->createExcelFile($filename, $template['headings'], $template['data']);
            $this->info("Generated: {$filename}");
        }

        $this->info('All proper Excel templates created successfully!');
    }

    private function createExcelFile($filename, $headings, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headings
        $col = 1;
        foreach ($headings as $heading) {
            $sheet->setCellValueByColumnAndRow($col, 1, $heading);
            $col++;
        }

        // Make headings bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // Set data
        $row = 2;
        foreach ($data as $dataRow) {
            $col = 1;
            foreach ($dataRow as $value) {
                $sheet->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $path = public_path('templates/' . $filename);
        $writer->save($path);
    }
}
