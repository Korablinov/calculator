<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CreateXlsx implements FromView
{
    private array $result;
    private int $monthlyPayment;

    public function __construct(array $result, int $monthlyPayment)
    {
        $this->result = $result;
        $this->monthlyPayment = $monthlyPayment;
    }

    public function view(): View
    {
        return view('createXlsx', [
            'results' => $this->result,
            'monthlyPayment' => $this->monthlyPayment
        ]);
    }
}
