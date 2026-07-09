// 明細行を1行追加する
function addRow() {
    const tbody = document.getElementById('items-body');
    const table = document.getElementById('items-table');
    const formId = table.dataset.form;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="item_name[]" class="form-input"></td>
        <td><input type="number" name="quantity[]" class="form-input" value="1"></td>
        <td><input type="text" name="unit_price[]" class="form-input money-input" value="0" inputmode="numeric" autocomplete="off"></td>
        <td class="item-amount">0 円</td>
        <td><button type="button" class="delete-btn delete-btn--icon" onclick="removeRow(this)" title="削除" aria-label="削除"><i class="fa-solid fa-trash"></i></button></td>
    `;

    // テーブルがフォームの外にある場合（invoice_edit.phpのように data-form 属性がある場合）は、
    // 各inputにform属性を付けて元のフォームへ送信されるようにする
    if (formId) {
        tr.querySelectorAll('input').forEach((input) => input.setAttribute('form', formId));
    }

    tbody.appendChild(tr);

    // 追加した行の金額列（js/item-amount.js）を計算させる
    tr.querySelector('input[name="quantity[]"]').dispatchEvent(new Event('input', { bubbles: true }));
}

// 明細行を削除する（最低1行は残す）
function removeRow(button) {
    const tbody = document.getElementById('items-body');
    if (tbody.rows.length > 1) {
        button.closest('tr').remove();
    }
}
