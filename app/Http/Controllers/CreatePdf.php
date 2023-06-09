<?php


namespace App\Http\Controllers;

use Dompdf\Dompdf;

class CreatePdf
{
    /**
     * @param array $results
     * @param int $creditAmount
     * @param int $monthlyPayment
     * @param int $mortgageTerm
     * @param float $percent
     * @param int $overpayment
     * @param string $randomString
     */
    public static function create(
        array $results,
        int $creditAmount,
        int $monthlyPayment,
        int $mortgageTerm,
        float $percent,
        int $overpayment,
        string $randomString
    ) {
        $html = "<!DOCTYPE html>
<html lang='ru'>
<head>
    <title>План-график</title>
    <meta charset='windows-1251'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body {
            font-size: 10px;
        }
        .table {
            width: 100%;
            margin-bottom: 4rem;
            border: 1px solid #1a202c;
        }
        .table th, {
            width: 100%;
            font-size: 1rem;
            background-color: #eee;
            border: 1px solid #1a202c;
        }
        .table td {
            width: 100%;
            font-size: .8rem;
            border: 1px solid #1a202c;
        }
    </style>
</head>
<body>
<div style='width: 100%; max-width: 960px; margin: auto'>
    <h1>Расчёты:<br>
        Сумма ипотеки: $creditAmount руб <br>
        Ежемесячный платеж: $monthlyPayment руб <br>
        Срок: $mortgageTerm мес. <br>
        Ставка: $percent % <br>
        Переплата: $overpayment руб.</h1>
        <table class='table'>
            <thead>
            <tr>
                <th rowspan='2' style='text-align: center;'>Месяц</th>
                <th colspan='3' style='text-align: center;'>Платеж .руб</th>
                <th rowspan='2' style='text-align: center;'>Остаток долга</th>
            </tr>
            <tr>
                <th  style='text-align: center;'>Долг</th>
                <th  style='text-align: center;'>Проценты</th>
                <th  style='text-align: center;'>Всего</th>
            </tr>
            </thead>
            <tbody>";
        foreach ($results as $result) {
            $month = $result['month'];
            $mainPart = $result['mainPart'];
            $percentage = $result['percentage'];
            $balanceOwed = $result['balanceOwed'];
            $html .= "<tr>
            <td style='text-align: center'> $month</td>
            <td style='text-align: center'> $mainPart</td>
            <td style='text-align: center'> $percentage</td>
            <td style='text-align: center'> $monthlyPayment</td>
            <td style='text-align: center'> $balanceOwed</td>
            </tr>";
        }
        $html .= "</tbody>
        </table>
</div>
</body>
</html>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'Portrait');
        $dompdf->render();

        $pdf = $dompdf->output();
        file_put_contents(dirname(__DIR__, 3) . '/temp/' . $randomString . '.pdf', $pdf);
    }
}
