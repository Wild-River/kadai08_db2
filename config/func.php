<?php
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sql_error($stmt)
{
    $error = $stmt->errorInfo();
    exit('送信エラー:' . $error[2]);
}

//リダイレクト
function redirect($file_name)
{
    header("Location: " . $file_name);
    exit();
}

function sortLink(string $label, string $key, string $currentSort, string $currentOrder, string $keyword): string
{
    $nextOrder = ($currentSort === $key && $currentOrder === 'ASC') ? 'desc' : 'asc';
    $params = ['sort' => $key, 'order' => $nextOrder];
    if ($keyword !== '') {
        $params['keyword'] = $keyword;
    }
    $arrow = '';
    if ($currentSort === $key) {
        $arrow = $currentOrder === 'ASC' ? ' ▲' : ' ▼';
    }
    return '<a href="?' . h(http_build_query($params)) . '">' . h($label) . $arrow . '</a>';
}

function statusLabels()
{
    return [
        'draft'   => '下書き',
        'sent'    => '送付済み',
        'paid'    => '入金済み',
        'overdue' => '期限超過',
    ];
}

// 請求書プレビューに表示する発行者情報（プレースホルダー。実際の情報に書き換えてください）
function companyInfo()
{
    return [
        'name'    => '株式会社サンプル',
        'zip'     => '000-0000',
        'address' => '東京都千代田区0-0-0',
        'tel'     => '03-0000-0000',
        'email'   => 'info@example.com',
    ];
}
