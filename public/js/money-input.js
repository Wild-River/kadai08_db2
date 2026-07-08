// 単価などの金額入力欄（.money-input）を入力しながら桁区切り表示する
document.addEventListener('DOMContentLoaded', () => {
    const formatMoney = (value) => {
        const digits = value.replace(/[^\d]/g, '');
        if (digits === '') return '';
        return Number(digits).toLocaleString('ja-JP');
    };

    // 動的に追加される明細行のinputにも対応できるよう、documentでイベント委譲する
    document.addEventListener('input', (event) => {
        if (!event.target.matches('.money-input')) return;

        const input = event.target;
        const cursorFromEnd = input.value.length - input.selectionStart;
        input.value = formatMoney(input.value);
        const newPos = Math.max(0, input.value.length - cursorFromEnd);
        input.setSelectionRange(newPos, newPos);
    });

    // 送信時はカンマを除去してから送る
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('.money-input').forEach((input) => {
                input.value = input.value.replace(/,/g, '');
            });
        });
    });
});
