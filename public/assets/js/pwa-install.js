let deferredPwaPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
  event.preventDefault();
  deferredPwaPrompt = event;

  document.querySelectorAll('[data-pwa-install]').forEach((button) => {
    button.hidden = false;
  });
});

window.addEventListener('appinstalled', () => {
  deferredPwaPrompt = null;
});

document.addEventListener('DOMContentLoaded', () => {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
  }

  document.querySelectorAll('[data-pwa-install]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!deferredPwaPrompt) {
        Swal.fire({
          title: 'Pasang di HP',
          text: 'Buka menu browser, lalu pilih Tambahkan ke layar utama.',
          icon: 'info',
          confirmButtonText: 'OK'
        });
        return;
      }

      deferredPwaPrompt.prompt();
      await deferredPwaPrompt.userChoice;
      deferredPwaPrompt = null;
    });
  });
});
