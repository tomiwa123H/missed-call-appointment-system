document.addEventListener("DOMContentLoaded", () => {
    const sidebar  = document.getElementById("sidebar");
    const openBtn  = document.getElementById("openSidebar");
    const closeBtn = document.getElementById("closeSidebar");

    if (openBtn && sidebar) {
        openBtn.addEventListener("click", () => {
            sidebar.classList.add("active");
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener("click", () => {
            sidebar.classList.remove("active");
        });
    }
});