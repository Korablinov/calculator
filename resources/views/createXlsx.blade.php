<table>
    <thead>
    <tr>
        <th rowspan="2" style="text-align: center;">Месяц</th>
        <th colspan="3" style="text-align: center;">Платеж .руб</th>
        <th  style="text-align: center;">Долг</th>
        <th  style="text-align: center;">Проценты</th>
        <th  style="text-align: center;">Всего</th>
        <th style="text-align: center;">Остаток долга</th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $result)
        <tr>
            <td>{{ $result['month'] }}</td>
            <td>{{ $result['mainPart'] }}</td>
            <td>{{ $result['percentage'] }}</td>
            <td>{{ $monthlyPayment }}</td>
            <td>{{ $result['balanceOwed'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
