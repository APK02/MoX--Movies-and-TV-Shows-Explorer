function showToastMessage(message) {
  const toast = Toastify({
    text: message,
    duration: 3000,
  });
  toast.showToast();
}
