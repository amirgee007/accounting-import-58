<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class ApiLogsExcelFileExport implements FromView,ShouldAutoSize
{
    public $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    public function view(): View
    {
        return view('errors.downloadExcel', [
            'errors' => $this->errors
        ]);
    }
}
