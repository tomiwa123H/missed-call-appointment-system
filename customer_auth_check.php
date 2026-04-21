// Wait until the full HTML document is loaded before running the script
document.addEventListener("DOMContentLoaded", () => {

  // -----------------------------
  // INITIAL SETUP FUNCTION
  // -----------------------------
  function main() {

    // SAFETY CHECK: Stop if banner doesn't exist
    const cookieBanner = document.getElementById("cookie-banner");
    if (!cookieBanner) return;

    // Get all required DOM elements
    const elements = getDOMElements();

    // Setup GDPR logic
    handleConsent(elements);

    // Attach button events
    setupEventListeners(elements);
  }

  // -----------------------------
  // GET DOM ELEMENTS
  // -----------------------------
  //document refers to the current webpage, getElementById finds the element with the specified ID and returns it, allowing us to interact with it in JavaScript
// Gets the html element by its ID
  function getDOMElements() {
    return {
      cookieBanner: document.getElementById("cookie-banner"),
      acceptBtn: document.getElementById("accept-cookies"),
      declineBtn: document.getElementById("decline-cookies"),
      statsContainer: document.getElementById("stats-container"),
      visitCountSpan: document.getElementById("visit-count"),
      lastVisitSpan: document.getElementById("last-visit-date"),
      originalStatsHTML: document.getElementById("stats-container").innerHTML
    };
  }

  // -----------------------------
  // COOKIE FUNCTIONS
  // -----------------------------

  // Create a cookie
  function setCookie(name, value, days) {
    let expires = "";

    if (days) {
      const date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    }

    // Set cookie with name, value, expiry, path and security setting
    document.cookie =
      name + "=" + encodeURIComponent(value) + expires + "; path=/; SameSite=Lax";
  }

  // Get a cookie by name
  function getCookie(name) {

    // Create search string (e.g. "username=")
    const nameEQ = name + "=";

    // Split all cookies into an array
    const cookies = document.cookie.split(";");

    // Loop through each cookie
    for (let i = 0; i < cookies.length; i++) {

      // Remove extra spaces
      let c = cookies[i].trim();

      // Check if cookie starts with the name we want
      if (c.indexOf(nameEQ) === 0) {

        // Return the value part and decode it
        return decodeURIComponent(c.substring(nameEQ.length));
      }
    }

    return null; // Not found
  }

  // -----------------------------
  // UI FUNCTIONS
  // -----------------------------

  // Hide an element after a delay
  function hideAfterDelay(element, delay = 3000) {
    setTimeout(() => {
      if (element) {
        element.classList.add("hidden");
      }
    }, delay);
  }

  // Display visit stats
  function displayStats(data, elements) {
    if (!data) return;

    elements.visitCountSpan.textContent = data.count;
    elements.lastVisitSpan.textContent = data.date;

    elements.statsContainer.classList.remove("hidden");

    hideAfterDelay(elements.statsContainer, 3000);
  }

  // Show decline message
  function showDeclineMessage(elements) {
    elements.statsContainer.innerHTML = `
      <h3>Tracking Declined</h3>
      <p>We respect your privacy. No tracking cookies have been set.</p>
    `;

    elements.statsContainer.classList.remove("hidden");

    setTimeout(() => {
      elements.statsContainer.classList.add("hidden");
      elements.statsContainer.innerHTML = elements.originalStatsHTML;
    }, 3000);
  }

  // -----------------------------
  // TRACKING LOGIC
  // -----------------------------
  function handleTracking(elements) {

    const TRACKING_COOKIE = "user_visit_data";

    // Get existing visit data
    let visitDataString = getCookie(TRACKING_COOKIE);
    let data = { count: 0, date: "Never" };

    if (visitDataString) {
      try {
        data = JSON.parse(visitDataString);
      } catch (e) {
        console.error("Invalid cookie data");
      }
    }

    // Check if visit already counted this session
    const trackedThisSession = sessionStorage.getItem("session_tracked");

    if (!trackedThisSession) {
      data.count += 1;

      const today = new Date();
      data.date =
        today.toLocaleDateString() + " at " + today.toLocaleTimeString();

      setCookie(TRACKING_COOKIE, JSON.stringify(data), 365);

      sessionStorage.setItem("session_tracked", "true");
    }

    displayStats(data, elements);
  }

  // -----------------------------
  // GDPR CONSENT LOGIC
  // -----------------------------
  function handleConsent(elements) {

    const CONSENT_COOKIE = "user_cookie_consent";
    const consentStatus = getCookie(CONSENT_COOKIE);

 if (consentStatus === "accepted") {

  // Check if stats have already been shown during this visit
  const statsShown = sessionStorage.getItem("stats_shown");

  if (!statsShown) {
    // First time in this session → run tracking + show stats
    handleTracking(elements);

    // Mark as shown so it doesn't appear again this session
    sessionStorage.setItem("stats_shown", "true");
  }

} else if (consentStatus === "declined") {
  // User declined → do nothing (no tracking, no stats)

} else {
  // No cookie exists → first-time visitor → show banner
  elements.cookieBanner.classList.remove("hidden");
}
  }

  // -----------------------------
  // EVENT LISTENERS
  // -----------------------------
  function setupEventListeners(elements) {

    const CONSENT_COOKIE = "user_cookie_consent";

    // ACCEPT BUTTON
    elements.acceptBtn.addEventListener("click", () => {

      setCookie(CONSENT_COOKIE, "accepted", 365);

      // Disable buttons
      elements.acceptBtn.disabled = true;
      elements.declineBtn.disabled = true;

      // Hide banner
      elements.cookieBanner.classList.add("hidden");
      elements.cookieBanner.style.display = "none";

      // Start tracking
      handleTracking(elements);
    });

    // DECLINE BUTTON
    elements.declineBtn.addEventListener("click", () => {

      setCookie(CONSENT_COOKIE, "declined", 365);

      elements.acceptBtn.disabled = true;
      elements.declineBtn.disabled = true;

      elements.cookieBanner.classList.add("hidden");
      elements.cookieBanner.style.display = "none";

      showDeclineMessage(elements);
    });
  }

  // -----------------------------
  // RUN APP
  // -----------------------------
  main();

});