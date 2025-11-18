document.addEventListener("DOMContentLoaded", () => {
  const feedback = document.getElementById("feedback");

  if (feedback) {
    setTimeout(() => {
      feedback.style.opacity = 0;
    }, 400);
  }

  const form = document.getElementById("userForm");
  form.addEventListener("submit", () => {
    form.querySelector("button").textContent = "Cadastrando...";
  });
});
