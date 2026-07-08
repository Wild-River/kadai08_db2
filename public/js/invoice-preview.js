document.addEventListener('DOMContentLoaded', () => {
    const backLink = document.getElementById('back-to-edit-link');
    if (!backLink) return;

    backLink.addEventListener('click', (event) => {
        event.preventDefault();
        window.close();

        // window.close()が効かない場合（直接URLを開いた場合など）は通常のリンク遷移にフォールバック
        setTimeout(() => {
            window.location.href = backLink.href;
        }, 100);
    });
});
