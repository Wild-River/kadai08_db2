document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('tr.row-link[data-href]').forEach((row) => {
    row.addEventListener('click', () => {
      window.location.href = row.dataset.href;
    });
  });
});
