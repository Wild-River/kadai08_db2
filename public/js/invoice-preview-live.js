// 請求書作成画面: フォームの入力内容をリアルタイムで右側のミニプレビューに反映する
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('new-form');
    const els = {
        customerName: document.getElementById('preview-customer-name'),
        subject: document.getElementById('preview-subject'),
        invoiceNumber: document.getElementById('preview-invoice-number'),
        issueDate: document.getElementById('preview-issue-date'),
        dueDate: document.getElementById('preview-due-date'),
        statusBadge: document.getElementById('preview-status-badge'),
        itemsBody: document.getElementById('preview-items-body'),
        subtotal: document.getElementById('preview-subtotal'),
        taxLabel: document.getElementById('preview-tax-label'),
        tax: document.getElementById('preview-tax'),
        total: document.getElementById('preview-total'),
    };
    if (!form || !els.customerName) return;

    const customers = typeof PREVIEW_CUSTOMERS !== 'undefined' ? PREVIEW_CUSTOMERS : [];
    const statusLabels = typeof PREVIEW_STATUS_LABELS !== 'undefined' ? PREVIEW_STATUS_LABELS : {};

    const yen = (n) => Math.round(n).toLocaleString('ja-JP') + ' 円';

    const update = () => {
        const customerId = form.querySelector('[name="customer_id"]').value;
        const customer = customers.find((c) => String(c.id) === String(customerId));
        els.customerName.textContent = (customer ? customer.name : '顧客未選択') + ' 御中';

        const title = form.querySelector('[name="title"]').value.trim();
        els.subject.hidden = title === '';
        els.subject.textContent = title === '' ? '' : '件名：' + title;

        els.invoiceNumber.textContent = form.querySelector('[name="invoice_number"]').value || '未入力';
        els.issueDate.textContent = form.querySelector('[name="issue_date"]').value || '未入力';
        els.dueDate.textContent = form.querySelector('[name="due_date"]').value || '未入力';

        const statusKey = form.querySelector('[name="status"]').value;
        els.statusBadge.textContent = statusLabels[statusKey] || '';
        els.statusBadge.className = 'status-badge status-' + statusKey;

        let subtotal = 0;
        els.itemsBody.innerHTML = '';
        document.querySelectorAll('#items-body tr').forEach((row) => {
            const name = row.querySelector('[name="item_name[]"]').value.trim();
            if (name === '') return;

            const quantity = parseFloat(row.querySelector('[name="quantity[]"]').value) || 0;
            const unitPrice = parseFloat(row.querySelector('[name="unit_price[]"]').value.replace(/,/g, '')) || 0;
            const amount = quantity * unitPrice;
            subtotal += amount;

            const tr = document.createElement('tr');
            const cells = [name, String(quantity), yen(unitPrice), yen(amount)];
            cells.forEach((text) => {
                const td = document.createElement('td');
                td.textContent = text;
                tr.appendChild(td);
            });
            els.itemsBody.appendChild(tr);
        });

        const taxRate = parseFloat(form.querySelector('[name="tax_rate"]').value) || 0;
        const tax = Math.round(subtotal * (taxRate / 100));
        const total = subtotal + tax;

        els.subtotal.textContent = yen(subtotal);
        els.taxLabel.textContent = `消費税（${taxRate}%）`;
        els.tax.textContent = yen(tax);
        els.total.textContent = yen(total);
    };

    // 明細テーブルはform要素の外にあり、form.addEventListenerではイベントを拾えないため、
    // document全体で拾ってから明細以外の入力もまとめて拾う
    document.addEventListener('input', update);
    document.addEventListener('change', update);
    // 明細行の削除は removeRow() 側でイベントを発火しないため、クリックを拾って更新する
    document.addEventListener('click', (event) => {
        if (event.target.closest('.delete-btn')) update();
    });

    update();
});
