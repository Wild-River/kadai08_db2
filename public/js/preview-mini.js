// 編集画面右側のミニプレビューを、A4比率の枠(.preview-mini)の幅に合わせて縮小表示する
// 枠の高さはCSSのaspect-ratioで決まるため、ここでは幅から縮小率を出すだけでよい
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.preview-mini');
    const scaleTarget = document.querySelector('.preview-mini__scale');
    if (!wrap || !scaleTarget) return;

    const fit = () => {
        const containerWidth = wrap.clientWidth;
        const naturalWidth = scaleTarget.offsetWidth;
        if (!containerWidth || !naturalWidth) return;

        const scale = containerWidth / naturalWidth;
        scaleTarget.style.transform = `scale(${scale})`;
    };

    fit();
    window.addEventListener('resize', fit);
});
