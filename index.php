<?php
$pdo = new PDO('mysql:host=localhost;dbname=caffeine_db;charset=utf8mb4', 'usr01', 'db01');

// 查詢日期，預設今天
$date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// 刪除指定記錄
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM caffeine_log WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: index.php?date=" . urlencode($_POST['date']));
    exit;
}

// 新增資料
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['item']) && isset($_POST['caffeine']) && isset($_POST['date'])) {
    $item = $_POST['item'];
    $caffeine = intval($_POST['caffeine']);
    $insertDate = $_POST['date'];
    $stmt = $pdo->prepare("INSERT INTO caffeine_log (date, item, caffeine) VALUES (?, ?, ?)");
    $stmt->execute([$insertDate, $item, $caffeine]);
    header("Location: index.php?date=" . urlencode($insertDate));
    exit;
}

// 查詢資料
$stmt = $pdo->prepare("SELECT * FROM caffeine_log WHERE date = ?");
$stmt->execute([$date]);
$data = $stmt->fetchAll();

$total = 0;
foreach ($data as $row) {
    $total += $row['caffeine'];
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>咖啡因攝取紀錄</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 2rem; }
        h1, h2 { color: #333; }
        form, table { background: white; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.5rem; border-bottom: 1px solid #ccc; text-align: center; }
        .warning { color: red; font-weight: bold; }
        .ok { color: green; font-weight: bold; }
        .total-cell { font-weight: bold; }
        .overlimit { background-color: #ffdddd; }
        .delete-btn {
            background: #ff6666;
            border: none;
            outline: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-btn:hover { background: #ff3333; }
    </style>
</head>
<body>
    <h1>☕ 咖啡因攝取紀錄</h1>

    <form method="GET">
        <label>查詢日期：
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        </label>
        <button type="submit">查詢</button>
    </form>

    <form method="POST">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        <label>品項名稱：
            <input type="text" name="item" required>
        </label>
        <label>咖啡因含量（mg）：
            <input type="number" name="caffeine" required min="0">
        </label>
        <button type="submit">新增紀錄（<?= htmlspecialchars($date) ?>）</button>
    </form>

    <h2>📅 <?= htmlspecialchars($date) ?> 的攝取紀錄</h2>
    <table>
        <thead>
            <tr>
                <th>品項</th>
                <th>咖啡因（mg）</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['item']) ?></td>
                <td><?= intval($row['caffeine']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                        <button type="submit" class="delete-btn">刪除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-cell <?= ($total > 400 ? 'overlimit' : '') ?>">
                <td>總攝取</td>
                <td colspan="2"><?= $total ?> mg</td>
            </tr>
        </tbody>
    </table>

    <?php if ($total > 400): ?>
        <p class="warning">⚠️ 此日攝取量已超過建議上限 400mg，請留意！</p>
    <?php else: ?>
        <p class="ok">✅ 尚未超標，請持續注意健康！</p>
    <?php endif; ?>

    <hr>
    <section style="font-size: 0.95rem; color: #555; background-color: #fffbe6; padding: 1rem; border-radius: 8px; border: 1px solid #ffe58f;">
        <h3>📌 咖啡因每日建議攝取上限：</h3>
        <ul>
            <li><strong>成人：</strong>最多 <span style="color: red;">400mg</span></li>
            <li><strong>孕婦與哺乳期婦女：</strong>建議不超過 <span style="color: red;">300mg</span></li>
            <li><strong>青少年與兒童：</strong>建議不超過 <span style="color: red;">100mg</span></li>
        </ul>
        <p>⚠️ 長期攝取過量咖啡因可能導致失眠、心悸、焦慮、骨質疏鬆等健康問題。</p>
    </section>
</body>
</html>
