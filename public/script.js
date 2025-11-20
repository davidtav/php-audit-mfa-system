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


document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("toggleBlur");
    const tableBody = document.querySelector("table tbody");

    if (!toggle || !tableBody) return;

    toggle.addEventListener("change", function () {
        if (this.checked) {
            tableBody.classList.add("blur-active");
        } else {
            tableBody.classList.remove("blur-active");
        }
    });
});
