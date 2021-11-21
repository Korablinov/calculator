<?php


namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Calculator extends Controller
{
    public function index()
    {
        return view('index');
    }

    /**
     * @return JsonResponse
     */
    public function getBanks(): JsonResponse
    {
        $banks = DB::table('banks')->get();
        return new JsonResponse($banks);
    }

    /**
     * @param Logger $logger
     * @param Request $request
     * @return JsonResponse
     */
    public function getMortgages(Logger $logger, Request $request): JsonResponse
    {
        $bankId = $request->input('bank_id');

        $mortgages = DB::table('mortgages')->where('bank_id', $bankId)->get();

        return new JsonResponse($mortgages);
    }

    /**
     * @param Logger $logger
     * @param Request $request
     * @return JsonResponse
     */
    public function getPercent(Logger $logger, Request $request): JsonResponse
    {
        $mortgagesId = $request->input('mortgage_id');
        $logger->debug('айди ипотеки', [$mortgagesId]);

        $percent = DB::table('mortgages')->where('id', $mortgagesId)->value('percent');
        $logger->debug('Процент:', ['percent' => $percent]);

        return new JsonResponse(['percent' => $percent]);
    }

    /**
     * @param int $price
     * @param int $anInitialFee
     * @param float $percent
     * @param int $mortgageTerm
     * @return bool
     */
    private function validateData(int $price, int $anInitialFee, float $percent, int $mortgageTerm): bool
    {
        if (!$price) {
            return false;
        }
        if (!$percent) {
            return false;
        }
        if (!$anInitialFee) {
            return false;
        }
        if ($anInitialFee <= 0) {
            return false;
        }
        if ($percent > 100) {
            return false;
        }
        if ($price <= $anInitialFee) {
            return false;
        }
        if ($mortgageTerm % 12 !== 0) {
            return false;
        }
        return true;
    }

    /**
     * @param Logger $logger
     * @param Request $request
     * @return JsonResponse
     */
    public function createFileAndResults(Logger $logger, Request $request): JsonResponse
    {
        $logger->debug('request: ', [$request]);

        $price = (int)$request->input('price');
        $anInitialFee = (int)$request->input('anInitialFee');
        $percent = (float)$request->input('percent');
        $mortgageTerm = (int)$request->input('mortgageTerm');
        $randomString = (string)$request->input('randomString');

        if (!$this->validateData($price, $anInitialFee, $percent, $mortgageTerm)) {
            return new JsonResponse(['isValid' => 'err']);
        }

        $logger->debug('Данные: ', [
            'стоимость недвижимости:' => $price,
            'Первоначальный взнос:' => $anInitialFee,
            'Процентная ставка:' => $percent,
            'Срок ипотеки,мес:' => $mortgageTerm
        ]);

        $monthlyRate = $percent / 12 / 100; // Ежемесячная ставка
        $totalRate = (1 + $monthlyRate) ** $mortgageTerm; // Общая ставка
        $creditAmount = $price - $anInitialFee; // Сумма кредита
        $monthlyPayment = $creditAmount * $monthlyRate * $totalRate / ($totalRate - 1); // Ежемесячный платеж
        $balanceOwed = $creditAmount; // Остаток долга
        $overpayment = $monthlyPayment * $mortgageTerm - $creditAmount; // Переплата

        $results = [];
        for ($month = 1; $month <= $mortgageTerm; $month++) {
            $percentage = $balanceOwed * $monthlyRate;
            $mainPart = $monthlyPayment - $percentage;
            $balanceOwed = $balanceOwed - $mainPart;

            $results[] = [
                'percentage' => round($percentage),
                'mainPart' => round($mainPart),
                'balanceOwed' => round(abs($balanceOwed)),
                'month' => $month
            ];
        }
        $logger->debug('Результаты: ', [$results]);

        try {
            CreatePdf::create(
                $results,
                $creditAmount,
                $monthlyPayment,
                $mortgageTerm,
                $percent,
                $overpayment,
                $randomString
            );
        } catch (Exception $e) {
            $logger->error('Не удалось создать файл pdf  ', ['pdf_error' => $e->getMessage()]);
        }

        try {
            Excel::store(
                new CreateXlsx($results, (int)round($monthlyPayment)),
                '././temp/' .$randomString . '.xlsx'
            );
        } catch (Exception $e) {
            $logger->error('Не удалось создать файл xlsx ', ['xlsx_error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'overpayment' => round($overpayment),
            'creditAmount' => $creditAmount,
            'percent' => $percent,
            'monthlyPayment' => round($monthlyPayment)
        ]);
    }

    /**
     * @param Logger $logger
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPdf(Logger $logger, Request $request): \Illuminate\Http\Response
    {
        $logger->debug('request getPdf: ', [$request]);
        $randomString = (string)$request->input('randomString');
        $filename = "$randomString.pdf";
        $pathToFile = dirname(__DIR__, 3) . '/temp/' . $filename;
        $logger->debug($pathToFile);

        $file = '';
        try {
            $file = file_get_contents($pathToFile);
        } catch (Exception $e) {
            $logger->error('Не удалось cкачать файл PDF - ', ['download_pdf_error' => $e->getMessage()]);
        }

        $logger->debug('Пользователь скачал файл pdf');
        return Response::make($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * @param Logger $logger
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getXlsx(Logger $logger, Request $request): \Illuminate\Http\Response
    {
        $logger->debug('request getXlsx: ', [$request]);
        $randomString = (string)$request->input('randomString');
        $filename = "$randomString.xlsx";
        $pathToFile = dirname(__DIR__, 3) .
            '/storage/app/temp/' . $filename;
        $file = '';

        try {
            $file = file_get_contents($pathToFile);
        } catch (Exception $e) {
            $logger->error('Не удалось cкачать файл Xlsx - ', ['download_xlsx_error' => $e->getMessage()]);
        }

        return Response::make($file, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
