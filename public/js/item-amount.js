// 明細行の「金額」列（数量 × 単価、表示のみ）をリアルタイムに再計算する
document.addEventListener('DOMContentLoaded', () => {
    const updateRowAmount = (row) => {
        const quantityInput = row.querySelector('input[name="quantity[]"]');
        const unitPriceInput = row.querySelector('input[name="unit_price[]"]');
        const amountCell = row.querySelector('.item-amount');
        if (!quantityInput || !unitPriceInput || !amountCell) return;

        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value.replace(/,/g, '')) || 0;
        amountCell.textContent = Math.round(quantity * unitPrice).toLocaleString('ja-JP') + ' 円';
    };

    // 動的に追加される明細行にも対応できるよう、documentでイベント委譲する
    document.addEventListener('input', (event) => {
        if (!event.target.matches('input[name="quantity[]"], input[name="unit_price[]"]')) return;
        const row = event.target.closest('tr');
        if (row) updateRowAmount(row);
    });

    // JSで行を追加した直後にも計算しておく
    document.querySelectorAll('#items-body tr').forEach(updateRowAmount);
});
