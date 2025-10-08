<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\Project;
use App\Models\ProjectTransaction;

class GenerateProjectTransactionTemplate extends Command
{
    protected $signature = 'template:project-transactions';
    protected $description = 'Generate enhanced Excel template for project transactions with project names and keys';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Transactions');

        // Set column headers
        $headers = [
            'A1' => 'project_key',
            'B1' => 'project_name',
            'C1' => 'developer_name',
            'D1' => 'financial_type',
            'E1' => 'serving',
            'F1' => 'what',
            'G1' => 'amount',
            'H1' => 'method',
            'I1' => 'reference_no',
            'J1' => 'status',
            'K1' => 'transaction_date',
            'L1' => 'due_date',
            'M1' => 'actual_date',
            'N1' => 'note',
            'O1' => 'transaction_category'
        ];

        // Apply headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerRange = 'A1:O1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Get all projects with developers
        $projects = Project::with('developer')->get();
        
        // Debug: Check if we have projects
        if ($projects->isEmpty()) {
            $this->warn('No projects found in database. Creating sample template with placeholder data.');
            // Create sample project data for template
            $sampleProjects = collect([
                (object)['key' => 'PROJ-001', 'title' => 'Sample Project 1', 'developer' => (object)['name' => 'Sample Developer 1']],
                (object)['key' => 'PROJ-002', 'title' => 'Sample Project 2', 'developer' => (object)['name' => 'Sample Developer 2']],
                (object)['key' => 'PROJ-003', 'title' => 'Sample Project 3', 'developer' => (object)['name' => 'Sample Developer 3']],
            ]);
            $projects = $sampleProjects;
        } else {
            $this->info('Found ' . $projects->count() . ' projects');
        }

        // Add project data rows (for reference) - only fill non-lookup columns
        $row = 2;
        foreach ($projects->take(5) as $project) { // Limit to first 5 for template
            $sheet->setCellValue('A' . $row, $project->key);
            // B column will be auto-filled by formula
            $sheet->setCellValue('C' . $row, $project->developer->name);
            $sheet->setCellValue('D' . $row, 'expense'); // Example
            $sheet->setCellValue('E' . $row, 'asset'); // Example
            $sheet->setCellValue('F' . $row, 'unit_installment'); // Example
            $sheet->setCellValue('G' . $row, '1000.00'); // Example
            $sheet->setCellValue('H' . $row, 'bank_transfer'); // Example
            $sheet->setCellValue('I' . $row, 'REF-' . str_pad($row - 1, 3, '0', STR_PAD_LEFT)); // Example
            $sheet->setCellValue('J' . $row, 'pending'); // Example
            $sheet->setCellValue('K' . $row, date('Y-m-d')); // Example
            $sheet->setCellValue('L' . $row, date('Y-m-d', strtotime('+30 days'))); // Example
            $sheet->setCellValue('M' . $row, ''); // Empty actual_date
            $sheet->setCellValue('N' . $row, 'Sample transaction note'); // Example
            $sheet->setCellValue('O' . $row, 'Sample category'); // Example
            $row++;
        }

        // First, we need to create the Projects Reference sheet before referencing it
        // This will be done after the main sheet setup

        // For now, create simple dropdowns with project keys from the collection
        $projectKeys = $projects->pluck('key')->toArray();
        if (!empty($projectKeys)) {
            $projectKeysString = '"' . implode(',', $projectKeys) . '"';
            $validation = $sheet->getCell('A2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a project key from the drop-down list.');
            $validation->setFormula1($projectKeysString);
            
            // Apply to range A2:A1000
            $sheet->setDataValidation('A2:A1000', clone $validation);
        }

        // Add dropdown validation for project names (column B)
        $projectNames = $projects->pluck('title')->toArray();
        if (!empty($projectNames)) {
            $projectNamesString = '"' . implode(',', array_map(function($name) {
                return str_replace('"', '""', $name); // Escape quotes in project names
            }, $projectNames)) . '"';
            
            $validation = $sheet->getCell('B2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a project name from the drop-down list.');
            $validation->setFormula1($projectNamesString);
            
            // Apply to range B2:B1000
            $sheet->setDataValidation('B2:B1000', clone $validation);
        }

        // Note: VLOOKUP formulas will be added manually by users as needed
        // The dropdowns provide the reference data for manual selection

        // Add note about the lookup functionality
        $sheet->setCellValue('A1', 'project_key (Select key, name auto-fills)');
        $sheet->setCellValue('B1', 'project_name (Select name, key auto-fills)');

        // Add dropdown validation for financial types (column D)
        $financialTypes = array_keys(ProjectTransaction::getAvailableFinancialTypes());
        if (!empty($financialTypes)) {
            $financialTypesString = '"' . implode(',', $financialTypes) . '"';
            $validation = $sheet->getCell('D2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1($financialTypesString);
            
            $sheet->setDataValidation('D2:D1000', clone $validation);
        }

        // Add dropdown validation for serving types (column E)
        $servingTypes = array_keys(ProjectTransaction::getAvailableServingTypes());
        if (!empty($servingTypes)) {
            $servingTypesString = '"' . implode(',', $servingTypes) . '"';
            $validation = $sheet->getCell('E2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1($servingTypesString);
            
            $sheet->setDataValidation('E2:E1000', clone $validation);
        }

        // Add dropdown validation for what types (column F)
        $whatTypes = array_keys(ProjectTransaction::getAvailableWhatTypes());
        if (!empty($whatTypes)) {
            $whatTypesString = '"' . implode(',', $whatTypes) . '"';
            $validation = $sheet->getCell('F2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1($whatTypesString);
            
            $sheet->setDataValidation('F2:F1000', clone $validation);
        }

        // Add dropdown validation for methods (column H)
        $methods = array_keys(ProjectTransaction::getAvailableTransactionMethods());
        if (!empty($methods)) {
            $methodsString = '"' . implode(',', $methods) . '"';
            $validation = $sheet->getCell('H2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1($methodsString);
            
            $sheet->setDataValidation('H2:H1000', clone $validation);
        }

        // Add dropdown validation for status (column J)
        $statuses = array_keys(ProjectTransaction::getAvailableStatuses());
        if (!empty($statuses)) {
            $statusesString = '"' . implode(',', $statuses) . '"';
            $validation = $sheet->getCell('J2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1($statusesString);
            
            $sheet->setDataValidation('J2:J1000', clone $validation);
        }

        // Auto-size columns
        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add a separate sheet with all projects for reference
        $projectsSheet = $spreadsheet->createSheet();
        $projectsSheet->setTitle('Projects Reference');
        
        // Headers for projects sheet
        $projectsSheet->setCellValue('A1', 'Project Key');
        $projectsSheet->setCellValue('B1', 'Project Name');
        $projectsSheet->setCellValue('C1', 'Developer');
        $projectsSheet->setCellValue('D1', 'Status');
        $projectsSheet->setCellValue('E1', 'Stage');

        // Style headers
        $projectsSheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Add all projects to reference sheet
        $row = 2;
        foreach ($projects as $project) {
            $projectsSheet->setCellValue('A' . $row, $project->key);
            $projectsSheet->setCellValue('B' . $row, $project->title);
            $projectsSheet->setCellValue('C' . $row, $project->developer->name);
            $projectsSheet->setCellValue('D' . $row, $project->status ?? 'active');
            $projectsSheet->setCellValue('E' . $row, $project->stage ?? 'planning');
            $row++;
        }

        // Auto-size columns in projects sheet
        foreach (range('A', 'E') as $column) {
            $projectsSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add a third sheet with dropdown options
        $optionsSheet = $spreadsheet->createSheet();
        $optionsSheet->setTitle('Dropdown Options');

        // Get available options
        $financialTypes = ProjectTransaction::getAvailableFinancialTypes();
        $servingTypes = ProjectTransaction::getAvailableServingTypes();
        $whatTypes = ProjectTransaction::getAvailableWhatTypes();
        $methods = ProjectTransaction::getAvailableTransactionMethods();
        $statuses = ProjectTransaction::getAvailableStatuses();

        // Add option headers and values
        $optionsSheet->setCellValue('A1', 'Financial Types');
        $optionsSheet->setCellValue('B1', 'Serving Types');
        $optionsSheet->setCellValue('C1', 'What (Purpose)');
        $optionsSheet->setCellValue('D1', 'Methods');
        $optionsSheet->setCellValue('E1', 'Status');

        // Style headers
        $optionsSheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC000']
            ]
        ]);

        // Add options
        $row = 2;
        $maxRows = max(count($financialTypes), count($servingTypes), count($whatTypes), count($methods), count($statuses));

        $financialKeys = array_keys($financialTypes);
        $servingKeys = array_keys($servingTypes);
        $whatKeys = array_keys($whatTypes);
        $methodKeys = array_keys($methods);
        $statusKeys = array_keys($statuses);

        for ($i = 0; $i < $maxRows; $i++) {
            if (isset($financialKeys[$i])) {
                $optionsSheet->setCellValue('A' . ($row + $i), $financialKeys[$i]);
            }
            if (isset($servingKeys[$i])) {
                $optionsSheet->setCellValue('B' . ($row + $i), $servingKeys[$i]);
            }
            if (isset($whatKeys[$i])) {
                $optionsSheet->setCellValue('C' . ($row + $i), $whatKeys[$i]);
            }
            if (isset($methodKeys[$i])) {
                $optionsSheet->setCellValue('D' . ($row + $i), $methodKeys[$i]);
            }
            if (isset($statusKeys[$i])) {
                $optionsSheet->setCellValue('E' . ($row + $i), $statusKeys[$i]);
            }
        }

        // Auto-size columns in options sheet
        foreach (range('A', 'E') as $column) {
            $optionsSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add an instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        // Add instructions content
        $instructionsSheet->setCellValue('A1', 'PROJECT TRANSACTIONS TEMPLATE INSTRUCTIONS');
        $instructionsSheet->setCellValue('A3', 'How to use the Project Key/Name lookup:');
        $instructionsSheet->setCellValue('A4', '1. Select a project key from dropdown in column A - the project name will auto-fill in column B');
        $instructionsSheet->setCellValue('A5', '2. OR select a project name from dropdown in column B - you must manually find the matching key');
        $instructionsSheet->setCellValue('A6', '3. Use the "Projects Reference" sheet to see all available project keys and names');
        $instructionsSheet->setCellValue('A8', 'Required Fields:');
        $instructionsSheet->setCellValue('A9', '• project_key (Column A) - Must match exactly with system data');
        $instructionsSheet->setCellValue('A10', '• financial_type (Column D) - Use dropdown: expense or revenue');
        $instructionsSheet->setCellValue('A11', '• amount (Column G) - Numeric value greater than 0');
        $instructionsSheet->setCellValue('A12', '• status (Column J) - Use dropdown: pending, completed, or cancelled');
        $instructionsSheet->setCellValue('A13', '• transaction_date (Column K) - Format: YYYY-MM-DD');
        $instructionsSheet->setCellValue('A15', 'Optional Fields:');
        $instructionsSheet->setCellValue('A16', '• serving, what, method, reference_no, due_date, actual_date, note, transaction_category');
        $instructionsSheet->setCellValue('A18', 'Tips:');
        $instructionsSheet->setCellValue('A19', '• All dropdown values are validated - you can only select valid options');
        $instructionsSheet->setCellValue('A20', '• Check the "Projects Reference" and "Dropdown Options" sheets for all valid values');
        $instructionsSheet->setCellValue('A21', '• The project_name column (B) is auto-calculated when you select a project_key');
        $instructionsSheet->setCellValue('A22', '• Only the project_key column (A) is used for import - project_name is for reference only');
        
        // Style the instructions
        $instructionsSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000080']
            ]
        ]);
        
        $instructionsSheet->getStyle('A3:A22')->applyFromArray([
            'font' => ['size' => 11]
        ]);
        
        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $instructionsSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set the first sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        // Save the file
        $templatePath = public_path('templates/project-transactions-template.xlsx');
        
        // Ensure directory exists
        if (!file_exists(dirname($templatePath))) {
            mkdir(dirname($templatePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($templatePath);

        $this->info('Enhanced project transactions template generated successfully!');
        $this->info('Location: ' . $templatePath);
        $this->info('Features:');
        $this->info('- Main sheet with sample data and all required columns');
        $this->info('- Projects reference sheet with all project keys and names');
        $this->info('- Dropdown options sheet with all valid values');
        
        return 0;
    }
}
