document.addEventListener('DOMContentLoaded', function () {

  // ---- Random key generator (bonus feature) ----
  var randomBtn = document.getElementById('randomKeyBtn');
  var keyInput = document.getElementById('key');
  if (randomBtn && keyInput) {
    randomBtn.addEventListener('click', function () {
      var randomKey = Math.floor(Math.random() * 25) + 1; // 1-25
      keyInput.value = randomKey;
      keyInput.focus();
    });
  }

  // ---- Copy encrypted/decrypted result to clipboard (bonus feature) ----
  var copyBtn = document.getElementById('copyBtn');
  var resultText = document.getElementById('resultText');
  if (copyBtn && resultText) {
    copyBtn.addEventListener('click', function () {
      var text = resultText.textContent;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function () {
          var original = copyBtn.textContent;
          copyBtn.textContent = '✓ Copied!';
          setTimeout(function () { copyBtn.textContent = original; }, 1500);
        });
      } else {
        // Fallback for older browsers
        var temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
      }
    });
  }

  // ---- Stagger the process table row reveal animation ----
  var rows = document.querySelectorAll('.process-table tbody tr');
  rows.forEach(function (row, index) {
    row.style.setProperty('--i', index);
  });

  // ---- Clear/Reset button clears the form immediately client-side too ----
  var clearBtns = document.querySelectorAll('button[value="clear"]');
  clearBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var textarea = document.getElementById('text');
      if (textarea) textarea.value = '';
      if (keyInput) keyInput.value = 3;
    });
  });
});
