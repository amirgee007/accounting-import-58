<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductSkuRenamedListExport implements FromView,ShouldAutoSize
{
    public $names;


    public function __construct($names)
    {
        $this->names = $names;
    }

    public function view(): View
    {
        return view('partials.downloadExcelRenameFiles', [
            'names' => $this->names
        ]);
    }
}
