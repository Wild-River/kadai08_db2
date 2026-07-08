// 明細行を1行追加する
function addRow() {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="item_name[]" class="form-input"></td>
        <td><input type="number" name="quantity[]" class="form-input" value="1"></td>
        <td><input type="text" name="unit_price[]" class="form-input money-input" value="0" inputmode="numeric" autocomplete="off"></td>
        <td><button type="button" class="delete-btn" onclick="removeRow(this)">削除</button></td>
    `;
    tbody.appendChild(tr);
}

// 明細行を削除する（最低1行は残す）
function removeRow(button) {
    const tbody = document.getElementById('items-body');
    if (tbody.rows.length > 1) {
        button.closest('tr').remove();
    }
}
