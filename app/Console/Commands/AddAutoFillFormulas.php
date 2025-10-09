<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AddAutoFillFormulas extends Command
{
    protected $signature = 'template:add-autofill';
    protected $description = 'Add auto-fill formulas to existing project transactions template';

    public function handle()
    {
        $templatePath = public_path('templates/project-transactions-template.xlsx');
        
        if (!file_exists($templatePath)) {
            $this->error('Template file not found. Please generate template first.');
            return 1;
        }

        try {
            // Load existing spreadsheet
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Add auto-fill formulas for rows 2-100
            for ($i = 2; $i <= 100; $i++) {
                // When project_key is entered, auto-fill project_name (Column B)
                $sheet->setCellValue('B' . $i, '=IF(A' . $i . '="","",VLOOKUP(A' . $i . ',\'Projects Reference\'.A:C,2,0))');
                
                // When project_key is entered, auto-fill developer_name (Column C)  
                $sheet->setCellValue('C' . $i, '=IF(A' . $i . '="","",VLOOKUP(A' . $i . ',\'Projects Reference\'.A:C,3,0))');
            }

            // Save the updated file
            $writer = new Xlsx($spreadsheet);
            $writer->save($templatePath);

            $this->info('Auto-fill formulas added successfully!');
            $this->info('Template now has VLOOKUP formulas for automatic project name and developer filling.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error adding formulas: ' . $e->getMessage());
            return 1;
        }
    }
}
