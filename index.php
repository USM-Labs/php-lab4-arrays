<?php

declare(strict_types=1);

/**
 * Calculates total amount of all transactions.
 *
 * @param array<int, array{id:int,date:string,amount:float,description:string,merchant:string}> $transactions
 */
function calculateTotalAmount(array $transactions): float
{
    $total = 0.0;

    foreach ($transactions as $transaction) {
        $total += $transaction['amount'];
    }

    return $total;
}

/**
 * Finds transactions by part of description.
 *
 * @param array<int, array{id:int,date:string,amount:float,description:string,merchant:string}> $transactions
 * @return array<int, array{id:int,date:string,amount:float,description:string,merchant:string}>
 */
function findTransactionByDescription(array $transactions, string $descriptionPart): array
{
    return array_values(array_filter(
        $transactions,
        static fn (array $transaction): bool => stripos($transaction['description'], $descriptionPart) !== false
    ));
}

/**
 * Finds transaction by id using foreach.
 *
 * @param array<int, array{id:int,date:string,amount:float,description:string,merchant:string}> $transactions
 */
function findTransactionById(array $transactions, int $id): ?array
{
    foreach ($transactions as $transaction) {
        if ($transaction['id'] === $id) {
            return $transaction;
        }
    }

    return null;
}

/**
 * Finds transaction by id using array_filter.
 *
 * @param array<int, array{id:int,date:string,amount:float,description:string,merchant:string}> $transactions
 */
function findTransactionByIdFilter(array $transactions, int $id): ?array
{
    $matches = array_values(array_filter(
        $transactions,
        static fn (array $transaction): bool => $transaction['id'] === $id
    ));

    return $matches[0] ?? null;
}

/**
 * Returns number of days between transaction date and today.
 */
function daysSinceTransaction(string $date): int
{
    $transactionDate = new DateTimeImmutable($date);
    $today = new DateTimeImmutable('today');

    return (int) $transactionDate->diff($today)->format('%a');
}

/**
 * Adds transaction to the global transaction list.
 */
function addTransaction(int $id, string $date, float $amount, string $description, string $merchant): void
{
    global $transactions;

    $transactions[] = [
        'id' => $id,
        'date' => $date,
        'amount' => $amount,
        'description' => $description,
        'merchant' => $merchant,
    ];
}

/**
 * Renders transactions as an HTML table.
 *
 * @param array<int, array{id:int,date:string,amount:float,description:string,merchant:string}> $transactions
 */
function renderTransactionsTable(array $transactions, string $caption): string
{
    $html = '<table><caption>' . htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') . '</caption>';
    $html .= '<thead><tr><th>ID</th><th>Дата</th><th>Сумма</th><th>Описание</th><th>Получатель</th><th>Дней прошло</th></tr></thead><tbody>';

    foreach ($transactions as $transaction) {
        $html .= sprintf(
            '<tr><td>%d</td><td>%s</td><td>%.2f</td><td>%s</td><td>%s</td><td>%d</td></tr>',
            $transaction['id'],
            htmlspecialchars($transaction['date'], ENT_QUOTES, 'UTF-8'),
            $transaction['amount'],
            htmlspecialchars($transaction['description'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($transaction['merchant'], ENT_QUOTES, 'UTF-8'),
            daysSinceTransaction($transaction['date'])
        );
    }

    $html .= sprintf(
        '<tr class="total"><td colspan="2">Итого</td><td>%.2f</td><td colspan="3"></td></tr>',
        calculateTotalAmount($transactions)
    );
    $html .= '</tbody></table>';

    return $html;
}

/**
 * Returns gallery image paths from a directory.
 *
 * @return array<int, string>
 */
function getGalleryImages(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = scandir($directory);

    if ($files === false) {
        return [];
    }

    $images = [];

    foreach ($files as $file) {
        $path = rtrim($directory, '/') . '/' . $file;

        if (is_file($path) && preg_match('/\.jpe?g$/i', $file) === 1) {
            $images[] = $path;
        }
    }

    return $images;
}

$transactions = [
    ['id' => 1, 'date' => '2024-01-10', 'amount' => 125.90, 'description' => 'Payment for groceries', 'merchant' => 'SuperMart'],
    ['id' => 2, 'date' => '2024-02-17', 'amount' => 48.50, 'description' => 'Dinner with friends', 'merchant' => 'Local Restaurant'],
    ['id' => 3, 'date' => '2024-03-03', 'amount' => 900.00, 'description' => 'Monthly rent', 'merchant' => 'City Apartments'],
    ['id' => 4, 'date' => '2024-03-18', 'amount' => 35.20, 'description' => 'Taxi ride', 'merchant' => 'Fast Taxi'],
    ['id' => 5, 'date' => '2024-04-02', 'amount' => 210.75, 'description' => 'Online course', 'merchant' => 'EduPlatform'],
    ['id' => 6, 'date' => '2024-04-26', 'amount' => 18.30, 'description' => 'Coffee meeting', 'merchant' => 'Bean House'],
    ['id' => 7, 'date' => '2024-05-11', 'amount' => 77.00, 'description' => 'Book order', 'merchant' => 'BookStore'],
    ['id' => 8, 'date' => '2024-05-28', 'amount' => 320.40, 'description' => 'Laptop repair', 'merchant' => 'Tech Service'],
];

addTransaction(9, '2024-06-14', 64.99, 'Streaming subscription', 'MediaBox');
addTransaction(10, '2024-07-01', 150.00, 'Gym membership', 'FitLife');

$transactionsByDate = $transactions;
usort($transactionsByDate, static fn (array $left, array $right): int => strcmp($left['date'], $right['date']));

$transactionsByAmount = $transactions;
usort($transactionsByAmount, static fn (array $left, array $right): int => $right['amount'] <=> $left['amount']);

$descriptionMatches = findTransactionByDescription($transactions, 'course');
$idMatch = findTransactionById($transactions, 5);
$idFilterMatch = findTransactionByIdFilter($transactions, 5);

$images = getGalleryImages(__DIR__ . '/image');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторная работа №4</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #202124; background: #f7faf9; }
        header, footer { background: #253237; color: white; padding: 24px 40px; }
        nav { background: #e2e8e4; padding: 12px 40px; }
        nav a { color: #253237; margin-right: 18px; text-decoration: none; font-weight: 700; }
        main { padding: 28px 40px; }
        table { border-collapse: collapse; width: 100%; margin: 18px 0 32px; background: white; }
        caption { text-align: left; font-weight: 700; margin-bottom: 8px; }
        th, td { border: 1px solid #cbd5d1; padding: 10px; text-align: left; }
        th { background: #edf2f0; }
        .total { font-weight: 700; background: #f2fbf6; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px; }
        .gallery img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5d1; }
        code { background: #edf2f0; padding: 2px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <h1>Лабораторная работа №4. Массивы и функции</h1>
    </header>
    <nav>
        <a href="#transactions">Транзакции</a>
        <a href="#search">Поиск</a>
        <a href="#gallery">Галерея</a>
    </nav>
    <main>
        <section id="transactions">
            <h2>Система управления банковскими транзакциями</h2>
            <?= renderTransactionsTable($transactions, 'Исходный список транзакций') ?>
            <?= renderTransactionsTable($transactionsByDate, 'Сортировка по дате') ?>
            <?= renderTransactionsTable($transactionsByAmount, 'Сортировка по сумме по убыванию') ?>
        </section>

        <section id="search">
            <h2>Поиск</h2>
            <p>Поиск по описанию <code>course</code>: найдено <?= count($descriptionMatches) ?>.</p>
            <p>Поиск по ID через foreach: <?= htmlspecialchars((string) ($idMatch['description'] ?? 'не найдено'), ENT_QUOTES, 'UTF-8') ?>.</p>
            <p>Поиск по ID через array_filter: <?= htmlspecialchars((string) ($idFilterMatch['description'] ?? 'не найдено'), ENT_QUOTES, 'UTF-8') ?>.</p>
        </section>

        <section id="gallery">
            <h2>Галерея изображений из директории image</h2>
            <div class="gallery">
                <?php foreach ($images as $image): ?>
                    <img src="<?= htmlspecialchars('image/' . basename($image), ENT_QUOTES, 'UTF-8') ?>" alt="Gallery image">
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>PHP 8+, строгая типизация, массивы, функции, файловая система.</p>
    </footer>
</body>
</html>
