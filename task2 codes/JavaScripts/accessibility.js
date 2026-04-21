// Gets the hamburger button element by its ID
const ham = document.getElementById("access-hamburger");
//document refers to the current webpage, getElementById finds the element with the specified ID and returns it, allowing us to interact with it in JavaScript
// Gets the accessibility panel element by its ID
const panel = document.getElementById("access-panel");

// Sets default font scale (1 = 100%)
let fontScale = 1; // 100%

// Toggle open/close
// Adds a click event listener to the hamburger button
ham.addEventListener("click", (event) => {

    // Prevents the click from triggering other click events (like closing the panel)
    event.stopPropagation();

    // Toggles the "open" class on the panel (shows/hides it)
    panel.classList.toggle("open");

    // Toggles a class on the body (used for styling when panel is open)
    document.body.classList.toggle("panel-open");
});

// Close on click outside
// Adds a click event listener to the whole document
document.addEventListener("click", (e) => {

    // If the click is NOT inside the panel AND NOT on the hamburger button
    if (!panel.contains(e.target) && !ham.contains(e.target)) {

        // Remove "open" class to close the panel
        panel.classList.remove("open");

        // Remove body class
        document.body.classList.remove("panel-open");
    }
});

/* ==========================
   Accessibility Functions
   ========================== */

// Increase up to 200%
// Function to increase font size
function increaseFont() {

    // Only increase if below 200% (2 = 200%)
    if (fontScale < 2) {

        // Increase font scale by 0.1 (10%)
        fontScale += 0.1;

        // Apply new font size to entire document
        document.documentElement.style.fontSize = fontScale + "em";
    }
}

// Decrease down to 100%
// Function to decrease font size
function decreaseFont() {

    // Only decrease if above 100% (1 = 100%)
    if (fontScale > 1) {

        // Decrease font scale by 0.1
        fontScale -= 0.1;

        // Apply new font size
        document.documentElement.style.fontSize = fontScale + "em";
    }
}

// Function to toggle high contrast mode
function toggleHighContrast() {

    // Adds/removes "high-contrast" class on body
    document.body.classList.toggle("high-contrast");
}

// Full reset
// Function to reset all accessibility settings
function resetAccessibility() {

    // Reset font scale back to default (100%)
    fontScale = 1;

    // Reset font size to normal
    document.documentElement.style.fontSize = "1em";

    // Remove high contrast mode
    document.body.classList.remove("high-contrast");
}